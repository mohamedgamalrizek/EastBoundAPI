<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;

class CacheController extends Controller
{
    public function clear()
    {
        Artisan::call('optimize:clear');
        return redirect()->back()->with('success', ___('alert.cache_successfully_cleared.'));
    }
}
