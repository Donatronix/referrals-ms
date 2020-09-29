<?php

namespace App\Api\V1\Controllers\Admin;

use App\Helpers\AdminListing;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Traits\AdminUserCheckTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApplicationController
 *
 * @package App\Api\V1\Controllers
 */
class ApplicationController extends Controller
{
    use AdminUserCheckTrait;

    /**
     * Method for get list all applications for select user
     *
     * @OA\Get(
     *     path="/v1/referrals/admin/applications",
     *     description="Get applications",
     *     tags={"Admin"},
     *
     *     security={
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     },
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
     *         name="orderBy",
     *         description="Order By",
     *         required=false,
     *         in="query",
     *         @OA\Schema (
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderDirection",
     *         description="Order Direction",
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
     *         name="per_page",
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
     *         response=200,
     *         description="Success",
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \Exception
     */
    public function index(Request $request)
    {
        // admin check
        if(($response = $this->adminUserCheck($request)) !== true){
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'orderBy' => 'in:device_id,device_name,id,ip,metadata,package_name,referrer_code,referrer_id,referrer_status,user_id,user_status|nullable',
            'orderDirection' => 'in:asc,desc|nullable',
            'search' => 'string|nullable',
            'page' => 'integer|nullable',
            'per_page' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // create AdminListing instance for a specific model
        $data = AdminListing::create(Application::class)->processRequestAndGet(
            $request,

            // set columns to query
            [
                'id',
                'device_id',
                'device_name',
                'ip',
                'metadata',
                'package_name',
                'referrer_code',
                'referrer_id',
                'referrer_status',
                'user_id',
                'user_status'
            ],

            // set columns to searchIn
            [
                'id',
                'device_id',
                'device_name',
                'ip',
                'metadata',
                'package_name',
                'referrer_code'
            ],

            function ($query) use ($request) {
                $query->when($request->has('user_id'), function ($q) use ($request) {
                    return $q->where('user_id', $request->get('user_id'));
                });
            }
        );

        // Return bulk items
        if ($request->has('bulk')) {
            $data = ['bulkItems' => $data->pluck('id')];
        }

        // Return json items list by ajax
        return response()->jsonApi(json_decode($data->toJson()), 200);
    }
}
