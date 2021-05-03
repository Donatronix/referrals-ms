<?php


namespace App\Api\V1\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Template;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TemplateController extends Controller
{
    /**
     * Template Controller
     *
     * @OA\Get(
     *     path="/v1/referrals/admin/template",
     *     description="Get templates",
     *     tags={"Template Controller"},
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
     *         name="limit",
     *         description="count ot cards in return",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default = 20,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="page of list",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *              default = 1,
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="List of all templates"
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
        try {
            $templates = Template::all();
            foreach($templates as $t) {
                $t->jasonarray = json_decode($t->json);
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
            'data' => $templates
        ], 200);
    }

    /**
     * Template Controller
     *
     * @OA\Post(
     *     path="/v1/referrals/admin/template/{id:[\d*]}",
     *     description="Save template",
     *     tags={"Template Controller"},
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
     *         description="Template ID. Empty for new template",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         description="Template title",
     *         required=true,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="html",
     *         description="Template html",
     *         required=true,
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
        try {
            if(isset($request->id)) {
                $template = Template::find(intval($request->id));
            } else {
                $template = new Template();
            }
            $template->title = $request->title;
            $template->html = $request->html;
            $template->json = json_encode($request->jsonarray);
            $template->save();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $template->id
        ], 200);
    }

}
