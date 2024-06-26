<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Connect Exist Integrations with Exist.io
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

                    <p class="mb-3 text-lg">
                        Before we can further configure Exist Integrations with several third-party data sources, we need to connect to your <a href="https://exist.io">Exist</a> account.
                    </p>

                    <p class="mb-3 font-light">
                        Click on the button to 'Connect Exist Integrations to Exist' to start the authorization workflow. It will take you to Exist where you'll authorize Exist Integrations to write data to your Exist account.
                    </p>

                    <div class="flex space-x-2 justify-center">
                        <div>
                            <form action="{{ route('exist.connect') }}" method="get">
                                <button type="submit" class="inline-block px-6 py-2 border-2 border-blue-600 text-blue-600 font-medium text-xs leading-tight uppercase rounded-full hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0 transition duration-150 ease-in-out">
                                    Connect Exist Integrations to Exist
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
