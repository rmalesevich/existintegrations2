<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Exist Connection for {{ Auth::user()->name }}
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
                            <p>Connected as: {{ $user->existUser->username }}</p>
                            <p>Timezone: {{ $user->existUser->timezone }}</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <form action="{{ route('exist.updateAccountProfile') }}" method="post">
                            @csrf
                            <button 
                                type="submit" 
                                title="Exist Integrations uses your Timezone to properly aggregate data to the appropriate date to send to Exist. If your timezone changes on Exist, use this button to pull the latest information from Exist into Exist Integrations."
                                class="inline-block px-6 py-2.5 bg-purple-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-purple-700 hover:shadow-lg focus:bg-purple-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-purple-800 active:shadow-lg transition duration-150 ease-in-out">
                                Reload Account Profile from Exist
                            </button>
                        </form>
                    </div>

                    <div class="flex space-x-2 justify-center">
                        <div>
                            <form action="{{ route('exist.disconnect') }}" method="post">
                                @method('DELETE')
                                @csrf
                                <button 
                                    type="submit" 
                                    title="If you disconnect your Exist Integrations account from Exist, all data stored within Exist Integrations to support sending 3rd party data to Exist will be permanently deleted."
                                    class="inline-block px-6 py-2 border-2 border-red-600 text-red-600 font-medium text-xs leading-tight uppercase rounded-full hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                                    Disconnect Exist Integrations from Exist
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
