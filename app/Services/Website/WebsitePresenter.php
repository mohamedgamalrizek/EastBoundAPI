<?php
namespace App\Services\Website;
use App\Models\Package;
class WebsitePresenter
{
    public function lines(?string $text): array { return array_values(array_filter(array_map('trim', preg_split('/\R/', $text ?? '')))); }
    public function link(?string $url): ?string {
        $url = trim($url ?? '');
        return preg_match('~^(https?://|mailto:|tel:|/(?!/)|#)~i', $url) ? $url : null;
    }
    public function location($item): array {
        return ['id'=>(string)$item->id,'slug'=>$item->slug,'name'=>$item->name,'headline'=>$item->headline,'description'=>$item->description,
            'about'=>$item->about,'image'=>$item->image ? media_url($item->image) : null,'metaTitle'=>$item->meta_title,'metaDescription'=>$item->meta_description];
    }
    public function tour(Package $tour, bool $detail = false): array {
        $cities = $tour->websiteCities->where('active', true)->where('destination_id', $tour->website_destination_id);
        $result = ['id'=>(string)$tour->id,'slug'=>$tour->website_slug,'destinationId'=>(string)$tour->website_destination_id,
            'destinationSlug'=>$tour->websiteDestination->slug,'cityIds'=>$cities->pluck('id')->map(fn($id)=>(string)$id)->values(),
            'title'=>$tour->title,'fullTitle'=>$tour->website_full_title ?: $tour->title,'type'=>$tour->website_type,
            'durationDays'=>(int)$tour->duration_days,'duration'=>$tour->duration_days.' Days / '.$tour->duration_nights.' Nights',
            'locations'=>$cities->pluck('name')->implode(', '),'price'=>null,'bookingMode'=>'enquiry',
            'image'=>$tour->image ? media_url($tour->image) : null,'isFeatured'=>(bool)$tour->website_featured,
            'rating'=>$tour->avg_rating,'reviewsCount'=>$tour->reviews_count,'featuredTag'=>$tour->website_tag];
        if ($detail) $result += ['summary'=>$tour->description,'whyChoose'=>$this->lines($tour->website_why_choose),
            'travelTips'=>$this->lines($tour->website_travel_tips),'included'=>$tour->inclusionList(),'notIncluded'=>$tour->exclusionList(),
            'optionalExcursions'=>$this->lines($tour->website_optional_excursions),'startPoint'=>$tour->website_start_point,
            'tourDurationHours'=>$tour->website_duration_hours,'itinerary'=>$tour->itineraries->map(fn($day)=>['day'=>$day->day_number,'title'=>$day->title,'description'=>$day->description,'highlights'=>[]])];
        return $result;
    }
    public function article($post, bool $detail = false): array {
        $result = ['id'=>(string)$post->id,'slug'=>$post->slug,'title'=>$post->title,'summary'=>$post->excerpt,
            'image'=>$post->image ? media_url($post->image) : null,'date'=>$post->published_at?->format('Y-m-d'),
            'tag'=>$post->category,'author'=>$post->author,'readTime'=>$post->read_minutes.' min read',
            'metaTitle'=>$post->title,'metaDescription'=>$post->excerpt];
        if ($detail) $result['content'] = $post->body;
        return $result;
    }
}
