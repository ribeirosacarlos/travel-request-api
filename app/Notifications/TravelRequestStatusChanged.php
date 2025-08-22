<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestStatusChanged extends Notification
{
    use Queueable;

    protected $travelRequest;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(TravelRequest $travelRequest, string $oldStatus, string $newStatus)
    {
        $this->travelRequest = $travelRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->newStatus === TravelRequest::STATUS_APPROVED 
            ? 'Pedido de Viagem Aprovado' 
            : 'Pedido de Viagem Cancelado';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Seu pedido de viagem para {$this->travelRequest->destination} foi {$this->newStatus}.");

        if ($this->newStatus === TravelRequest::STATUS_APPROVED) {
            $message->line('Sua viagem foi aprovada! Prepare-se para partir.');
        } else {
            $message->line('Infelizmente sua viagem foi cancelada.');
            if ($this->travelRequest->cancellation_reason) {
                $message->line("Motivo: {$this->travelRequest->cancellation_reason}");
            }
        }

        return $message->line('Obrigado por usar nosso sistema!');
    }

    public function toArray($notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'destination' => $this->travelRequest->destination,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Seu pedido de viagem para {$this->travelRequest->destination} foi {$this->newStatus}",
        ];
    }
}