<form action="{{ route('whatpulse.connect') }}" method="post">
    @csrf
    <div>
        <div class="mb-3 xl:w-96">
            <label for="whatpulseAccountName" class="form-label inline-block mb-2 text-gray-700">
            {{ __('app.addRequestedInformation1', ['service' => 'WhatPulse', 'information' => 'Account Name']) }}
            </label>
            <input type="text" class="form-control block w-full px-3 py-1.5 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-solid border-gray-300 rounded transition ease-in-out  m-0 focus:text-gray-700 focus:bg-white focus:border-blue-600 focus:outline-none" id="whatpulseAccountName" name="whatpulseAccountName" placeholder="{{ __('app.addRequestedInformation1Placeholder', ['information' => 'account']) }}" />
        </div>
  
        <button type="submit" class="inline-block px-6 py-2.5 bg-green-500 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-green-600 hover:shadow-lg focus:bg-green-600 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-green-700 active:shadow-lg transition duration-150 ease-in-out">
            {{ __('app.initiateConnect', ['service' => 'WhatPulse']) }}
        </button>

    </div>
</form>