<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'poppler' => [
        'pdftoppm' => env('PDFTOPPM_BINARY'),
        'pdfinfo' => env('PDFINFO_BINARY'),
    ],

    'similarity' => [
        'python' => env('SIMILARITY_PYTHON', 'python'),
        'python_path' => env('SIMILARITY_PYTHONPATH'),
        'reranker' => env('SIMILARITY_RERANKER', 'cross-encoder/ms-marco-MiniLM-L-6-v2'),
        'timeout' => env('SIMILARITY_TIMEOUT', 600),
        'request_timeout' => env('SIMILARITY_REQUEST_TIMEOUT', 600),
        'max_upload_kb' => env('SIMILARITY_UPLOAD_MAX_KB', 51200),
    ],

];
