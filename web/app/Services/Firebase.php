<?php

namespace App\Services;

use App\Models\Link;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;

class Firebase
{
    /**
     * @param $referralCode
     * @param $packageName
     *
     * @return mixed
     */
    public static function linkGenerate($referralCode, $packageName)
    {
        $referrerData = [
            //'code' => $referralCode,
            'utm_source' => $referralCode,
            'utm_medium' => Link::MEDIUM,
            'utm_campaign' => Link::CAMPAIGN,
            'utm_content' => $packageName
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
                            'utmMedium' => Link::MEDIUM,
                            'utmCampaign' => Link::CAMPAIGN,
                            'utmContent' => $packageName,
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
                        'androidPackageName' => $packageName,
                        'androidFallbackLink' => sprintf(
                            "%s?id=%s&referrer=%s",
                            config('firebase.app_urls.apple_store'),
                            $packageName,
                            urlencode(http_build_query($referrerData))
                        ),

                        //'androidMinPackageVersionCode' => Link::ANDROID_MIN_PACKAGE_VERSION
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
