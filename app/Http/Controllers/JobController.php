<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JobController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $jobs = Job::latest()->paginate(9);

        return view('jobs.index')->with('jobs', $jobs);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('jobs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|integer',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'tags' => 'nullable|string',
            'job_type' => 'required|string',
            'remote' => 'required|boolean',
            'address' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'nullable|string',
            'company_name' => 'required|string',
            'company_description' => 'nullable|string',
            'company_website' => 'nullable|url',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);


        $validatedData['user_id'] = Auth::id();

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            $validatedData['company_logo'] = $path;
        }

        Job::create($validatedData);

        return redirect()->route('jobs.index')->with('success', 'Job listing created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job): View
    {
        return view('jobs.show')->with('job', $job);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Job $job): View
    {
        $this->authorize('update', $job);
        return view('jobs.edit')->with('job', $job);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'required|integer',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'tags' => 'nullable|string',
            'job_type' => 'required|string',
            'remote' => 'required|boolean',
            'address' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'nullable|string',
            'company_name' => 'required|string',
            'company_description' => 'nullable|string',
            'company_website' => 'nullable|url',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($request->hasFile('company_logo')) {
            if ($job->company_logo) {
                Storage::disk('public')->delete($job->company_logo);
            }
            $path = $request->file('company_logo')->store('logos', 'public');
            $validatedData['company_logo'] = $path;
        }

        $job->update($validatedData);

        return redirect()->route('jobs.index')->with('success', 'Job listing updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job): RedirectResponse
    {
        $this->authorize('delete', $job);

        if ($job->company_logo) {
            // Storage::delete('public/logos' . $job->company_logo);
            Storage::disk('public')->delete($job->company_logo);
        }

        if (request()->query('from') == 'dashboard') {
            $job->delete();
            return redirect()->route('dashboard')->with('success', 'Job listing deleted successfully');
        }
        $job->delete();
        return redirect()->route('jobs.index')->with('success', 'Job listing deleted successfully');
    }

    public function search(Request $request): View
    {
        $keywords = strtolower($request->input('keywords'));
        $location = strtolower($request->input('location'));

        $query = Job::query();

        if ($keywords) {
            $query->where(function ($q) use ($keywords) {
                $q->whereRaw('LOWER(title) like ?', ['%' . $keywords . '%'])
                    ->orWhereRaw('LOWER(description) like ?', ['%' . $keywords . '%'])
                    ->orWhereRaw('LOWER(tags) like ?', ['%' . $keywords . '%']);
            });
        }

        if ($location) {
            $query->where(function ($q) use ($location) {
                $q->whereRaw('LOWER(address) like ?', ['%' . $location . '%'])
                    ->orWhereRaw('LOWER(city)like ?', ['%' . $location . '%'])
                    ->orWhereRaw('LOWER(state) like ?', ['%' . $location . '%'])
                    ->orWhereRaw('LOWER(zipcode) like ?', ['%' . $location . '%']);
            });
        }


        $jobs = $query->paginate(12);
        return view('jobs.index')->with('jobs', $jobs);
    }
}
