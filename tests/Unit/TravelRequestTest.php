<?php

namespace Tests\Unit;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_request_can_be_approved()
    {
        $admin = User::factory()->admin()->create();
        $request = TravelRequest::factory()->requested()->create();

        $result = $request->approve($admin);

        $this->assertTrue($result);
        $this->assertEquals(TravelRequest::STATUS_APPROVED, $request->status);
        $this->assertEquals($admin->id, $request->approved_by);
        $this->assertNotNull($request->approved_at);
    }

    public function test_approved_travel_request_cannot_be_approved_again()
    {
        $admin = User::factory()->admin()->create();
        $request = TravelRequest::factory()->approved()->create();

        $result = $request->approve($admin);

        $this->assertFalse($result);
    }

    public function test_travel_request_can_be_cancelled()
    {
        $request = TravelRequest::factory()->requested()->create();

        $result = $request->cancel('Teste de cancelamento');

        $this->assertTrue($result);
        $this->assertEquals(TravelRequest::STATUS_CANCELLED, $request->status);
        $this->assertNotNull($request->cancelled_at);
        $this->assertEquals('Teste de cancelamento', $request->cancellation_reason);
    }

    public function test_approved_travel_request_cannot_be_cancelled()
    {
        $request = TravelRequest::factory()->approved()->create();

        $result = $request->cancel('Teste');

        $this->assertFalse($result);
        $this->assertEquals(TravelRequest::STATUS_APPROVED, $request->status);
    }

    public function test_can_be_cancelled_method()
    {
        $requestedRequest = TravelRequest::factory()->requested()->create();
        $approvedRequest = TravelRequest::factory()->approved()->create();

        $this->assertTrue($requestedRequest->canBeCancelled());
        $this->assertFalse($approvedRequest->canBeCancelled());
    }
}