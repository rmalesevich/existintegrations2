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

                <div class="float-right">
                    <a href="{{ route('home') }}" class="inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                        {{ __('app.backToHome') }}
                    </a>
                </div>

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
                            {{ __('app.noAvailableIntegrations1') }}
                        </p>
                        <p class="mb-4">
                            {{ __('app.noAvailableIntegrations2') }} <a href="{{ config('services.roadmapUri') }}" class="text-blue-600 hover:text-blue-700 transition duration-300 ease-in-out mb-4">roadmap</a>.
                        </p>
                    </div>
                @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
