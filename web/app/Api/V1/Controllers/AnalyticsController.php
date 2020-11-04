<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink;

/**
 * Class AnalyticsController
 *
 * @package App\Api\V1\Controllers
 */
class AnalyticsController extends Controller
{
    /**
     * Get analytics for referral link
     *
     * @OA\Get(
     *     path="/v1/referrals/analytics/byLink",
     *     summary="Get analytics for referral link",
     *     description="Get analytics for referral link",
     *     tags={"Analytics"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
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
     *     @OA\Parameter(
     *         name="dynamic_link",
     *         description="Dynamic Link",
     *         in="query",
     *         example="https://smr.page.link/MD7S",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="duration_days",
     *         description="Duration in days",
     *         in="query",
     *         example="14",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success getting analytics for referral link"
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
     * Get analytics for referral link
     *
     * @param Request $request
     *
     * @return mixed
     *
     * @throws FailedToGetStatisticsForDynamicLink
     */
    public function index(Request $request)
    {
        // Check Package Name
        $packageName = $request->get('package_name', Link::ANDROID_PACKAGE_NAME);

        if ($request->has('dynamic_link')) {
            $referralLink = $request->get('dynamic_link');
        } else {
            // Get invite object for user
            $link = Link::where('user_id', Auth::user()->getAuthIdentifier())
                ->where('package_name', $packageName)
                ->first();

            if (!$link) {
                return response()->jsonApi([
                    'type' => 'error',
                    'title' => 'Data Not Found',
                    'message' => 'Dynamic link not found for this user and package'
                ], 400);
            }

            $referralLink = $link->referral_link;
        }

        $dynamicLinks = app('firebase.dynamic_links');

        // Get statistic info
        try {
            $stats = $dynamicLinks->getStatistics($referralLink, $request->get('duration_days', 7));
            $eventStats = $stats->eventStatistics();

            $info = [
                'allClicks' => $eventStats->clicks(),
                'allRedirects' => $eventStats->redirects(),
                'allAppInstalls' => $eventStats->appInstalls(),
                'allAppFirstOpens' => $eventStats->appFirstOpens(),
                'allAppReOpens' => $eventStats->appReOpens(),
                'allAndroidEvents' => $eventStats->onAndroid(),
                'allDesktopEvents' => $eventStats->onDesktop(),
                'allIOSEvents' => $eventStats->onIOS(),
                'clicksOnDesktop' => $eventStats->clicks()->onDesktop(),
                'appInstallsOnAndroid' => $eventStats->onAndroid()->appInstalls(),
                'appReOpensOnIOS' => $eventStats->appReOpens()->onIOS(),
                'totalAmountOfClicks' => count($eventStats->clicks()),
                'totalAmountOfAppFirstOpensOnAndroid' => $eventStats->appFirstOpens()->onAndroid()->count(),
                'custom' => $eventStats->filter(function (array $eventGroup) {
                    return $eventGroup['platform'] === 'CUSTOM_PLATFORM_THAT_THE_SDK_DOES_NOT_KNOW_YET';
                }),
                'otherData' => $stats->rawData()
            ];
        } catch (FailedToGetStatisticsForDynamicLink $e) {
            return response()->jsonApi($e->getMessage());
        }

        // Return response
        return response()->jsonApi($info, 200);
    }

    /**
     * Get installed app, but is unregistered users (is filter by referral code also)
     *
     * @OA\Get(
     *     path="/v1/referrals/analytics/unregistered",
     *     summary="Get unregistered users (by referral code)",
     *     description="Get installed app, without registered users. If send referral code then get list by refcode",
     *     tags={"Analytics"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="referrer_code",
     *         in="query",
     *         description="Referrer code",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success getting unregistered users (by referral code)"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function unregistered(Request $request)
    {
        $list = Application::where('user_id', 0)
            ->when($request->has('referrer_code'), function ($q) use ($request) {
                return $q->where('referrer_code', $request->get('referrer_code'));
            })
            ->get()->toArray();

        // Return response
        return response()->jsonApi($list, 200);
    }
}
