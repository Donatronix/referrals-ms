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
    ]
];


