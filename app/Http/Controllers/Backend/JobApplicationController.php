<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function index()
    {
        $applications = JobApplication::with('jobOpening')
            ->latest()
            ->paginate(10);

        return view('backend.job_application.index', compact('applications'));
    }

    public function show($id)
    {
        $application = JobApplication::with('jobOpening')->findOrFail($id);

        if ($application->status === 'new') {
            $application->update(['status' => 'reviewed']);
        }

        return view('backend.job_application.show', compact('application'));
    }

    public function resume($id)
    {
        $application = JobApplication::findOrFail($id);

        abort_unless($application->resume_path && Storage::disk('local')->exists($application->resume_path), 404);

        return Storage::disk('local')->download($application->resume_path);
    }

    public function delete($id)
    {
        $application = JobApplication::findOrFail($id);

        if ($application->resume_path) {
            Storage::disk('local')->delete($application->resume_path);
        }

        $application->delete();

        return response()->json([
            'status'      => true,
            'message'     => ___('alert.successfully_deleted'),
            'status_code' => 200,
        ]);
    }
}
