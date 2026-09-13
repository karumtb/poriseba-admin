<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewPlacementApplication extends Model
{
    protected $table = 'view_placement_applications';

    protected $appends = ['profilePreviewUrl'];

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Classic Accessor format to resolve undefined method errors.
     * Works perfectly with database views.
     */
    public function getprofilePreviewUrlAttribute()
    {
        // Use $this->attribute_name instead of array mapping to prevent view errors
        if (!empty($this->attributes['profile_photo'])) {
            return rtrim(env('PUBLIC_URL'), '/') . '/' . ltrim($this->attributes['profile_photo'], '/');
        }

        return null;
    }
}
