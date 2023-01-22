<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Integrations - WhatPulse
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Connect to WhatPulse
                    </h3>    
                
                    <p class="mb-4">
                        To connect Exist Integrations to WhatPulse, you will only need your WhatPulse username and have your profile open to your WhatPulse public profile.
                    </p>

                    <p class="mb-4">
                        From the Exist Integrations home press the {{ __('app.addNewIntegrationButton') }} button. Scroll down to WhatPulse, enter in your username, and press the {{ __('app.initiateConnect', ['service' => 'WhatPulse']) }} button.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('app.manageIntegrationText', ['service' => 'WhatPulse']) }}
                    </h3>

                    <p class="mb-4">
                        After you have connected to WhatPulse, you must set the attributes you want sent to Exist. After you connect to WhatPulse you will automatically be sent to the page. If you leave, access the configuration from the home page.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Attributes
                    </h3>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Keystrokes - the total keys recorded in the pulse will be sent to the official Exist Keystrokes attribute</li>
                        <li>Mouse Clicks - the total mouse clicks recorded in the pulse will be sent to a custom attribute in Exist</li>
                        <li>Download MB - the total downloads in MB recorded in the pulse will be sent to a custom attribute in Exist</li>
                        <li>Upload MB - the total uploads in MB recorded in the pulse will be sent to a custom attribute in Exist</li>
                    </ul>

                    <p class="mb-4">
                        Select the attributes you wish to send to Exist and press the {{ __('app.attributeButton', ['service' => 'WhatPulse']) }} button.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('app.zeroOutHeader') }}
                    </h3>

                    <p class="mb-4">
                        If you are experiencing data issues with the data Exist Integrations is sending to Exist, you can initiative a 'Zero Out.' This will zero out all attributes for the last {{ env('BASE_DAYS') }} days. Then it will reprocess all pulses.
                    </p>

                    <p class="mb-4">
                        This can happen if a pulse is deleted or if you manually adjust the totals of the custom attributes from the Exist web or mobile apps.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Processor
                    </h3>

                    <p class="mb-4">
                        The WhatPulse Processor runs every hour at {{ env('WHATPULSE_HOUR') }} minutes past the hour. It will execute the following sequence:
                    </p>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Download all pulses from WhatPulse for the last {{ env('BASE_DAYS') }} days.</li>
                        <li>Save a record in the Exist Integrations database for each new pulse and selected attribute combinations.</li>
                        <li>Check if the {{ __('app.zeroOutHeader') }} was triggered. If it is, the attributes will be reset to 0 on Exist for the last {{ env('BASE_DAYS') }} days.</li>
                        <li>Increment the attribute value in Exist for each Pulse/Attribute combination by day.</li>
                        <li>Purge any data linked to your WhatPulse user over than {{ env('LOG_DAYS_KEPT') }} days.</li>
                    </ul>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Disconnect
                    </h3>

                    <p class="mb-4">
                        You can remove all data from Exist Integrations by disconnecting the service through the Management page. Exist Integrations will also disconnect the service if the service returns a 401 Unauthorized from the API.
                    </p>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
