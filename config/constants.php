<?php

return [
    'config_json' => 'config.json',
    'msg_relogin_as_non_manager' => 'Re-login using non-manager account.',
    'msg_client_submits_too_often' => "You've already submitted request today.",
    'one_request_per_day' => env('ONE_REQUEST_PER_DAY', false),
    'records_per_page' => env('RECORDS_PER_PAGE', 10)
];
