<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Exist Integrations
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <p class="mb-4">
                        <a href="https://exist.io" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">Exist</a> is an amazing service that collects data from numerous services into your Exist Dashboard. With the Exist Dashboard you can understand your behavior like how the weather could impact your weight, or if your steps affect your mood.
                    </p>

                    <p class="mb-4">
                        Exist Integrations extends the value you can get from Exist by connecting additional data sources that aren't directly supported by Exist.
                    </p>

                    <p class="mb-4">
                        Exist Integrations works with:
                    </p>

                @php
                    $count = 0;
                @endphp

                @foreach ($integrations->where('enabled', true) as $integration)
                    @if ($loop->first || $count % 3 == 0)
                        <div class="grid grid-cols-3 gap-4 flex items-center mb-4">
                    @endif
                    <div class="m-4">
                        <img src="{{ $integration['logo'] }}" alt="{{ $integration['outputName'] }} Logo" class="mb-4"><br />
                        {{ $integration['description'] }}
                    </div>
                    @if ($loop->last || $count % 3 == 2)
                        </div>
                    @endif

                    @php
                        $count++;
                    @endphp

                @endforeach

                    <p class="mb-4">
                        <a href="{{ config('services.roadmapUri') }}" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">More integrations and improvements</a> are always being worked on!
                    </p>

                    <p class="mb-4">
                        <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">Register</a> today! Exist Integrations is a <strong>completely free service</strong>.
                    </p>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
