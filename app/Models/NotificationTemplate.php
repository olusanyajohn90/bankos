<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'event', 'channel', 'subject', 'body', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Replace {{variable}} placeholders in the body/subject.
     */
    public function render(array $data): array
    {
        $body    = $this->body;
        $subject = $this->subject ?? '';

        foreach ($data as $key => $value) {
            $body    = str_replace('{{' . $key . '}}', $value, $body);
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return ['subject' => $subject, 'body' => $body];
    }
}
