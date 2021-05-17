<?php


namespace App\Api\V1\Controllers;

use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Models\ReferralCode;
use Illuminate\Support\Facades\Redirect;
use MongoDB\Driver\Session;

class ReferralCodeController extends Controller
{
    /**
     *  Get referral code and link
     *
     * @OA\Get (
     *     path="/referral-codes",
     *     description="Get all user's referral codes and link",
     *     tags={"Referral-code"},
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
     *          "throttling-tier": "Unlimited",
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
        return 123456;
    }

    /**
     * Created code and link.
     *
     * @OA\Post (
     *     path="/referral-codes",
     *     description="Store code and link",
     *     tags={"Referral-code"},
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
     *          "throttling-tier": "Unlimited",
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
     *                  property="package_name",
     *                  type="string",
     *                  maximum="30",
     *                  description="Package name property",
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
     *                  description="Package name of error"
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
            ReferralCode::create([
                'user_id' => $request->get('user_id'),
                'package_name' => $request->get('package_name'),
                'referral_link' => $request->get('referral_link')
            ]);
        }
        catch (\Exception $e){
            dump($e->getMessage());
        }
    }

    /**
     * Show one code and link
     *
     * @OA\Get(
     *     path="/referral-codes/{id:[\d+]}",
     *     description="Show referral code and lonk",
     *     tags={"Referral-code"},
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
        $refcode = ReferralCode::find($id);
        return dump($refcode);
    }

    /**
     * Update referral link and code.
     *
     * @OA\Put(
     *     path="/referral-codes/{id:[\d+]}",
     *     description="Update referral code and link",
     *     tags={"Referral-code"},
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
     *          description="Note ID",
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
            ReferralCode::where('user_id', $request->user_id)->update('id', $id)->firstOrFail();
        }
        catch (\Exception $e){
            return  response()->jsonApi([
                'type' => 'error',
                'title' => 'Referrals link not found',
                'message' => "Referrals link #{$id} not found"
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $refcode = ReferralCode::find($id);
        $refcode->delete();

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
