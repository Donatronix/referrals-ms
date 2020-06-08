<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;

/**
 * Class InviteController
 * @package App\Api\V1\Controllers
 */
class InviteController extends Controller
{
    /**
     * Get user referrer invite code
     *
     * @OA\Get(
     *     path="/api/v1/referral/invite",
     *     summary="Get user invite code",
     *     description="Get user referrer invite code",
     *     tags={"Referral"},
     *
     *     @OA\Parameter(
     *         name="user-id",
     *         description="User ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="package_name",
     *         description="Package Name",
     *         in="query",
     *         example="net.sumra.chat",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success get or generate referrer invite code"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     */

    /**
     * Get user referrer invite code
     *
     * @param Request $request
     *
     * @return \Sumra\JsonApi\
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request)
    {
        $userId = $request->header('user-id');

        if ($userId === null) {
            abort(401, 'Unauthorized');
        }

        // Find app user object
        $user = User::where('app_user_id', $userId)->first();

        if(!$user){
            // create a new invite record
            $user = User::create([
                'app_user_id' => $userId,
                'user_name' => $request->header('username', null)
            ]);
        }

        // Check Package Name
        $packageName = $request->get('package_name', Link::ANDROID_PACKAGE_NAME);

        // Get link by user id and package name
        $link = Link::where('app_user_id', $user->app_user_id)->where('package_name', $packageName)->first();

        if(!$link){
            $referrerData = [
                //'code' => $user->referral_code,
                'utm_source' => $user->referral_code,
                'utm_medium' => Link::MEDIUM,
                'utm_campaign' => Link::CAMPAIGN,
                'utm_content' => $packageName
            ];

            // Create dynamic link from google firebase service
            $websiteUrl = 'https://sumra.net/discover?referrer=' . $user->referral_code;
            $googlePlayUrl = 'https://play.google.com/store/apps/details?id=' . $packageName . '&referrer=' . urlencode(http_build_query($referrerData));

            $parameters = [
                'dynamicLinkInfo' => [
                    'domainUriPrefix' => config('dynamic_links.default_domain'),
                    'link' => $websiteUrl,
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
                            'utmSource' => $user->referral_code,
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
                        'androidFallbackLink' => $googlePlayUrl,
                        //'androidMinPackageVersionCode' => Link::DEFAULT_ANDROID_MIN_PACKAGE_VERSION
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

            try {
                $shortLink = $dynamicLinks->createDynamicLink($parameters);
            } catch (FailedToCreateDynamicLink $e) {
                return response()->jsonApi($e->getMessage());
            }

            // Add
            $link = Link::create([
                'app_user_id' => $user->app_user_id,
                'package_name' => $packageName,
                'referral_link' => (string) $shortLink
            ]);
        }

        // Return dynamic link
        return response()->jsonApi([
            'referral_code' => $user->referral_code,
            'referral_link' => $link->referral_link
        ], 200);
    }
}
