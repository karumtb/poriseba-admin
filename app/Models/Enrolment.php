<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Enrolment extends Model
{
    use HasFactory, SoftDeletes;
    // Automatically include profile_preview_url in arrays/JSON responses
     // 1. Disable automatic snake_case conversion for appends
    public static $snakeAttributes = false;

    // 2. Define the exact camelCase keys you want in the JSON output
    protected $appends = [
        'profilePreviewUrl',
        'aadhaarPreviewUrl',
        'certificatePreviewUrl',
    ];
    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'city',
        'state',
        'pin_code',
        "highest_qualification",
        'experience',
        'adhar_pdf',
        'certificate_pdf',
        'vendor_id',
        'designation',
        'specialization',
        'profile_photo',
        'status','email',
        'phone',
        'rating',
        'remark',
        'placement_location',
        'placement_start',
        'placement_end',
        'fb_reviews',
        'insta_reviews',
        'google_reviews',
        'gender'
    ];
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'id');
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation', 'id');
    }
    /**
     * Get the full URL for the enrolment profile preview.
     */
    protected function profilePreviewUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $path = !empty($attributes['profile_photo'])
                    ? ltrim($attributes['profile_photo'], '/')
                    : 'no-image.png';

                return rtrim(env('PUBLIC_URL'), '/') . '/' . $path;
            },
        );
    }
    /**
     * Get the full URL for the aadhaar  preview.
     */
    protected function aadhaarPreviewUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return !empty($attributes['adhar_pdf'])
                    ? rtrim(env('PUBLIC_URL'), '/') . '/' . ltrim($attributes['adhar_pdf'], '/')
                    : null;
            },
        );
    }

      /**
     * Get the full URL for the certificate  preview.
     */
   protected function certificatePreviewUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return !empty($attributes['certificate_pdf'])
                    ? rtrim(env('PUBLIC_URL'), '/') . '/' . ltrim($attributes['certificate_pdf'], '/')
                    : null;
            },
        );
    }
}
