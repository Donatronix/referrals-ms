<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Total;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TotalController extends Controller
{
    /**
     *  Display a listing of the users.
     *
     *  @OA\Get(
     *     path="/v1/referrals/total",
     *     description="Get all users",
     *     tags={"Total"},
     *
     *     security={{
     *          "default" :{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     x={
     *          "auth-type": "Applecation & Application Use",
     *          "throttling-tier": "Unlimited",
     *          "wso2-appliocation-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *           },
     *     },
     *
     *     @OA\Response(
     *         response="200",
     *         description="Service Contracts list",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                     description="user uuid",
     *                     example="fd069ebe-cdea-3fec-b1e2-ca5a73c661fc",
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     description="user id",
     *                     example="edd72fdb-c83c-3e27-9047-d840e8745c61",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="username",
     *                     example="Lonzo",
     *                 ),
     *                  @OA\Property(
     *                      property="amount",
     *                      type="integer",
     *                      description="Number of invited users",
     *                      example=100,
     *                  ),
     *                 @OA\Property(
     *                      property="reward",
     *                      type="double",
     *                      description="Amount of remuneration",
     *                      example=50.50,
     *                  ),
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="ID not found"
     *              ),
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="username not found"
     *              ),
     *              @OA\Property(
     *                  property="amount",
     *                  type="string",
     *                  description="amount not found"
     *              ),
     *              @OA\Property(
     *                  property="reward",
     *                  type="string",
     *                  description="reward not found"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     *  @return \Illuminate\Http\Response
     */
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

    public function checkRemoteServices($input_data)
    {
        // Igor, this is demo data for the test. By connecting them, you don't need a remote microservice.
        // $input_data = \App\Services\TestService::showDataFromRemoteMembership();
        return \App\Services\RemoteService::accrualRemuneration($input_data);
    }
}
