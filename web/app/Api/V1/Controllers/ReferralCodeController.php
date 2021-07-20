<?php

namespace App\Api\V1\Controllers;

use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Referral code Controller
 *
 * @package App\Api\V1\Controllers
 */
class ReferralCodeController extends Controller
{
    /**
     * Get referral codes and links
     *
     * @OA\Get(
     *     path="/v1/referrals/referral-codes",
     *     description="Get all user's referral codes and links",
     *     tags={"Referral Code"},
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
     *     @OA\Response(
     *         response="200",
     *         description="The list of referral codes has been displayed successfully"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
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
                'data' => $codes->toArray()
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
     *  Create link and code user generated
     *
     * @OA\Post(
     *     path="/v1/referrals/referral-codes",
     *     summary="Create link and code for an existing user",
     *     description="Create link and code user generated",
     *     tags={"Referral Code"},
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
     *              @OA\Property(
     *                  property="is_default",
     *                  type="string",
     *                  description="Is Defailt refferal link",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="string",
     *                  description="Note about referral code",
     *                  example=""
     *              )
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
     *     @OA\Response(
     *          response="500",
     *          description="Unknown error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="package_name",
     *                  type="string",
     *                  description="Package name of error",
     *              ),
     *              @OA\Property(
     *                  property="referral_link",
     *                  type="string",
     *                  description="Referral link of error"
     *              ),
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Code of error"
     *              )
     *          )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $input_data = (object)$this->validate($request, $this->rules());

        $referral_cnt = ReferralCode::byOwner()->get()->count();

        if ($referral_cnt >= config('app.link_limit')) {
            return response()->jsonApi([
                'status' => 'warning',
                'title' => "Exceeded the limit",
                'message' => 'You have exceeded the limit on the number of links to this service'
            ], 200);
        }

        try {
            $code = ReferralCodeService::createReferralCode([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'application_id' => $input_data->application_id,
                'is_default' => false
            ]);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Referral code generate",
                'message' => 'The creation of the referral link was successful',
                'data' => $code->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Referral code generate',
                'message' => "There was an error while creating a referral code: " . $e->getMessage()
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
            $code = ReferralCode::find($id);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Get referral code info",
                'message' => 'Get referral code info with link',
                'data' => $code,
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Get referral code info",
                'message' => "Referral code #{$id} not found"
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
     *          description="Referral code ID",
     *          example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *          @OA\Schema (
     *              type="integer"
     *          ),
     *     ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="is_default",
     *                  type="string",
     *                  description="Is Defailt refferal link",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="string",
     *                  description="Note about referral code",
     *                  example=""
     *              ),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Save successfull"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *          response="500",
     *          description="Unknown error",
     *          @OA\JsonContent(
     *                  type="object",
     *                  @OA\Property(
     *                      property="application_id",
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
            $data->is_default = $input_data->default;
            $data->note = $input_data->note;
            $data->save();

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Updating success",
                'message' => 'The referral link field update has been successfully updated'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Referrals link not found',
                'message' => $e,
                // 'message' => "Referrals link #{$id} for updated not found"
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
     *         required=true,
     *         description="Delete referral code by ID",
     *         example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *         @OA\Schema(
     *             type="string"
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
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Not found',
                'message' => "Referrals link #{$id} for deleted not found"
            ], 404);
        }
    }

    /**
     * Change the default link
     *
     * @OA\Get(
     *     path="/v1/referrals/referral-codes/{id}/default",
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
     *          example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
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
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Operation not successful',
                'message' => "Changing the default a referral link was not successful."
            ], 404);
        }
    }

    /**
     * @return string[]
     */
    private function rules(): array
    {
        return [
            'application_id' => 'required|string|max:30',
            'is_default' => 'integer|max:1',
            'note' => 'string|max:255'
        ];
    }
}
