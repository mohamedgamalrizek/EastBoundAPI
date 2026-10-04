<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        return view('backend.settings.social_links.index', [
            'items' => SocialLink::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('backend.settings.social_links.create', ['item' => null]);
    }

    public function store(Request $request)
    {
        SocialLink::create($this->validated($request));
        return redirect()->route('settings.social-links.index')->with('success', 'Social link added successfully.');
    }

    public function edit(SocialLink $socialLink)
    {
        return view('backend.settings.social_links.edit', ['item' => $socialLink]);
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $socialLink->update($this->validated($request));
        return redirect()->route('settings.social-links.index')->with('success', 'Social link updated successfully.');
    }

    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();
        return response()->json(['status' => true, 'message' => 'Social link deleted successfully.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:100', 'regex:/^fa-[a-z0-9-]+$/'],
            'url' => ['required', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]) + ['sort_order' => (int) $request->input('sort_order', 0)];
    }
}
