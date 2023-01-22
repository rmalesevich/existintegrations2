<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Integrations - Trakt
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Connect to YNAB
                    </h3>    
                
                    <p class="mb-4">
                        To connect Exist Integrations to YNAB, you will have to go through the OAuth workflow from YNAB.
                    </p>

                    <p class="mb-4">
                        From the Exist Integrations home press the {{ __('app.addNewIntegrationButton') }} button. Scroll down to YNAB, and press the {{ __('app.initiateConnect', ['service' => 'YNAB']) }} button. You will be sent to YNAB. When requested authorize Exist Integrations. You will be redirected back to Exist Integrations.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('app.manageIntegrationText', ['service' => 'YNAB']) }}
                    </h3>

                    <p class="mb-4">
                        After you have connected to YNAB, you must set the configure each Budget Category to one of the Exist attributes.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Attributes
                    </h3>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Money spent - the total money spent on a given day sent to the official Exist attribute</li>
                        <li>Money earned - the total money earned on a given day sent to a custom attribute in Exist</li>
                        <li>Money saved - the total money saved on a given day sent to a custom attribute in Exist</li>
                    </ul>

                    <p class="mb-4">
                        Configure the attributes you wish to send to Exist and press the {{ __('app.attributeButton', ['service' => 'YNAB']) }} button.
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
                        The YNAB Processor runs every hour at {{ env('YNAB_HOUR') }} minutes past the hour. It will execute the following sequence:
                    </p>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Download all transactions from YNAB for the last 14 days</li>
                        <li>Check if the Category for the transaction is configured and identify the attribute</li>
                        <li>If no records are in the user_data, save the value for the attribute</li>
                        <li>If a record does exist, check if the date has changed. If the date has changed, send a negative value for the previous day and a positive value for the new day</li>
                        <li>If a record does exist, and the value has changed, save a value that is the difference between what Exist Integrations has sent and what the value is from YNAB.</li>
                        <li>Increment any of the new data in Exist</li>
                        <li>Purge any data linked to your Trakt user over than {{ env('LOG_DAYS_KEPT') }} days.</li>
                    </ul>

                    <p class="mb-4">
                        Note: Exist Integrations no longer supports sub-transactions. Any transaction that is split will not be processed into Exist Integrations. Deleted transactions don't also seem to be sending to Exist Integrations, so if you notice differences please utilize the 'Correct Data Issues' section.
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
