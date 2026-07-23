<?php

return [
    'api' => [
        'title' => 'Landing Page API',
        'description' => 'REST API for developer landing page with contact form, health check, metrics, and AI sentiment analysis.',
        'version' => '1.0.0',

        // OpenAPI spec version
        'spec_version' => '3.0.0',

        // Where to store the generated docs
        'docs' => storage_path('api-docs'),

        // Where to store the generated JSON file
        'docs-json' => storage_path('api-docs/api-docs.json'),

        // Path to the JSON/YAML output for the browser
        'docs-url' => '/api-docs',

        // Middleware for docs access
        'middleware' => [],

        // Annotations paths
        'annotations' => [
            base_path('app/Http/Controllers'),
            base_path('app/Http/Requests'),
            base_path('app/Http/Resources'),
        ],

        // Generate YAML (true) or JSON (false)
        'generate-always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),

        // Additional JSON configuration
        'json' => [
            'info' => [
                'contact' => [
                    'email' => 'support@example.com',
                ],
            ],
        ],
    ],
];
