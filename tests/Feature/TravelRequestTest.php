<?php

namespace Tests\Feature;

use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_user_can_create_travel_request()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/travel-requests', [
            'requester_name' => 'João Silva',
            'destination' => 'Paris, França',
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'return_date' => now()->addDays(20)->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'requester_name',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'destination' => 'Paris, França',
            'status' => TravelRequest::STATUS_REQUESTED,
        ]);
    }

    public function test_user_can_only_view_own_travel_requests()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $request1 = TravelRequest::factory()->create(['user_id' => $user1->id]);
        $request2 = TravelRequest::factory()->create(['user_id' => $user2->id]);

        $token = auth()->login($user1);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/travel-requests');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($request1->id, $data[0]['id']);
    }

    public function test_admin_can_view_all_travel_requests()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        
        $request1 = TravelRequest::factory()->create(['user_id' => $admin->id]);
        $request2 = TravelRequest::factory()->create(['user_id' => $user->id]);

        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/travel-requests');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_admin_can_approve_travel_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $request = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/travel-requests/{$request->id}/status", [
            'status' => TravelRequest::STATUS_APPROVED,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $request->id,
            'status' => TravelRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
        ]);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    public function test_admin_can_cancel_travel_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $request = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/travel-requests/{$request->id}/status", [
            'status' => TravelRequest::STATUS_CANCELLED,
            'cancellation_reason' => 'Orçamento insuficiente',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $request->id,
            'status' => TravelRequest::STATUS_CANCELLED,
            'cancellation_reason' => 'Orçamento insuficiente',
        ]);

        Notification::assertSentTo($user, TravelRequestStatusChanged::class);
    }

    public function test_cannot_cancel_approved_travel_request()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $request = TravelRequest::factory()->approved()->create(['user_id' => $user->id]);

        $token = auth()->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/travel-requests/{$request->id}/status", [
            'status' => TravelRequest::STATUS_CANCELLED,
            'cancellation_reason' => 'Teste',
        ]);

        $response->assertStatus(422);
    }

    public function test_regular_user_cannot_update_status()
    {
        $user = User::factory()->create();
        $request = TravelRequest::factory()->create(['user_id' => $user->id]);

        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/travel-requests/{$request->id}/status", [
            'status' => TravelRequest::STATUS_APPROVED,
        ]);

        $response->assertStatus(403);
    }

    public function test_can_filter_travel_requests_by_status()
    {
        $user = User::factory()->create();
        
        $approvedRequest = TravelRequest::factory()->approved()->create(['user_id' => $user->id]);
        $requestedRequest = TravelRequest::factory()->requested()->create(['user_id' => $user->id]);

        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/travel-requests?status=' . TravelRequest::STATUS_APPROVED);

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($approvedRequest->id, $data[0]['id']);
    }

    public function test_can_filter_travel_requests_by_destination()
    {
        $user = User::factory()->create();
        
        $parisRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Paris, França'
        ]);
        $londonRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Londres, Inglaterra'
        ]);

        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/travel-requests?destination=Paris');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($parisRequest->id, $data[0]['id']);
    }
}