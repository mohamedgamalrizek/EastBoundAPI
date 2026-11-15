<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebsiteCity extends Model
{
    protected $fillable = ['destination_id','name','slug','headline','description','about','image','meta_title','meta_description','active','sort_order'];
    protected $casts = ['active'=>'boolean'];
    public function destination() { return $this->belongsTo(WebsiteDestination::class); }
    public function packages() { return $this->belongsToMany(Package::class, 'package_website_city'); }
}
