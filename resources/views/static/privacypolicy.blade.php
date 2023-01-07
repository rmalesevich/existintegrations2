<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Privacy Policy
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <p class="mb-4">
                        This information applies to all information collected or submitted to the Exist Integrations website.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Information we collect and use
                    </h3>

                    <p class="mb-4">
                        Exist Integrations accounts require an email address to register and login. This is used for:
                    </p>

                    <ul class="mb-4 list-disc list-inside">
                        <li>Logging in to the site</li>
                        <li>Password resets</li>
                        <li>Sending important information about your account like integrations that have become disconnected</li>
                        <li>Responding to emails that you initiate to Exist Integrations</li>
                    </ul>

                    <p class="mb-4">
                        Exist Integrations does not send promotional emails.
                    </p>

                    <p class="mb-4">
                        Exist Integrations stores logs to ensure the functionality of the site is operating correctly. Personal details are scrubbed from the logs so they cannot be linked to individual accounts. The logs are purged from the server periodically.
                    </p>

                    <p class="mb-4">
                        Exist Integrations stores information linked to your account to support the integration between third-party services and Exist. The integrations are always initiative by you.
                    </p>

                    <p class="mb-4">
                        For more information on Exist, see their <a href="https://exist.io/privacy/" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">Privacy Policy</a>.
                    </p>

                    <p class="mb-4">
                        Exist Integrations does not share any data with outside parties beyond what is necessary to support sending your data to Exist.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Data stored in Exist Integrations linked to your account
                    </h3>

                    <p class="mb-4">
                        Exist Integrations stores only what data is necessary to support the integration between the third-party services and Exist. This includes basic profile information from each service. See the <a href="{{ route('integrations') }}" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">Integrations</a> page for information on how the integration pulls your data, what data is included, how it is sent to Exist, and how it is purged.
                    </p>

                    <p class="mb-4">
                        Any user data that is sent to Exist is stored on Exist Integrations for only 21  days before it is purged.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Security
                    </h3>

                    <p class="mb-4">
                        Exist Integrations implements a variety of security measures to help keep your information secure. All communication is done through HTTPS. Passwords are hashed using industry-standard methods. Logs are not associated with individual user accounts.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Accessing, changing, or deleting your information
                    </h3>

                    <p class="mb-4">
                        You may access, change, or delete your information with Exist Integrations at any time through the usage of the website.
                    </p>

                    <p class="mb-4">
                        By disabling integrations with third-party services, all data linked to that account is immediately deleted.
                    </p>

                    <p class="mb-4">
                        Exist Integrations may delete your information at any time for any reason, such as technical reasons, legal concerns, abuse prevention, removal of idle accounts or other reasons.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Contacting Exist Integrations
                    </h3>

                    <p class="mb-4">
                        If you have questions regarding this privacy policy, you may contact the <a href="mailto:ryan@malesevich.me" class="text-blue-500 hover:text-blue-700 transition duration-300 ease-in-out mb-4">developer</a>.
                    </p>

                    <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        Changes to this policy
                    </h3>

                    <p class="mb-4">
                        If we decide to change our privacy policy, we will post those changes on this page and summarize them here:
                    </p>

                    <ul class="mb-4 list-disc list-inside">
                        <li>2023 January 08: Removed the section on data stored related to each integration as it is more relevant in the Integrations page.</li>
                        <li>2023 January 07: Updated the email section to add “important account updates” to the acceptable usage of emails that may be sent from Exist Integrations.</li>
                        <li>2022 February 05: Migrated from the legacy Data Policy to this Privacy Policy.</li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
