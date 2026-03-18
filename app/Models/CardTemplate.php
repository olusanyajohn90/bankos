<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'name', 'primary_color', 'secondary_color', 'text_color',
        'background_color', 'logo_path', 'background_image_path',
        'show_qr', 'show_photo', 'show_department', 'show_grade',
        'show_blood_group', 'show_emergency_contact', 'expiry_years', 'is_default',
    ];

    protected $casts = [
        'show_qr' => 'boolean',
        'show_photo' => 'boolean',
        'show_department' => 'boolean',
        'show_grade' => 'boolean',
        'show_blood_group' => 'boolean',
        'show_emergency_contact' => 'boolean',
        'is_default' => 'boolean',
        'expiry_years' => 'integer',
    ];

    public function idCards(): HasMany { return $this->hasMany(StaffIdCard::class, 'template_id'); }
}
