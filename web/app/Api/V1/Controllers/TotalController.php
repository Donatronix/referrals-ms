<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Total;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TotalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = Total::paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->jsonApi(
                array_merge([
                    'type' => 'success',
                    'title' => 'Operation was success',
                    'message' => 'Users were shown successfully',
                ], $users->toArray()),
                200);

        }
        catch (ModelNotFoundException $e){
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all users",
                'data' => null
            ], 404);
        }
    }
}
