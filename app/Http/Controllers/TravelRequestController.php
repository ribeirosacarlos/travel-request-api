<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTravelRequestRequest;
use App\Http\Requests\ListTravelRequestsRequest;
use App\Http\Requests\UpdateTravelRequestStatusRequest;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TravelRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(ListTravelRequestsRequest $request): AnonymousResourceCollection
    {
        $query = auth()->user()->isAdmin() 
            ? TravelRequest::query()
            : auth()->user()->travelRequests();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('destination')) {
            $query->byDestination($request->destination);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        $travelRequests = $query->with(['user', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return TravelRequestResource::collection($travelRequests);
    }

    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        $travelRequest = auth()->user()->travelRequests()->create([
            'requester_name' => $request->requester_name,
            'destination' => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date' => $request->return_date,
        ]);

        return response()->json([
            'message' => 'Pedido de viagem criado com sucesso',
            'data' => new TravelRequestResource($travelRequest->load(['user', 'approvedBy'])),
        ], 201);
    }

    public function show(TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('view', $travelRequest);

        return response()->json([
            'data' => new TravelRequestResource($travelRequest->load(['user', 'approvedBy'])),
        ]);
    }

    public function updateStatus(UpdateTravelRequestStatusRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('updateStatus', $travelRequest);

        $oldStatus = $travelRequest->status;
        $newStatus = $request->status;

        if ($newStatus === TravelRequest::STATUS_APPROVED) {
            if (!$travelRequest->approve(auth()->user())) {
                return response()->json([
                    'error' => 'Não é possível aprovar este pedido'
                ], 422);
            }
        } elseif ($newStatus === TravelRequest::STATUS_CANCELLED) {
            if (!$travelRequest->cancel($request->cancellation_reason)) {
                return response()->json([
                    'error' => 'Não é possível cancelar este pedido'
                ], 422);
            }
        }

        // Enviar notificação se o status mudou
        // if ($oldStatus !== $newStatus) {
        //     $travelRequest->user->notify(new TravelRequestStatusChanged($travelRequest, $oldStatus, $newStatus));
        // }

        return response()->json([
            'message' => 'Status atualizado com sucesso',
            'data' => new TravelRequestResource($travelRequest->fresh()->load(['user', 'approvedBy'])),
        ]);
    }
}