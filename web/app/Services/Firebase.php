<?php

namespace App\Services;

use App\Models\ReferralCode;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;

class Firebase
{
    /**
     * Create dynamic link from google firebase service
     *
     * @param $referralCode
     * @param $application_id
     *
     * @return mixed
     */
    public static function linkGenerate($referralCode, $application_id)
    {
        $referrerData = [
            'utm_source' => $referralCode,
            'utm_medium' => ReferralCode::MEDIUM,
            'utm_campaign' => ReferralCode::CAMPAIGN,
            'utm_content' => $application_id
        ];

        try {
            $parameters = [
                'dynamicLinkInfo' => [
                    'domainUriPrefix' => config('firebase.dynamic_links.default_domain'),

                    'link' => sprintf("%s?referrer=%s", config('firebase.app_urls.website'), $referralCode),

                    'socialMetaTagInfo' => [
                        'socialTitle' => 'Sumra Net',
                        'socialDescription' => 'Follow me on Sumra network',
                        'socialImageLink' => 'https://sumra.net/css/logo.svg'
                    ],
                    'navigationInfo' => [
                        'enableForcedRedirect' => true,
                    ],
                    'analyticsInfo' => [
                        'googlePlayAnalytics' => [
                            'utmSource' => $referralCode,
                            'utmMedium' => ReferralCode::MEDIUM,
                            'utmCampaign' => ReferralCode::CAMPAIGN,
                            'utmContent' => $application_id,
                            /*
                            'utmTerm' => 'utmTerm',
                            'gclid' => 'gclid'
                            */
                        ],
                        /*
                          'itunesConnectAnalytics' => [
                            'at' => 'affiliateToken',
                            'ct' => 'campaignToken',
                            'mt' => '8',
                            'pt' => 'providerToken'
                          ]
                        */
                    ],
                    'androidInfo' => [
                        'androidPackageName' => $application_id,
                        'androidFallbackLink' => sprintf(
                            "%s?id=%s&referrer=%s",
                            config('firebase.app_urls.apple_store'),
                            $application_id,
                            urlencode(http_build_query($referrerData))
                        ),
                        //'androidMinPackageVersionCode' => '20040902'
                    ],
                    /*
                    'iosInfo' => [
                      'iosBundleId' => 'net.sumra.ios',
                      'iosFallbackLink' => 'https://fallback.domain.tld',
                      'iosCustomScheme' => 'customScheme',
                      'iosIpadFallbackLink' => 'https://ipad-fallback.domain.tld',
                      'iosIpadBundleId' => 'iPadBundleId',
                      'iosAppStoreId' => 'appStoreId'
                    ],
                    */
                ],
                'suffix' => [
                    'option' => 'SHORT'
                ]
            ];

            $dynamicLinks = app('firebase.dynamic_links');

            return $dynamicLinks->createDynamicLink($parameters);
        } catch (FailedToCreateDynamicLink $e) {
            return response()->jsonApi($e->getMessage());
        }
    }
}
