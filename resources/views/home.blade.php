<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Exist Integrations for {{ Auth::user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                @if ($messageSet)
                    <div class="bg-purple-100 rounded-lg py-5 px-6 mb-4 text-base text-purple-700 mb-3" role="alert">
                        {{ $message }}
                    </div>
                @endif
                    
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

                    <div class="border m-4 p-4">
                        <div class="float-right">
                            <a href="{{ route('exist.manage') }}" class="inline-block px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">
                                Manage Exist Settings
                            </a>
                        </div>
                        <div class="mb-2">
                            <div class="text-gray-900 font-bold text-xl mb-2">
                                Exist Connection Status
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="text-sm text-gray-900">
                                <p>Connected as: {{ $user->existUser->username }}</p>
                                <p>Timezone: {{ $user->existUser->timezone }}</p>
                            </div>
                        </div>
                    </div>

                    @foreach ($integrations->where('enabled', true) as $integration)
                        @if ($user->integrationEnabled($integration['service']))
                        @php
                            $userType = $integration['service'] . 'User';
                        @endphp
                        <div class="border m-4 p-4">
                            <div class="float-right">
                                <a href="{{ route($integration['service'] . '.manage') }}" class="inline-block px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">
                                    {{ __('app.manageIntegrationText', ['service' => $integration['outputName']]) }}
                                </a>
                            </div>
                            <div class="mb-2">
                                <div class="text-gray-900 font-bold text-xl mb-2">
                                    {{ $integration['outputName'] }} Connection Status
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="text-sm text-gray-900">
                                    <p>Connected as: {{ $user->$userType->user }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach

                    <div class="m-4 p-4">
                        <div class="float-left">
                            <a href="{{ route('logs') }}" class="inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                                Display Logs
                            </a>
                        </div>
                        <div class="float-right">
                            <a href="{{ route('add') }}" class="inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                                {{ __('app.addNewIntegrationButton') }}
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
