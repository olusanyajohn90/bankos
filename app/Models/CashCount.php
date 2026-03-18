<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashCount extends Model
{
    use HasUuids;

    protected $fillable = ['teller_session_id', 'count_type', 'denominations', 'total'];

    protected $casts = [
        'denominations' => 'array',
        'total'         => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(TellerSession::class, 'teller_session_id');
    }
}
