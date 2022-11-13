<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add new Integrations for {{ Auth::user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                @if (session()->has('errorMessage'))
                    <div class="bg-red-100 rounded-lg py-5 px-6 mb-4 text-base text-red-700 mb-3">
                        {{ session()->get('errorMessage') }}
                    </div>
                @endif

                @if (session()->has('successMessage'))
                    <div class="bg-green-100 rounded-lg py-5 px-6 mb-4 text-base text-green-700 mb-3">
                        {{ session()->get('successMessage') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 rounded-lg py-5 px-6 mb-4 text-base text-red-700 mb-3">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $availableIntegrations = 0
                @endphp

                @foreach ($integrations->where('enabled', true) as $integration)
                    @if (!$user->integrationEnabled($integration['service']))
                    <div class="m-4 p-4">
                        <img src="{{ $integration['logo'] }}" alt="{{ $integration['outputName'] }} Logo" class="mb-4">

                        <p class="mb-4">
                            {{ $integration['description'] }}
                        </p>

                        @include('add.' . $integration['service'])
                    </div>

                        @php
                            $availableIntegrations++;
                        @endphp
                    @endif
                @endforeach

                @if ($availableIntegrations == 0)
                    <div class="m-4 p-4">
                        <p class="mb-4">
                            There are currently no integrations that Exist Integrations support that you aren't already using. Awesome job!
                        </p>
                        <p class="mb-4">
                            If you have other services that has an exposed API that could be added, please suggest them on our <a href="{{ config('services.roadmapUri') }}" class="text-blue-600 hover:text-blue-700 transition duration-300 ease-in-out mb-4">roadmap</a>.
                        </p>
                    </div>
                @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
