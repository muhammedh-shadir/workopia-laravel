<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $bookmarks = $user->bookmarkedJobs()->paginate(9);
        return view('jobs.bookmarked')->with('bookmarks', $bookmarks);
    }

    public function store(Job $job)
    {
        $user = Auth::user();
        if ($user->bookmarkedJobs()->where('job_id', $job->id)->exists()) {
            return back()->with('error', 'Job is already bookmarked');
        }

        $user->bookmarkedJobs()->attach($job->id);
        return back()->with('success', 'Job bookmarked successfully!');
    }

    public function destroy(Job $job)
    {
        $user = Auth::user();
        if (!$user->bookmarkedJobs()->where('job_id', $job->id)->exists()) {
            return back()->with('error', 'Job is not bookmarked');
        }

        $user->bookmarkedJobs()->detach($job->id);
        return back()->with('success', 'Bookmarked removed successfully!');
    }
}
