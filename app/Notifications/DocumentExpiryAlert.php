<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Notifications\Notification;

class DocumentExpiryAlert extends Notification
{
    public function __construct(public Document $document) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'           => 'document_expiry_alert',
            'document_id'    => $this->document->id,
            'title'          => $this->document->title,
            'expiry_date'    => $this->document->expiry_date?->toDateString(),
            'days_remaining' => $this->document->expiry_date?->diffInDays(now()),
            'entity_type'    => $this->document->documentable_type,
            'message'        => "Document '{$this->document->title}' expires on {$this->document->expiry_date?->format('d M Y')}.",
        ];
    }
}
