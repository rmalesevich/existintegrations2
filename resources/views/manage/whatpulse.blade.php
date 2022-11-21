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

                    <div class="float-right">
                        <a href="{{ route('home') }}" class="inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                            Back to Home
                        </a>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            Connected as: {{ $user->whatpulseUser->user }}
                        </h3>
                    </div>

                    <div class="mb-12">
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
                                    class="inline-block px-6 py-2.5 bg-green-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-green-600 hover:shadow-lg focus:bg-green-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-700 active:shadow-lg transition duration-150 ease-in-out">
                                    Set the Attributes from WhatPulse to send to Exist
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            Correct Data Issues
                        </h3>
                    </div>

                    <div class="mb-12">
                        <div class="text-sm text-gray-900">
                            <p class="mb-4">
                                If you are experiencing issues with the data that has been sent to Exist, you can trigger Exist Integrations to zero out the data for the attributes that are configured. On the next processing of your data, it should correct the values.
                            </p>
                            <form action="{{ route('whatpulse.zero') }}" method="post">
                                @csrf
                                <button 
                                    type="submit" 
                                    onclick="return confirm('Are you sure you want to zero out your data for these attributes?');"
                                    class="inline-block px-6 py-2.5 bg-yellow-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-yellow-600 hover:shadow-lg focus:bg-yellow-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-yellow-700 active:shadow-lg transition duration-150 ease-in-out">
                                    Zero out WhatPulse data on Exist
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mb-4 pb-4">
                        <div class="float-right">
                            <form action="{{ route('whatpulse.disconnect') }}" method="post">
                                @method('DELETE')
                                @csrf
                                <button 
                                    type="submit" 
                                    title="If you disconnect your Exist Integrations account from WhatPulse, all data stored within Exist Integrations to support sending 3rd party data to Exist will be permanently deleted."
                                    onclick="return confirm('Are you sure you want to disconnect this integration?');"
                                    class="inline-block px-6 py-2.5 bg-red-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-lg transition duration-150 ease-in-out">
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
