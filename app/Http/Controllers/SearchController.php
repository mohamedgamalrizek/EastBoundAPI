<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function searchRoute(Request $request)
    {
        if (!request()->ajax() || $request->input('search') == null) {
            return;
        }

        $routes = DB::table('route_lists')->where('status', Status::ACTIVE->value)
            ->where('title', 'LIKE', '%' . $request->input('search') . '%')
            ->latest('updated_at')
            ->orderBy('name')
            ->select(['title', 'name'])
            ->get();

        return response()->json($routes, 200);
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        if ($search == null) {
            return redirect()->back();
        }

        $baseQuery = DB::table('route_lists')->where('status', Status::ACTIVE->value);

        $columns = ['title', 'name', 'uri'];

        foreach ($columns as $column) {

            $query = clone $baseQuery;

            // $query->where($column, 'LIKE', '%' . $search . '%');
            $query->where($column, $search);

            if ($query->exists()) {
                break; // Stop loop if matches are found
            }
        }

        if ($query->count()  === 1) {
            $route = $query->first();
            return redirect()->route($route->name);
        }

        return redirect()->back()->with('danger', __('alert.no_result_found'), 'error');
    }
}
