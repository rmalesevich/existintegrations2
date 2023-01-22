<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Integrations - Toggl Track
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Connect to Toggl Track
                    </h3>

                    <p class="mb-4">
                        To connect Exist Integrations to Toggl Track, you will need to get your API Token. In Toggl Track click on your profile and then Profile Settings. On that page you will need to copy the API Token.
                    </p>

                    <p class="mb-4">
                        From the Exist Integrations home press the {{ __('app.addNewIntegrationButton') }} button. Scroll down to Toggl Track, paste your API Token, and press the {{ __('app.initiateConnect', ['service' => 'Toggl Track']) }} button. You will be directed to the Manage page.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('app.manageIntegrationText', ['service' => 'Toggl Track']) }}
                    </h3>

                    <p class="mb-4">
                        After you have connected to Toggl Track, you must set the configure each Project to one of the Exist attributes.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Attributes
                    </h3>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Watching TV * - the total time watching television shows will be sent to the official watching TV attribute</li>
                        <li>Watching Movies *  - the total time watching movies will be sent to a custom attribute in Exist</li>
                        <li>Time gaming - the total time playing games will be sent to the official time gaming attribute</li>
                        <li>Productive time - the total time you were productive will be sent to the official productive time attribute</li>
                        <li>Neutral time - the total time you were neutral will be sent to the official neutral time attribute</li>
                        <li>Distracting time - the total time you were distracted will be sent to the official distracting time attribute</li>
                    </ul>

                    <p class="mb-4">
                        * These attributes are also supported with Trakt. If you have configured them with Trakt, you will not be able to configure them with Toggl Track.
                    </p>

                    <p class="mb-4">
                        Configure the attributes you wish to send to Exist and press the {{ __('app.attributeButton', ['service' => 'Toggl Track']) }} button.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('app.zeroOutHeader') }}
                    </h3>

                    <p class="mb-4">
                        If you are experiencing data issues with the data Exist Integrations is sending to Exist, you can initiative a 'Zero Out.' This will zero out all attributes for the last {{ env('BASE_DAYS') }} days. Then it will reprocess all pulses.
                    </p>

                    <p class="mb-4">
                        This can happen if a time entry is deleted or if you manually adjust the totals of the custom attributes from the Exist web or mobile apps.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Processor
                    </h3>

                    <p class="mb-4">
                        The Toggl Track Processor runs every hour at {{ env('TOGGL_HOUR') }} minutes past the hour. It will execute the following sequence:
                    </p>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Download all time entries from Toggl Track for the projects linked to an attribute for the last {{ env('BASE_DAYS') }} days</li>
                        <li>If no records are in the user_data, save the value for the attribute</li>
                        <li>If a record does exist, check if the date has changed. If the date has changed, send a negative value for the previous day and a positive value for the new day</li>
                        <li>If a record does exist, and the value has changed, save a value that is the difference between what Exist Integrations has sent and what the value is from Toggl Track</li>
                        <li>Increment any of the new data in Exist</li>
                        <li>Purge any data linked to your Toggl Track user over than {{ env('LOG_DAYS_KEPT') }} days.</li>
                    </ul>

                    <p class="mb-4">
                        Note: The Exist API currently doesn't allow negative values to be sent with the increment endpoint for time entries. If you are impacted, use the Zero Out functionality until Exist updates the API or Exist Integrations handles this use case.
                    </p>

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
