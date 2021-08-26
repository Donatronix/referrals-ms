<?php

return [
    /**
     * Pagination
     */
    'pagination_limit' => env('APP_PAGINATION_LIMIT', 10),

    /**
     * Microservices API
     */
    'api' => [

    ],

    /**
     * RabbitMQ Exchange Points
     */
    'exchange_queue' => [
        'contacts_book' => env('RABBITMQ_RECEIVER_CONTACTS', 'ContactsBookMS')
    ],

    /**
     * Referral code and link generation
     * Add a restriction on the presence of codes / links for a specific get parameter application ID
     */
    'referral_code' => [
        'limit' => env('REFERRAL_CODES_LIMIT', 10)
    ],

    'application_version_key' => env('APPLICATION_VERSION_KEY', null),
];


