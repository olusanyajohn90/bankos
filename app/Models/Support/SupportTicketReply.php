<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

class SupportTicketReply extends Model
{
    use HasUuids;

    protected $table = 'support_ticket_replies';

    protected $fillable = ['ticket_id','author_id','body','type','is_internal','attachment_path'];

    protected $casts = ['is_internal' => 'boolean'];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function ticket() { return $this->belongsTo(SupportTicket::class, 'ticket_id'); }
}
