<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'centre_id', 'branch_id', 'loan_officer_id',
        'name', 'code', 'solidarity_guarantee', 'status', 'notes',
    ];

    protected $casts = [
        'solidarity_guarantee' => 'boolean',
    ];

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function loanOfficer()
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(GroupMember::class)->where('status', 'active');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'group_members')
            ->withPivot('role', 'joined_at', 'status')
            ->withTimestamps();
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function getActiveLoansCountAttribute(): int
    {
        return $this->loans()->whereIn('status', ['active', 'overdue'])->count();
    }

    public function getPortfolioAtRiskAttribute(): float
    {
        return (float) $this->loans()->where('status', 'overdue')->sum('outstanding_balance');
    }
}
