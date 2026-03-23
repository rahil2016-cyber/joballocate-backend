<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company registration (until admin panel)
    |--------------------------------------------------------------------------
    |
    | When true, new employer accounts get verification_status=verified immediately.
    | Set to false when admin moderation is implemented.
    |
    */

    'auto_verify_new_companies' => filter_var(
        env('AUTO_VERIFY_NEW_COMPANIES', true),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | New job posts (until admin panel)
    |--------------------------------------------------------------------------
    |
    | When true, new jobs are published to the public board immediately.
    | Set to false to require admin approval (pending_review) again.
    |
    */

    'auto_publish_new_jobs' => filter_var(
        env('AUTO_PUBLISH_NEW_JOBS', true),
        FILTER_VALIDATE_BOOL
    ),

];
