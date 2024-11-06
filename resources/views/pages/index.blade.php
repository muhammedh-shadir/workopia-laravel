<x-layout>
    <h2 class="text-center text-3xl mb-4 font-bold border border-gray-300 p-3">
        Recent Jobs
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @forelse ($jobs as $job)
            <x-job-card :job="$job" />
        @empty
            <p>No job available</p>
        @endforelse
    </div>
    <x-button-link url="/jobs" bgClass="" hoverClass="" :block="true" icon="arrow-alt-circle-right" textClass="text-xl text-center">Show All Jobs</x-button-link>
    <x-bottom-banner />
</x-layout>