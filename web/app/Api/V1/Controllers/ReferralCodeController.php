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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
