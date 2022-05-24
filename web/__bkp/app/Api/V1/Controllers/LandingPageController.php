<?php

namespace App\Api\V1\Controllers;

use App\Models\LandingPage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Class LandingPageController
 *
 * @package App\Api\V1\Controllers
 */
class LandingPageController extends Controller
{
    /**
     * LandingPage Controller
     *
     * @OA\Get(
     *     path="/landing-pages",
     *     description="Get all user's landing pages",
     *     tags={"Landing pages"},
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
     *     @OA\Response(
     *         response="200",
     *         description="List of all landing pages"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function index(): JsonResponse
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        try {
            $usersPages = LandingPage::byOwner($user_id);
            $pages = [];
            foreach ($usersPages as $p) {
                $pages[] = [
                    'template_id' => $p->template_id,
                    'html' => $p->template->html,
                    'jsonarray' => json_decode($p->json),
                ];
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }

        // Return response
        return response()->json([
            'success' => true,
            'data' => $pages,
        ], 200);
    }

    /**
     * Landing page Controller
     *
     * @OA\Post(
     *     path="/landing-pages",
     *     description="Save landing page",
     *     tags={"Landing pages"},
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
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="id",
     *                  type="integer",
     *                  description="Landing Page ID. Empty for new page",
     *                  example="1"
     *              ),
     *              @OA\Property (
     *                  property="template_id",
     *                  type="integer",
     *                  description="Template id for new page",
     *                  example=""
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Save successfull"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        try {
            if (isset($request->id)) {
                $page = LandingPage::find(intval($request->id));
                if ($page->user_id != $user_id) {
                    throw new Exception('Invalid user');
                }
            } else {
                $page = new LandingPage();
                $page->user_id = $user_id;
                $page->template_id = intval($request->template_id);
                if ($page->template_id == 0) {
                    throw new Exception('Invalid template');
                }
            }
            $page->json = json_encode($request->jsonarray);
            $page->save();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }

        // Return response
        return response()->json([
            'success' => true,
            'data' => $page->id,
        ], 200);
    }
}
