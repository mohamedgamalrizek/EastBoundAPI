<?php

namespace App\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;


class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = Activity::orderBy('id', 'desc')->paginate(10);
        return view('backend.log.index', compact('logs'));
    }

    public function view($id)
    {
        $logDetails   = Activity::find($id);
        return view('backend.log.view', compact('logDetails'));
    }
}
