<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EnrolmentHiring extends Model
{
    use HasFactory, SoftDeletes;
    // Automatically include profile_preview_url in arrays/JSON responses
     // 1. Disable automatic snake_case conversion for appends
    public static $snakeAttributes = false;

    // 2. Define the exact camelCase keys you want in the JSON output
    protected $appends = [

    ];
    protected $fillable = [
        'enrolment_id',
        'hospital_id',
        'released_at',
    ];
    public function hospital()
    {
        return $this->belongsTo(User::class, 'hospital_id', 'id');
    }
     public function enrolment()
    {
        return $this->belongsTo(Enrolment::class, 'enrolment_id', 'id');
    }

}
