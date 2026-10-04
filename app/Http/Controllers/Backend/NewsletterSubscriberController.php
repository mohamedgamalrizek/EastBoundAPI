<?php

namespace App\Http\Controllers\Backend;

use App\Models\Subscriber;
use App\Http\Controllers\Controller;

class NewsletterSubscriberController extends Controller
{
    public function index()
    {
        return view('backend.newsletter_subscriber.index', [
            'subscribers' => Subscriber::orderByDesc('id')->paginate(10),
            'stats' => [
                'Total Subscribers' => Subscriber::count(),
                'Subscribed'        => Subscriber::where('status', 'subscribed')->count(),
                'Unsubscribed'      => Subscriber::where('status', 'unsubscribed')->count(),
                'Website Signups'   => Subscriber::where('source', 'website')->orWhere('source', 'home')->count(),
            ],
        ]);
    }

    public function delete($id)
    {
        try {
            Subscriber::findOrFail($id)->delete();

            return response()->json([
                'status'      => true,
                'message'     => ___('alert.successfully_deleted'),
                'status_code' => 200,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'      => false,
                'message'     => ___('alert.something_went_wrong'),
                'status_code' => 500,
            ], 500);
        }
    }
}
