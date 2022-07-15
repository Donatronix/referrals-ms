<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Template;
use Exception;

/**
 * Class TemplateController
 *
 * @package App\Api\V1\Controllers
 */
class TemplateController extends Controller
{
    /**
     * Template Controller
     *
     * @OA\Get(
     *     path="/admin/template",
     *     description="Get templates",
     *     tags={"Template"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
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
     *         response="401",
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
        try {
            $templates = Template::all();
            foreach ($templates as $t) {
                $t->jasonarray = json_decode($t->json);
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
            'data' => $templates,
        ], 200);
    }

    /**
     * Template Controller
     *
     * @OA\Post(
     *     path="/admin/template",
     *     description="Save template",
     *     tags={"Template"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property (
     *                  property="id",
     *                  type="integer",
     *                  description="Template ID. Empty for new template",
     *                  example="1"
     *              ),
     *              @OA\Property (
     *                  property="title",
     *                  type="string",
     *                  description="Template title",
     *                  example="test1"
     *              ),
     *              @OA\Property (
     *                  property="html",
     *                  type="string",
     *                  description="Template html",
     *                  example="test2"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Save successfull"
     *     ),
     *     @OA\Response(
     *         response="401",
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
        try {
            if (isset($request->id)) {
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
                'error' => $e->getMessage(),
            ], 400);
        }

        // Return response
        return response()->json([
            'success' => true,
            'data' => $template->id,
        ], 200);
    }
}
