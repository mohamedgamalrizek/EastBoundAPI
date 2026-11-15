<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebsiteDestination extends Model
{
    protected $fillable = ['name','slug','headline','description','about','image','meta_title','meta_description','active','sort_order'];
    protected $casts = ['active'=>'boolean'];
    public function cities() { return $this->hasMany(WebsiteCity::class, 'destination_id'); }
    public function packages() { return $this->hasMany(Package::class, 'website_destination_id'); }
}
