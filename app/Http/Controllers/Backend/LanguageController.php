<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\StoreRequest;
use App\Http\Requests\Language\UpdateRequest;
use App\Repositories\Language\LanguageInterface;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected $repo;

    public function __construct(LanguageInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {

        $languages = $this->repo->all();

        return view('backend.language.index', compact('languages'));
    }

    // language create page
    public function create()
    {
        $flags = $this->repo->flags();

        return view('backend.language.create', compact('flags'));
    }

    // language store
    public function store(StoreRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('language.index')->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('danger', $result['message']);
    }

    public function edit($id)
    {
        $lang = $this->repo->find($id);
        $flags = $this->repo->flags();

        return view('backend.language.edit', compact('lang', 'flags'));
    }

    // language update
    public function update(UpdateRequest $request)
    {
        $result = $this->repo->update($request);

        if ($result['status']) {
            return redirect()->route('language.index')->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('danger', $result['message']);
    }

    // delete language
    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }

    // edit phrase
    public function editPhrase($id)
    {
        $lang = $this->repo->find($id);

        return view('backend.language.edit_phrase', compact('lang'));
    }

    // update phrase
    public function updatePhrase(Request $request)
    {
        $result = $this->repo->updatePhrase($request);

        if ($request->expectsJson()) {
            return response()->json($result, $result['status_code']);
        }

        if ($result['status']) {
            return redirect()->route('language.index')->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('danger', $result['message']);
    }

    public function modulePhrase(Request $request)
    {
        try {
            $path = base_path("lang/{$request->code}/{$request->module}.json");

            $jsonString = file_get_contents($path);

            $phrases = json_decode($jsonString, true);
            ksort($phrases);
            $data = ['code' => $request->code, 'module' => $request->module, 'phrases' => $phrases];

            return response()->json($data);
        } catch (\Throwable $th) {
            $data = ['message' => ___('alert.something_went_wrong')];

            return response()->json($data, 500);
        }
    }
}
