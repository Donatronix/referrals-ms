<?php


namespace App\Api\V1\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Landingpage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LandingpageController extends Controller
{
    /**
     * Landingpage Controller
     *
     * @OA\Get(
     *     path="/v1/referrals/landingpage",
     *     description="Get all user's landingpages",
     *     tags={"Landingpages"},
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
     *         description="List of all landingpages"
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
    public function index() : JsonResponse
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        try {
            $userspages = Landingpage::where('user_id', $user_id);
            $pages = [];
            foreach($userspages as $p) {
                $pages[] = [
                    'template_id'=>$p->template_id,
                    'html'=>$p->template->html,
                    'jasonarray' => json_decode($p->json)
                ];
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $pages
        ], 200);
    }

    /**
     * Landingpage Controller
     *
     * @OA\Post(
     *     path="/v1/referrals/landingpage/{id:[\d*]}",
     *     description="Save landingpage",
     *     tags={"Landingpages"},
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
     *         name="id",
     *         in="path",
     *         description="Landingpage ID. Empty for new page",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="template_id",
     *         description="Template id for new page",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
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
    public function save(Request $request) : JsonResponse
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        try {
            if(isset($request->id)) {
                $page = Landingpage::find(intval($request->id));
                if($page->user_id!=$user_id) {
                    throw new Exception('Invalid user');
                }
            } else {
                $page = new Landingpage();
                $page->user_id = $user_id;
                $page->template_id = intval($request->template_id);
                if($page->template_id==0) {
                    throw new Exception('Invalid template');
                }
            }
            $page->json = json_encode($request->jsonarray);
            $page->save();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $page->id
        ], 200);
    }

}
