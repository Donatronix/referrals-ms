<?php


namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Vcard;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;

class UserController extends Controller
{
    /**
     * User's contact list
     *
     * @OA\Get(
     *     path="/v1/referrals/contacts",
     *     summary="Load user's contact list",
     *     description="Load user's contact list",
     *     tags={"User contacts"},
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
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function contacts()
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        $user = User::find($user_id);
        try {
            $contacts = $user->contacts();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);

    }

    /**
     * User's contact list: add contacts from vCard
     *
     * @OA\Post(
     *     path="/v1/referrals/contacts/vcard",
     *     summary="Add contacts from vCard",
     *     description="Add contacts from vCard",
     *     tags={"UserContacts"},
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
     *     @OA\Parameter(
     *         name="vcards",
     *         description="vCard text",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer",
     *              default = 0
     *          )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addvcard(Request $request)
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        $vcards = new Vcard();
        $cards = $vcards->fromText($request->vcards);
        $contacts = [];
        try {
            foreach($cards as $c) {
                $contact = Contact::create([
                    'user_id' => $user_id,
                    'firstname' => $c['N'][0]['value'][1][0],
                    'lastname' => $c['N'][0]['value'][0][0],
                    'middlename' => $c['N'][0]['value'][2][0],
                    'prefix' => $c['N'][0]['value'][3][0],
                    'suffix' => $c['N'][0]['value'][4][0],
                    'nickname' => $c['NICKNAME'][0]['value'][0][0],
                    'adrextend' => $c['ADR'][0]['value'][0][0],
                    'adrstreet' => $c['ADR'][0]['value'][2][0]."\n".$c['ADR'][0]['value'][1][0],
                    'adrcity' => $c['ADR'][0]['value'][3][0],
                    'adrstate' => $c['ADR'][0]['value'][4][0],
                    'adrzip' => $c['ADR'][0]['value'][5][0],
                    'adrcountry' => $c['ADR'][0]['value'][6][0],
                    'tel1' => $c['TEL'][0]['value'][0][0],
                    'email' => $c['EMAIL'][0]['value'][0][0]
                ]);
                $contact->save();
                $contacts[] = $contact;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);

    }

    /**
     * User's contact list: add contacts from Google export
     *
     * @OA\Post(
     *     path="/v1/referrals/contacts/google",
     *     summary="Add contacts from Google export",
     *     description="Add contacts from Google export",
     *     tags={"UserContacts"},
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
     *     @OA\Parameter(
     *         name="googleexport",
     *         description="Google export text",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer",
     *              default = 0
     *          )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addgoogle(Request $request)
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        $googlecsv = $request['googleexport'];
        $googles = $this->parse_csv($googlecsv);
        $header = array_shift($googles);
        $header[] = "tmp";
        $gcontacts = [];
        foreach($googles as $s) {
            $gcontacts[] = array_combine($header, $s);
        }
        $contacts = [];
        try {
            foreach($gcontacts as $c) {
                $contact = Contact::create([
                    'user_id' => $user_id,
                    'firstname' => $c['First Name'],
                    'lastname' => $c['Last Name'],
                    'middlename' => $c['Middle Name'],
                    'prefix' => $c['Title'],
                    'suffix' => $c['Suffix'],
                    'nickname' => '',
                    'adrextend' => $c['Home Address PO Box'],
                    'adrstreet' => $c['Home Street']."\n".$c['Home Street2']."\n".$c['Home Street3'],
                    'adrcity' => $c['Home City'],
                    'adrstate' => $c['Home State'],
                    'adrzip' => $c['Home Postal Code'],
                    'adrcountry' => $c['Home Country'],
                    'tel1' => $c['Other Phone']??$c['Primary Phone']??$c['Home Phone']??$c['Home Phone 2']??$c['Mobile Phone'],
                    'email' => $c['E-mail Address']
                ]);
                $contact->save();
                $contacts[] = $contact;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);

    }

    function parse_csv($str)
    {
        $str = preg_replace_callback('/([^"]*)("((""|[^"])*)"|$)/s',
            function ($matches) {
                $str = str_replace("\r", "\rR", $matches[3]);
                $str = str_replace("\n", "\rN", $str);
                $str = str_replace('""', "\rQ", $str);
                $str = str_replace(',', "\rC", $str);
                return preg_replace('/\r\n?/', "\n", $matches[1]) . $str;
            },
            $str);
        $str = preg_replace('/\n$/', '', $str);
        return array_map(function ($line) {
            return array_map(function ($field) {
                $field = str_replace("\rC", ',', $field);
                $field = str_replace("\rQ", '"', $field);
                $field = str_replace("\rN", "\n", $field);
                $field = str_replace("\rR", "\r", $field);
                return $field;
            },
                explode(',', $line));
        },
            explode("\n", $str));
    }

}
