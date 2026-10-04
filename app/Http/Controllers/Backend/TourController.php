<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Tour\TourInterface;
use Illuminate\Http\Request;

class TourController extends Controller
{
    protected $repo;

    public function __construct(TourInterface $repo)
    {
        $this->repo = $repo;
    }

    public function category()
    {
        return view(
            'backend.tour.category',
            $this->repo->category()
        );
    }

    public function categoryCreate()
    {
        return view('backend.tour.category-create', $this->repo->categoryCreate());
    }

    public function categoryStore(Request $request)
    {
        $result = $this->repo->categoryStore($request);

        if ($result['status']) {return redirect()->route('tour.category')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }
     public function categoryEdit($id)
    {
        return view('backend.tour.category-edit', $this->repo->categoryEdit($id));
    }

        public function categoryUpdate(Request $request, $id)
        {
            $result = $this->repo->categoryUpdate($request, $id);
    
            if ($result['status']) {
                return redirect()->route('tour.category')->with('success', $result['message']);
            }
    
            return back()->with('danger', $result['message'])->withInput();
        }

     public function categoryDelete($id)
    {
        $result = $this->repo->categoryDelete($id);

        return response()->json($result, $result['status_code']);
    }    




    public function schedule()
    {
        return view('backend.tour.schedule', $this->repo->schedule());
    }

     public function scheduleCreate()
    {
        return view('backend.tour.schedule-create', $this->repo->scheduleCreate());
    }

        public function scheduleStore(Request $request)
    {
        $result = $this->repo->scheduleStore($request);

        if ($result['status']) {return redirect()->route('tour.schedule')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }
     public function scheduleEdit($id)
    {
        return view('backend.tour.schedule-edit', $this->repo->scheduleEdit($id));
    }

        public function scheduleUpdate(Request $request, $id)
        {
            $result = $this->repo->scheduleUpdate($request, $id);
    
            if ($result['status']) {
                return redirect()->route('tour.schedule')->with('success', $result['message']);
            }
    
            return back()->with('danger', $result['message'])->withInput();
        }

     public function scheduleDelete($id)
    {
        $result = $this->repo->scheduleDelete($id);

        return response()->json($result, $result['status_code']);
    }    





    public function guides()
    {
        return view('backend.tour.guides', $this->repo->guides());
    }

    public function guidesCreate()
    {
        return view('backend.tour.guides-create', $this->repo->guidesCreate());
    }

    public function guidesStore(Request $request)
    {
        $result = $this->repo->guidesStore($request);

        if ($result['status']) {return redirect()->route('tour.guides')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }
     public function guidesEdit($id)
    {
        return view('backend.tour.guides-edit', $this->repo->guidesEdit($id));
    }

        public function guidesUpdate(Request $request, $id)
        {
            $result = $this->repo->guidesUpdate($request, $id);
    
            if ($result['status']) {
                return redirect()->route('tour.guides')->with('success', $result['message']);
            }
    
            return back()->with('danger', $result['message'])->withInput();
        }

     public function guidesDelete($id)
    {
        $result = $this->repo->guidesDelete($id);

        return response()->json($result, $result['status_code']);
    }

    public function guideAssignments()
    {
        return view('backend.tour.guide-assignments', $this->repo->guideAssignments());
    }

    public function guideAssignmentsStore(Request $request)
    {
        $result = $this->repo->guideAssignmentsStore($request);

        if ($result['status']) {
            return redirect()->route('tour.guideAssignments')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message'])->withInput();
    }

    public function guideAssignmentsStatus(Request $request, $id)
    {
        $result = $this->repo->guideAssignmentsStatus($request, $id);

        if ($result['status']) {
            return redirect()->route('tour.guideAssignments')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message']);
    }

    public function guideAssignmentsDelete($id)
    {
        $result = $this->repo->guideAssignmentsDelete($id);

        return response()->json($result, $result['status_code']);
    }

    public function reports()
    {
        return view('backend.tour.reports', $this->repo->reports());
    }
}
