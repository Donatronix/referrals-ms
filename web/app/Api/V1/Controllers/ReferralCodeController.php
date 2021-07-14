<?php

namespace App\Api\V1\Controllers;

use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @return mixed
     * @throws ValidationException
     */
    public function index()
    {
        try {
            $codes = ReferralCode::byOwner()->get();

            return response()->jsonApi([
                'status' => 'success',
                'title' => "List referral",
                'message' => 'list referral successfully received',
                'codes' => $codes
            ], 200);
        } catch (Exception $e) {
            $currentUserId = Auth::user()->getAuthIdentifier();

            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Not received list",
                'message' => "Data #{$currentUserId} not found"
            ], 404);
        }
    }

    /**
     * Show one code and link
     *
     * @OA\Get(
     *     path="/v1/referrals/referral-codes/{id}",
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
     *          required=true,
     *          description="ID referral code",
     *          example="1",
     *          @OA\Schema (
     *              type="string"
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
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $query = ReferralCode::find($id);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Showing one link",
                'message' => 'One link successfully shown',
                'row' => $query,
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Not found ID",
                'message' => "#{$id} not found"
            ], 404);
        }
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
     *          required=true,
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
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input_data = (object)$this->validate($request, $this->rules());

        try {
            $data = ReferralCode::find($id);
            $data->referral_link = $input_data->referral_link;
            $data->code = $input_data->code;
            $data->application_id = $input_data->application_id;
            $data->is_default = $input_data->default;
            $data->save();

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Updating success",
                'message' => 'The referral link field update has been successfully updated'
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Link not found',
                'message' => "Referrals link #{$id} for updated not found"
            ], 404);
        }
    }

    /**
     * @return string[]
     */
    private function rules()
    {
        return [
            'user_id' => 'integer',
            'referral_link' => 'required|string|max:35',
            'code' => 'required|string|max:8|min:8',
            'is_default' => 'integer|max:1',
            'application_id' => 'string|max:30',
        ];
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
     *         required=true,
     *         description="Delete referral code by ID",
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
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            ReferralCode::destroy($id);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Deleting success",
                'message' => 'The referral link field has been successfully deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Not found',
                'message' => "Referrals link #{$id} for deleted not found"
            ], 404);
        }
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
     *                  maximum=50,
     *                  description="Application ID",
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
        $input_data = (object)$this->validate($request, $this->rules());

        try {
            $currentUserId = Auth::user()->getAuthIdentifier();

            $referral_cnt = ReferralCode::where('user_id', $currentUserId)->count();

            if ($referral_cnt >= config('app.link_limit')) {
                return response()->jsonApi([
                    'status' => 'warning',
                    'title' => "Exceeded the limit",
                    'message' => 'You have exceeded the limit on the number of links to this service'
                ], 200);
            }

            $row = ReferralCode::sendDataToCreateReferralCode($currentUserId, $input_data->application_id);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Create was success",
                'message' => 'The creation of the referral link was successful',
                'row' => $row
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Operation not successful',
                'message' => "The operation to add a referral link was not successful."
            ], 404);
        }
    }

    /**
     * Change the default link
     *
     * @OA\Get(
     *     path="/v1/referrals/referral-codes/{id}/set",
     *     description="Set new referral code and link",
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
     *          required=true,
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
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */

    public function setDefault($id)
    {
        try {
            $code = ReferralCode::find($id);

            $list = ReferralCode::where('application_id', $code->application_id)->where('user_id', $code->user_id)->get();
            $list->each->update(['is_default' => false]);

            $code->update(['is_default' => true]);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Update was success",
                'message' => 'Changing the default а referral link was successful.'
            ], 200);
        } catch (\Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Operation not successful',
                'message' => "Changing the default a referral link was not successful."
            ], 404);
        }
    }
}
