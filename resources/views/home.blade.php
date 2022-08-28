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
                            <form action="{{ route('exist.manage') }}" method="get">
                                @csrf
                                <button type="submit" class="inline-block px-6 py-2.5 bg-blue-400 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-500 hover:shadow-lg focus:bg-blue-500 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-600 active:shadow-lg transition duration-150 ease-in-out">
                                    Manage Exist Settings
                                </button>
                            </form>
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

                    

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
