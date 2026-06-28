<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenRouter (AI resume assist)
    |--------------------------------------------------------------------------
    |
    | Set MODEL_KEY in .env to your OpenRouter API key (no spaces around "=").
    | Use the full chat-completions URL below — do not set only the domain or
    | you will get HTTP 404 from OpenRouter.
    |
    */

    'api_key' => (($k = env('MODEL_KEY')) !== null && trim((string) $k) !== '') ? trim((string) $k) : null,

    'model' => env('OPENROUTER_MODEL', 'openrouter/free'),

    /*
     * Must be: https://openrouter.ai/api/v1/chat/completions
     * (Setting OPENROUTER_BASE_URL=https://openrouter.ai alone breaks the path.)
     */
    'chat_completions_url' => env(
        'OPENROUTER_CHAT_COMPLETIONS_URL',
        'https://openrouter.ai/api/v1/chat/completions'
    ),

];
