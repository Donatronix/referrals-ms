<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use GraphAware\Neo4j\Client\ClientBuilder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**
 * Class ContactsController
 *
 * @package App\Api\V1\Controllers
 */


/**
 * Save contact data
 *
 * @OA\Post(
 *     path="/v1/referrals/contacts",
 *     summary="Save contact data in Neo4j",
 *     description="Save contact data in Neo4j",
 *     tags={"Contacts"},
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
 *         name="userID",
 *         description="user id",
 *         required=true,
 *         in="query",
 *          @OA\Schema (
 *              type="integer"
 *          )
 *     ),
 *     @OA\Parameter(
 *         name="contacts",
 *         description="Contacts in JSON",
 *         required=true,
 *         in="query",
 *          @OA\Schema (
 *              type="string"
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
class ContactsController extends Controller
{

    public function store(Request $request)
    {

        $userID =0;
        $json = '';
        $errors = $this->validation($request,$userID,$json);

        if( count($errors) > 0 )
            return response()->json([
                'status' => 'error',
                'title' => 'Data is not valid',
                'message' => implode( ', ', $errors)
            ], 400);

        $result = $this->save($userID,$json);

        if($result == 'Ok')
            return response()->json([
                'status'    => 'success',
                'title'     => 'Contacts are saved',
                'message'   => 'Contacts are saved'
            ], 200);
        else
        {
            return response()->json([
                'status'    => 'error',
                'title'     => 'Contacts are not saved',
                'message'   => $result
            ], 400);
        }

    }

 /***********************************
  *  P R I V A T E
 ************************************/
    private  function validation(Request $request, &$userID, &$json)
    {
        $errors = [];

        if( !isset($request->userID) )
            $errors[] = 'No user ID';
        else
        {
            $userID = (int)$request->userID;
            if($userID == 0)
                $errors[] = 'Invalid user ID';
        }

        $json = '';

        if( !isset($request->contacts) )
            $errors[] = 'No contacts';
        else
        {
            $json = json_decode($request->contacts);
            $msg = json_last_error();

            if($msg !== JSON_ERROR_NONE)
            {
                $string = json_last_error_msg();
                $errors[] = 'Invalid contacts: '.$string;
            }

        }

        return $errors;
    }


private function save($userID,$json)
 {
     $client = ClientBuilder::create()
         ->addConnection('default', env('NEO_DEFAULT_URL','http://neo4j:kanku@localhost:7474')) // Example for HTTP connection configuration (port is optional)
         ->addConnection('bolt', env('NEO_BOLT_URL','bolt://neo4j:kanku@localhost:7687')) // Example for BOLT connection configuration (port is optional)
         ->build();


     //Look for a user id= $userID. If not found, create such a user.
     $query = "MERGE (person:User {  id:$userID })
RETURN person";

     $client->run($query);

     foreach($json as $one)
     {
         $arr = (array)$one;
         foreach($arr as $key=>$value)
         {
             $name = $key;
             $text = $value;

            $query = "MATCH (person:User) WHERE person.id = $userID
            CREATE (ct:Contact {name: \"$name\", text:\"".$text."\"}),
            (person)-[:LISTEN]->(ct)
            ";
             $client->run($query);
         }
     }

    return 'Ok';
 }


}
