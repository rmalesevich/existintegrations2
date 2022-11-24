<?php

return [
    // HEADERS
    'backToHome' => 'Back to Home',
    'connectedAs' => 'Connected as :username',

    // MULTI-USE TEXT
    'zeroOutButton' => 'Zero Out :service Data on Exist',
    'zeroOutText' => 'If you are experiencing issues with the data that has been sent to Exist for :service, you can initiate a Zero Out of the data for the last ' . config('services.baseDays') . ' days. On the next processing of :service data, it will first zero out the records in Exist and replace them with the latest information from :service.',
    'initiateConnect' => 'Connect to :service',
    'accountProfileAPIFail' => 'Failed to retrieve profile information for your account from :service',
    'unknownError' => 'Unknown error occurred.',

    // OAUTH TEXT
    'oAuthFlowCanceled' => ':service authorization flow was canceled.',
    'oAuthCodeError' => 'Failed to exchange OAuth code for the Token.',
    'oAuthSuccess' => 'Exist Integrations has successfully connected to your :service account.',

    // ADD TEXT
    'noAvailableIntegrations1' => 'There are currently no integrations that Exist Integrations support that you aren\'t already using. Awesome job!',
    'noAvailableIntegrations2' => 'If you have other services that has an exposed API that could be added, please suggest them on our roadmap: ',

];