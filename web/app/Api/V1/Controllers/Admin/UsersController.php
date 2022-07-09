<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Helpers\AdminListing;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UsersController
 *
 * @package App\Api\V1\Controllers
 */
class UsersController extends Controller
{
    /**
     * Method for get list all referral users
     *
     * @OA\Get(
     *     path="/admin/referrals-list",
     *     description="Get referral users",
     *     tags={"Admin"},
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
     *         name="sort[by]",
     *         description="Sort by field (....)",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort[order]",
     *         description="Sort order (asc, desc)",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         description="Search keywords",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         description="Number of page",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         description="Items per page",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="bulk",
     *         description="Bulk filter",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     )
     * )
     *
     * @param Request $request
     *
     * @return string
     * @throws Exception
     */
    public function index(Request $request): string
    {
        // Validate data
        $validator = Validator::make($request->all(), [
            'sort.by' => 'in:referral_code,referrer_id,status,id,username|nullable',
            'sort.order' => 'in:asc,desc|nullable',
            'search' => 'string|nullable',
            'page' => 'integer|nullable',
            'limit' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // create AdminListing instance for a specific model
        $data = AdminListing::create(User::class)->processRequestAndGet(
            $request,

            // set columns to query
            [
                'id',
                'username',
                'referral_code',
                'referrer_id',
                'status',
            ],

            // set columns to searchIn
            [
                'referral_code',
                'username',
            ]
        );

        // Return bulk items
        if ($request->has('bulk')) {
            $data = ['bulkItems' => $data->pluck('id')];
        }

        // Return json items list by ajax
        return response()->jsonApi(json_decode($data->toJson()), 200);
    }

    /**
     * Get detail info about user
     *
     * @OA\Get(
     *     path="/admin/referrals-list/{id}",
     *     summary="Get detail info about user",
     *     description="Get detail info about user",
     *     tags={"Admin"},
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
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Data of user"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="error",
     *                  type="object",
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      description="code of error"
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      description="error message"
     *                  )
     *              )
     *          )
     *     )
     * )
     *
     * Get detail info of user
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id): mixed
    {
        // Get user model
        try {
            // Get and return user data
            $user = User::findOrFail($id)->toArray();

            return response()->jsonApi($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'User not found',
                'message' => "User #{$id} not found",
            ], 404);
        }
    }
}
