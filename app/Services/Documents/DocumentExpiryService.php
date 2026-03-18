<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class DocumentExpiryService
{
    public function alertExpiring(int $days = 30): int
    {
        $documents = Document::with(['uploadedBy', 'reviewedByUser'])
            ->whereNotNull('expiry_date')
            ->whereIn('status', ['approved', 'pending'])
            ->where('is_current_version', true)
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now())
            ->get();

        $notified = 0;
        foreach ($documents as $doc) {
            $recipients = collect();
            if ($doc->uploadedBy) {
                $recipients->push($doc->uploadedBy);
            }
            if ($doc->reviewedByUser && $doc->reviewedByUser->id !== $doc->uploadedBy?->id) {
                $recipients->push($doc->reviewedByUser);
            }
            foreach ($recipients->unique('id') as $user) {
                $user->notify(new \App\Notifications\DocumentExpiryAlert($doc));
                $notified++;
            }
        }
        return $notified;
    }
}
