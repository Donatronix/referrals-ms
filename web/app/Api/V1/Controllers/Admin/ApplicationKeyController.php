<?php

namespace App\Api\V1\Controllers\Admin;

use App\Helpers\AdminListing;
use App\Http\Controllers\Controller;
use App\Models\ApplicationKey;
use App\Traits\AdminUserCheckTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApplicationKeyController
 *
 * @package App\Api\V1\Controllers
 */
class ApplicationKeyController extends Controller
{
    use AdminUserCheckTrait;

    /**
     * Method for get list all application keys
     *
     * @OA\Get(
     *     path="/v1/referrals/admin/application-keys",
     *     description="Get application keys",
     *     tags={"Admin"},
     *
     *     @OA\Parameter(
     *         name="user-id",
     *         description="User ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
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
            'orderBy' => 'in:id,version_key,cipher,cipher_key|nullable',
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
        $data = AdminListing::create(ApplicationKey::class)->processRequestAndGet(
            $request,

            // set columns to query
            ['id', 'version_key', 'cipher', 'cipher_key'],

            // set columns to searchIn
            ['id', 'version_key', 'cipher', 'cipher_key']
        );

        // Return bulk items
        if ($request->has('bulk')) {
            $data = ['bulkItems' => $data->pluck('id')];
        }

        // Return json items list by ajax
        return response()->jsonApi(json_decode($data->toJson()), 200);
    }
}
