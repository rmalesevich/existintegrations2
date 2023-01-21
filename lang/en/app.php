<?php

return [
    // HEADERS
    'backToHome' => 'Back to Home',
    'connectedAs' => 'Connected as :username',

    // MULTI-USE TEXT
    'initiateConnect' => 'Connect to :service',
    'accountProfileAPIFail' => 'Failed to retrieve profile information for your account from :service',
    'categoryAPIFail' => 'Failed to retrieve :category for your account from :service',
    'categoryAPISuccess' => 'Retrieved :category for your account from :service',
    'refreshCategory' => 'Refresh :category from :service',
    'unknownError' => 'Unknown error occurred.',
    'serviceDisconnect' => 'Exist Integrations has been successfully disconnected from your :service account',
    'alreadyIntegrated' => 'You are already connected to :service',

    // OAUTH TEXT
    'oAuthFlowCanceled' => ':service authorization flow was canceled.',
    'oAuthCodeError' => 'Failed to exchange OAuth code for the Token.',
    'oAuthRefreshError' => 'Failed to refresh OAuth Access Token from :service',
    'oAuthSuccess' => 'Exist Integrations has successfully connected to your :service account.',

    // ADD TEXT
    'noAvailableIntegrations1' => 'There are currently no integrations that Exist Integrations support that you aren\'t already using. Awesome job!',
    'noAvailableIntegrations2' => 'If you have other services that has an exposed API that could be added, please suggest them on our roadmap: ',

    // MANAGE TEXT
    'disconnectTitle' => 'If you disconnect your Exist Integrations account from :service, all data stored within Exist Integrations to support sending 3rd party data to Exist will be permanently deleted.',
    'disconnectConfirm' => 'Are you sure you want to disconnect this integration?',
    'disconnectButton' => 'Disconnect Exist Integrations from :service',
    'attributeHeader' => 'Configure the Attributes to send to Exist',
    'attributeButton' => 'Set the Attributes from :service to send to Exist',
    'attributeButtonTitle' => 'The included attributes from :service will be sent regularly to Exist',
    'attributeSuccess' => 'Exist Integrations has set up your attributes',
    'zeroOutHeader' => 'Correct Data Issues',
    'zeroOutButton' => 'Zero Out :service Data on Exist',
    'zeroOutText' => 'If you are experiencing issues with the data that has been sent to Exist for :service, you can initiate a Zero Out of the data for the last ' . config('services.baseDays') . ' days. On the next processing of :service data, it will first zero out the records in Exist and replace them with the latest information from :service.',
    'zeroOutConfirm' => 'Are you sure you want to zero out your data for these attributes?',
    'zeroOutSuccess' => ':service attributes will be reset for the last :days days',
    'addNewIntegrationButton' => '+ Add New Integration',
    'manageIntegrationText' => 'Manage :service Settings',
    'dropdownIgnore' => 'Ignore',

    // WHATPULSE ERRORS
    'whatpulsePulseError' => 'Error loading the pulses for this user',
    
    // TRAKT ERRORS
    'traktHistoryError' => 'Error loading the history for this user',

    // YNAB ERRORS
    'ynabHistoryError' => 'Error loading the transactions for this user',

    // SERVICES THAT NEED REQUIRED INFORMATION
    'addRequestedInformation1' => ':service :information:',
    'addRequestedInformation1Placeholder' => ':information',
    'addRequestedInformation1Required' => ':information is required',

];