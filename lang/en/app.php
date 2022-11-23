<?php

return [
    // HEADERS
    'backToHome' => 'Back to Home',
    'connectedAs' => 'Connected as :username',

    // MULTI-USE TEXT
    'zeroOutButton' => 'Zero Out :service Data on Exist',
    'zeroOutText' => 'If you are experiencing issues with the data that has been sent to Exist for :service, you can initiate a Zero Out of the data for the last ' . config('services.baseDays') . ' days. On the next processing of :service data, it will first zero out the records in Exist and replace them with the latest information from :service.',

];