<?php

namespace App\Api\V1\Controllers\Admin;

use App\Helpers\AdminListing;
use App\Models\Device;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class DeviceController
 *
 * @package App\Api\V1\Controllers\Admin
 */
class DeviceController extends Controller
{
    use AdminUserCheckTrait;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return array|Factory|View
     */
    public function index(Request $request)
    {
        // admin check
        if(($response = $this->adminUserCheck($request)) !== true){
            return $response;
        }

        // Return json items list for ajax
        $validator = Validator::make($request->all(), [
            'orderBy' => 'in:id,name,device_id,user_id|nullable',
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
        $data = AdminListing::create(Device::class)->processRequestAndGet(
            $request,

            // set columns to query
            [
                'id',
                'name',
                'device_id',
                'user_id'
            ],

            // set columns to searchIn
            [
                'id',
                'name',
                'device_id'
            ]
        );

        // Return bulk items
        if ($request->has('bulk')) {
            $data = ['bulkItems' => $data->pluck('id')];
        }

        // Return json items list by ajax
        return response()->jsonApi(json_decode($data->toJson()), 200);
    }
}
