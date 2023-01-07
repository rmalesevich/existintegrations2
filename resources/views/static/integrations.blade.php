<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Integrations
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <p class="mb-4">
                        This page contains links to various pages that describe how each integration works.
                    </p>

                    <ul class="mb-4 list-disc list-inside">
                        <li><a href="{{ route('integrations.whatpulse') }}" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">WhatPulse</a></li>
                        <li><a href="{{ route('integrations.trakt') }}" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">Trakt</a></li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
