<?php
namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Http\Requests\Website\SaveLocationRequest;
use App\Models\WebsiteDestination;
use App\Repositories\Website\WebsiteLocationRepository;
use Illuminate\Http\Request;
class WebsiteLocationController extends Controller
{
    public function __construct(private WebsiteLocationRepository $repo) {}
    public function index(Request $request) {
        $type = $request->route('type');
        $items = $this->repo->query($type)->orderBy('sort_order')->orderBy('name')->paginate(25);
        return view('backend.website.locations.index', compact('type','items'));
    }
    public function create(Request $request) { return $this->form($request); }
    public function edit(Request $request) { return $this->form($request, $request->route('id')); }
    private function form(Request $request, $id = null) {
        $type = $request->route('type');
        $item = $id ? $this->repo->query($type)->findOrFail($id) : $this->repo->query($type)->getModel()->newInstance();
        $destinations = WebsiteDestination::orderBy('name')->get();
        return view('backend.website.locations.form', compact('type','item','destinations'));
    }
    public function store(SaveLocationRequest $request) { return $this->persist($request); }
    public function update(SaveLocationRequest $request) { return $this->persist($request, $request->route('id')); }
    private function persist($request, $id = null) {
        $type = $request->route('type');
        $item = $id ? $this->repo->query($type)->findOrFail($id) : $this->repo->query($type)->getModel()->newInstance();
        if ($type === 'cities' && $item->exists && $item->packages()->where('website_destination_id', '!=', $request->integer('destination_id'))->exists()) {
            return back()->withErrors(['destination_id'=>'This city is linked to tours in its current country. Update those links before moving it.'])->withInput();
        }
        $this->repo->save($type, $request, $item);
        return redirect()->route('website.'.$type.'.index')->with('success','Content saved.');
    }
}
