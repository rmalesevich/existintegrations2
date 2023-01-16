<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Display Logs for {{ Auth::user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="float-right">
                        <a href="{{ route('home') }}" class="inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                            {{ __('app.backToHome') }}
                        </a>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            Logs
                        </h3>
                    </div>

                @foreach ($logs as $log)
                    @php
                        if ($log->service == "ynab") {
                            $value = round($log->value / 1000, 2);
                        } else {
                            $value = $log->value;
                        }
                    @endphp
                    <div class="mb-2">
                        <strong>{{ $log->updated_at }}</strong> -
                    @if ($log->date_id !== null)
                        {{ $log->date_id }} - {{ $log->service }} - {{ $log->attribute }} - {{ $value }} - {{ $log->message }}
                    @else
                        {{ $log->service }} - {{ $log->message }}
                    @endif
                    </div>
                @endforeach

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
