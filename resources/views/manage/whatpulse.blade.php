<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage WhatPulse Connection for {{ Auth::user()->name }}
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

                    <div class="mb-8">
                        <div class="text-sm text-gray-900">
                            <p>Connected as: {{ $user->whatpulseUser->user }}</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="text-sm text-gray-900">
                            <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                                Configure the Attributes to send to Exist
                            </h3>
                            <form action="{{ route('whatpulse.setAttributes') }}" method="post">
                            @csrf
                                <div class="mb-4">
                                @foreach ($attributes as $attribute)
                                <div class="form-check"> 
                                    <input class="form-check-input appearance-none h-4 w-4 border border-gray-300 rounded-sm bg-white checked:bg-blue-600 checked:border-blue-600 focus:outline-none transition duration-200 mt-1 align-top bg-no-repeat bg-center bg-contain float-left mr-2 cursor-pointer" type="checkbox" id="{{ $attribute['attribute'] }}" name="{{ $attribute['attribute'] }}"
                                        @if ($userAttributes->where('attribute', $attribute['attribute'])->first() !== null)
                                            checked
                                        @else
                                            {{ $attribute['attribute'] }}
                                        @endif
                                    >
                                    <label class="form-check-label inline-block text-gray-800" for="{{ $attribute['attribute'] }}">
                                        {{ $attribute['label'] }}
                                    </label>
                                </div>
                                @endforeach

                                </div>

                                <button 
                                    type="submit" 
                                    title="The included attributes from WhatPulse will be sent regularly to Exist"
                                    class="inline-block px-6 py-2.5 bg-purple-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-purple-700 hover:shadow-lg focus:bg-purple-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-purple-800 active:shadow-lg transition duration-150 ease-in-out">
                                    Set the Attributes from WhatPulse to send to Exist
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex space-x-2 justify-center">
                        <div>
                            <form action="{{ route('whatpulse.disconnect') }}" method="post">
                                @method('DELETE')
                                @csrf
                                <button 
                                    type="submit" 
                                    title="If you disconnect your Exist Integrations account from WhatPulse, all data stored within Exist Integrations to support sending 3rd party data to Exist will be permanently deleted."
                                    class="inline-block px-6 py-2 border-2 border-red-600 text-red-600 font-medium text-xs leading-tight uppercase rounded-full hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                                    Disconnect Exist Integrations from WhatPulse
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
