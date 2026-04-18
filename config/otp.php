<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mock OTP (development)
    |--------------------------------------------------------------------------
    |
    | When true, the API includes the generated OTP in the send-otp response
    | so clients can test without SMS. Disable in production.
    |
    */

    'expose_code_in_response' => env('OTP_EXPOSE_CODE') !== null
        ? filter_var(env('OTP_EXPOSE_CODE'), FILTER_VALIDATE_BOOL)
        : env('APP_ENV') !== 'production',

    /*
    |--------------------------------------------------------------------------
    | OTP length and TTL
    |--------------------------------------------------------------------------
    */

    'length' => 6,

    'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 600),

    /*
    |--------------------------------------------------------------------------
    | Fixed OTP (local development)
    |--------------------------------------------------------------------------
    |
    | When true, every send uses [fixed_code] so it matches the app constant
    | ApiService.demoOtp (123456). Default: true when APP_ENV=local.
    | Never enable in production.
    |
    */

    'use_fixed_code' => env('OTP_USE_FIXED_CODE') !== null
        ? filter_var(env('OTP_USE_FIXED_CODE'), FILTER_VALIDATE_BOOL)
        : env('APP_ENV') !== 'production',

    'fixed_code' => env('OTP_FIXED_CODE', '123456'),

];
