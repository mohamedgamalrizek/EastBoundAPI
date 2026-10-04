<?php

namespace App\Repositories\Tour;

interface TourInterface
{
    public function category();

    public function categoryStore($request);

    public function categoryEdit($id);

    public function categoryUpdate($request, $id);

    public function categoryDelete($id);


    public function schedule();
    public function scheduleCreate();
    public function scheduleStore($request);
    public function scheduleEdit($id);
    public function scheduleUpdate($request, $id);
    public function scheduleDelete($id);

    public function guides();
    public function guidesStore($request);
    public function guidesEdit($id);
    public function guidesUpdate($request, $id);
    public function guidesDelete($id);

    public function guideAssignments();
    public function guideAssignmentsStore($request);
    public function guideAssignmentsStatus($request, $id);
    public function guideAssignmentsDelete($id);

    public function reports();
}
