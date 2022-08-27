<?php

use Sumra\SDK\Helpers\Helper;

return (static function () {
    $settings = [
        /**
         * Referral code and link generation
         * Add a restriction on the presence of codes / links for a specific get parameter application ID
         */
        'referral_code' => [
            'limit' => env('REFERRAL_CODES_LIMIT', 10)
        ],

        /**
         *  Setting the number of months for a schedule
         */
        'quantity_month' => 6,
    ];

    return array_merge(Helper::getConfig('settings'), $settings);
})();
