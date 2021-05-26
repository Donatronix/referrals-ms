<?php


namespace App\Api\V1\Controllers;


use App\Services\Firebase;

use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Exception;

class ReferralCodeController extends Controller
{

    /**
     *  Get referral code and link
     *
     * @OA\Get (
     *     path="/v1/referrals/referral-codes",
     *     description="Get all user's referral codes and link",
     *     tags={"Referral Code"},
     *
     *     security={{
     *          "default": {
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          }
     *     }},
     *     x={
     *          "auth-type" : "Application & Application User",
     *          "wso-application-security": {
     *              "security-types": {"oauth2"},
     *              "optinal": "false"
     *           }
     *     },
     *
     *     @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="path",
     *          description="ID user",
     *          example="112",
     *          @OA\Schema (
     *              type="integer"
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="The list of showing the codes of one referral is successful."
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function index()
    {
        try{
            $currentUserId = Auth::user()->getAuthIdentifier();
            ReferralCode::byOwner()->get();

            return response()->jsonApi([
                'status' => 'success',
                'title' => "List referral",
                'message' => 'list referral successfully received'
            ], 200);
        }
        catch (Exception $e){
            return response()->jsonApi([
                'type' => 'error',
                'title' => "Not received list referral",
                'message' => "Data #{$currentUserId} not found"
            ], 404);
        }
    }

    /**
     * Show one code and link
     *
     * @OA\Get(
     *     path="/v1/referrals/referral-code/{id}",
     *     description="Show referral code and link",
     *     tags={"Referral Code"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *           }
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optimal": "false"
     *          }
     *     },
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID referral code",
     *          example="1",
     *          @OA\Schema (
     *              type="integer"
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        print_r(ReferralCode::find($id));
    }

    /**
     * Update referral link and code.
     *
     * @OA\Put(
     *     path="/v1/referrals/referral-codes/{id}",
     *     description="Update referral code and link",
     *     tags={"Referral Code"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          }
     *     },
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Referral ID",
     *          example="1",
     *          @OA\Schema (
     *              type="integer"
     *          ),
     *     ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="application_id",
     *                  type="string",
     *                  description="Application ID of error",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="referral_link",
     *                  type="string",
     *                  description="Referral link of error",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Code of error",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="is_default",
     *                  type="string",
     *                  description="Update default property",
     *                  example=""
     *              ),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *          response="500",
     *          description="Unknown error",
     *          @OA\JsonContent(
     *                  type="object",
     *                  @OA\Property(
     *                      property="package_name",
     *                      type="string",
     *                      description="Error package name"
     *                  ),
     *                  @OA\Property(
     *                      property="referral_link",
     *                      type="string",
     *                      description="Error referral link"
     *                  ),
     *                  @OA\Property(
     *                      property="code",
     *                      type="string",
     *                      description="Error code"
     *                  ),
     *              ),
     *     ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validation($request);

        try{
            $data = ReferralCode::find($id);
            $data->referral_link = $request->get('referral_link');
            $data->code = $request->get('code');
            $data->application_id = $request->get('application_id');
            $data->is_default = $request->get('default',false);
            $data->save();
        }
        catch (\Exception $e){
            return  response()->jsonApi([
                'type' => 'error',
                'title' => 'Referrals link not found',
                'message' => $e
//                'message' => "Referrals link #{$id} not found"
            ], 404);
        }
    }

    /**
     * Remove referral code
     *
     * @OA\Delete(
     *     path="/v1/referrals/referral-codes/{id}",
     *     description="Delete referral code",
     *     tags={"Referral Code"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         },
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
     *         description="Delete referral code by ID",
     *         required=true,
     *         example="",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Referral code not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Code of error"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ReferralCode::destroy($id);

        return null;
    }

    /**
     *  Create link and code user generated
     *
     * @OA\Post(
     *     path="/v1/referrals/referral-codes",
     *     summary="Create link and code for an existing user",
     *     description="Create link and code user generated",
     *     tags={"Referral Code"},
     *
     *     security={{
     *          "default": {
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          }
     *     }},
     *
     *     x={
     *          "auth-type" : "Application & Application Yser",
     *          "throtting-tier" : "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          }
     *     },
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="application_id",
     *                  type="string",
     *                  maximum="50",
     *                  description="Service ID",
     *                  example="net.sumra.chat"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success create link and code",
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Your request requires the required parameter application ID"
     *              ),
     *          ),
     *     ),
     * )
     */

    public function store(Request $request)
    {
        ///$application_id = "net.sumra.chat";
        $currentUserId = Auth::user()->getAuthIdentifier();
        $this->validation($request);

        $referral_cnt = ReferralCode::where('user_id', $currentUserId)->count();

        if($referral_cnt <= config('app.link_limit')){
            ReferralCode::sendDataToCreateReferralCode($currentUserId, $request->application_id);
            return response()->jsonApi('Operation successful', 200);
        }

        return response()->jsonApi('You have exceeded the limit on the number of links to this service', 200);
    }

    private function validation($request)
    {
        return $this->validate($request, [
            'user_id' => 'integer',
            'referral_link' => 'required|string|max:35',
            'code' => 'required|string|max:8|min:8',
            'is_default' => 'integer|max:1',
            'application_id' => 'string|max:30',
        ]);
    }
}
