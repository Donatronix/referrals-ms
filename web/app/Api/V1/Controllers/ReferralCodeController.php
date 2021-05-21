<?php


namespace App\Api\V1\Controllers;

use App\Services\Firebase;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MongoDB\Driver\Session;
use Illuminate\Support\Facades\DB;

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
     *          description="List of all referral codes and links"
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
        return print_r(ReferralCode::all());
    }

    /**
     * Created code and link.
     *
     * @OA\Post (
     *     path="/v1/referrals/referral-codes",
     *     description="Store code and link",
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
     *          "auth-type": "Application & Application User",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          }
     *     },
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property (
     *                  property="application_id",
     *                  type="string",
     *                  maximum="30",
     *                  description="Application ID property",
     *                  example=""
     *              ),
     *              @OA\Property (
     *                  property="referral_link",
     *                  type="string",
     *                  maximum="35",
     *                  description="Referral link property",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  maximum="8",
     *                  description="Referral code property",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="is_default",
     *                  type="integer",
     *                  maximum="1",
     *                  description="Referral link by default. There is two variable: 1 and 0",
     *                  example="0"
     *              ),
     *              @OA\Property(
     *                  property="user_id",
     *                  type="integer",
     *                  description="user ID property",
     *                  example="2"
     *              )
     *          )
     *     ),
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validation($request);

        try
        {
            $data = new ReferralCode();

            $data->user_id = Auth::user()->getAuthIdentifier();
            $data->package_name = $request->package_name;
            $data->referral_link = Firebase::linkGenerate($data->referral_code, $request->package_name);
            $data->save();

        }
        catch (\Exception $e){
            dump($e->getMessage());
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
     *                  property="package_name",
     *                  type="string",
     *                  description="Package name of error",
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
     *                  property="application_id",
     *                  type="string",
     *                  description="Update application ID property",
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
            $data->default = $request->get('default',false);
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


    private function validation($request)
    {
        return $this->validate($request, [
            'user_id' => 'integer',
            'package_name' => 'string|max:30',
            'referral_link' => 'required|string|max:35'
        ]);
    }
}
