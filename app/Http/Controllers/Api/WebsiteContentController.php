<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Package, WebsiteDestination, WebsiteCity, ContentBlock, Blog, CmsPage, Menu, Faq, Testimonial};
use App\Services\Website\WebsitePresenter;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class WebsiteContentController extends Controller
{
    use ApiReturnFormatTrait;
    public function __construct(private WebsitePresenter $presenter) {}
    private function toursQuery() { return Package::forWebsite()->with(['websiteDestination','websiteCities','approvedReviews','itineraries']); }
    private function articlesQuery() { return Blog::published()->where(fn($q)=>$q->whereNull('published_at')->orWhereDate('published_at','<=',today())); }
    private function blocks(string $prefix) {
        $sections = array_filter(array_keys(ContentBlock::SECTIONS), fn($section)=>str_starts_with($section,$prefix));
        return ContentBlock::active()->whereIn('section',$sections)->orderBy('sort_order')->orderBy('id')->get()->groupBy('section')
            ->map(fn($items)=>$items->map(fn($item)=>['id'=>(string)$item->id,'title'=>$item->title,'body'=>$item->body,'icon'=>$item->icon,
                'image'=>$item->image ? media_url($item->image) : null,'url'=>$this->presenter->link($item->url)]));
    }
    public function globals() {
        $menuItem = function ($item) use (&$menuItem) {
            return ['id'=>(string)$item->id,'label'=>$item->title,'url'=>$this->presenter->link($item->url),
                'children'=>$item->relationLoaded('children') ? $item->children->map($menuItem) : []];
        };
        return $this->responseWithSuccess('Website globals.', ['locale'=>'en','bookingMode'=>'enquiry',
            'brand'=>['name'=>settings('name'),'logo'=>logo(settings('light_theme_logo')),'favicon'=>favicon(settings('favicon'))],
            'contact'=>['email'=>settings('email'),'phone'=>settings('phone'),'whatsapp'=>settings('whatsapp'),'address'=>settings('address')],
            'header'=>$this->blocks('website_header'),'footer'=>$this->blocks('website_footer'),
            'menus'=>collect(['header','footer','footer_legal'])->mapWithKeys(fn($position)=>[$position=>Menu::tree($position)->map($menuItem)])]);
    }
    public function page(string $slug) {
        $pages = ['home','about-us','destinations','tours','tailor-made','contact','magazine','faq','country'];
        abort_unless(in_array($slug,$pages,true),404);
        $section = str_replace('-', '_', $slug === 'about-us' ? 'about' : $slug);
        $cmsSlug = ['home'=>'/', 'contact'=>'contact-us', 'magazine'=>'blog'][$slug] ?? $slug;
        $page = CmsPage::published()->where('slug',$cmsSlug)->first();
        return $this->responseWithSuccess('Page content.', ['slug'=>$slug,'locale'=>'en',
            'page'=>$page ? collect($page->only($page->getFillable()))->except(['status','story_image'])->merge(['story_image'=>$page->story_image ? media_url($page->story_image) : null]) : null,
            'sections'=>$this->blocks('website_'.$section.'_')]);
    }
    public function legal(string $slug) {
        abort_unless(in_array($slug,['privacy','terms'],true),404);
        $page = CmsPage::published()->where('slug', $slug === 'privacy' ? 'privacy-policy' : 'terms-conditions')->firstOrFail();
        return $this->responseWithSuccess('Legal page.', $page->only(['title','slug','body','meta_description']));
    }
    public function destinations() {
        return $this->responseWithSuccess('Destinations.', ['destinations'=>WebsiteDestination::where('active',true)->orderBy('sort_order')->orderBy('id')->get()->map(fn($item)=>$this->presenter->location($item))]);
    }
    public function destination(string $slug) {
        $country = WebsiteDestination::where('active',true)->where('slug',$slug)->firstOrFail();
        $cities = $country->cities()->where('active',true)->orderBy('sort_order')->orderBy('id')->get();
        return $this->responseWithSuccess('Destination.', ['destination'=>$this->presenter->location($country)+['cities'=>$cities->map(fn($city)=>$this->presenter->location($city))]]);
    }
    public function city(string $country, string $city) {
        $destination = WebsiteDestination::where('active',true)->where('slug',$country)->firstOrFail();
        $record = $destination->cities()->where('active',true)->where('slug',$city)->firstOrFail();
        return $this->responseWithSuccess('City.', ['city'=>$this->presenter->location($record)+['destinationId'=>(string)$destination->id,'destinationSlug'=>$destination->slug]]);
    }
    public function tours(Request $request) {
        $data=$request->validate(['destination'=>['nullable','string','max:170'],'city'=>['nullable','string','max:170'],
            'type'=>['nullable',Rule::in(['package','day-tour'])],'featured'=>['nullable','boolean'],'search'=>['nullable','string','max:150'],
            'duration_min'=>['nullable','integer','min:1'],'duration_max'=>['nullable','integer','min:1'],'per_page'=>['nullable','integer','min:1','max:50'],
            'sort'=>['nullable',Rule::in(['latest','duration_asc','duration_desc'])],'page'=>['nullable','integer','min:1']]);
        $query=$this->toursQuery();
        if (!empty($data['destination'])) $query->whereHas('websiteDestination',fn($q)=>$q->where('slug',$data['destination']));
        if (!empty($data['city'])) $query->whereHas('websiteCities',fn($q)=>$q->where('slug',$data['city'])->where('active',true));
        if (!empty($data['type'])) $query->where('website_type',$data['type']);
        if (isset($data['featured'])) $query->where('website_featured',(bool)$data['featured']);
        if (!empty($data['search'])) $query->where('title','like','%'.$data['search'].'%');
        if (!empty($data['duration_min'])) $query->where('duration_days','>=',$data['duration_min']);
        if (!empty($data['duration_max'])) $query->where('duration_days','<=',$data['duration_max']);
        $sort=$data['sort']??'latest';
        if ($sort !== 'latest') $query->orderBy('duration_days',$sort==='duration_asc'?'asc':'desc');
        $rows=$query->orderByDesc('id')->paginate($data['per_page']??12);
        return $this->responseWithSuccess('Tours.', ['tours'=>$rows->getCollection()->map(fn($tour)=>$this->presenter->tour($tour)),'meta'=>$this->meta($rows)]);
    }
    public function tour(string $slug) {
        $tour=$this->toursQuery()->where('website_slug',$slug)->firstOrFail();
        return $this->responseWithSuccess('Tour.', ['tour'=>$this->presenter->tour($tour,true)]);
    }
    public function magazine(Request $request) {
        $data=$request->validate(['search'=>['nullable','string','max:150'],'category'=>['nullable','string','max:100'],'page'=>['nullable','integer','min:1'],'per_page'=>['nullable','integer','min:1','max:50']]);
        $query=$this->articlesQuery();
        if (!empty($data['search'])) $query->where('title','like','%'.$data['search'].'%');
        if (!empty($data['category'])) $query->where('category',$data['category']);
        $rows=$query->orderByDesc('published_at')->orderByDesc('id')->paginate($data['per_page']??9);
        return $this->responseWithSuccess('Magazine.', ['posts'=>$rows->getCollection()->map(fn($post)=>$this->presenter->article($post)),'meta'=>$this->meta($rows)]);
    }
    public function article(string $slug) { return $this->responseWithSuccess('Article.', ['post'=>$this->presenter->article($this->articlesQuery()->where('slug',$slug)->firstOrFail(),true)]); }
    public function faqs() { return $this->responseWithSuccess('FAQs.', ['faqs'=>Faq::where('status','active')->orderBy('id')->get(['id','question','answer','category'])]); }
    public function testimonials() { return $this->responseWithSuccess('Testimonials.', ['testimonials'=>Testimonial::active()->orderBy('id')->get()->map(fn($item)=>['id'=>(string)$item->id,'name'=>$item->name,'role'=>$item->role,'city'=>$item->city,'message'=>$item->message,'rating'=>$item->rating,'avatar'=>$item->avatar ? media_url($item->avatar) : null])]); }
    private function meta($rows): array { return ['current_page'=>$rows->currentPage(),'last_page'=>$rows->lastPage(),'per_page'=>$rows->perPage(),'total'=>$rows->total()]; }
}
