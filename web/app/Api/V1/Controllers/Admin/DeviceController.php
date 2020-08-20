<?php

namespace App\Api\V1\Controllers\Admin;

use App\Helpers\AdminListing;
use App\Http\Requests\Admin\Device\BulkDestroyDevice;
use App\Http\Requests\Admin\Device\DestroyDevice;
use App\Models\Device;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return array|Factory|View
     */
    public function index(Request $request)
    {
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

        $data = AdminListing::create(Device::class)->processRequestAndGet(
            $request,

            // set columns to query
            ['id', 'name', 'device_id', 'user_id'],

            // set columns to searchIn
            ['id', 'name', 'device_id']
        );

        // Return bulk items
        if ($request->has('bulk')) {
            $data = ['bulkItems' => $data->pluck('id')];
        }

        // Return json items list by ajax
        return $data->toJson();
    }
}
