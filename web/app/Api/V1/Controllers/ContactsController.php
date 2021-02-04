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
class ContactsController extends Controller
{

    public function test_neo4j()
        {
            $client = ClientBuilder::create()
                ->addConnection('default', env('NEO_DEFAULT_URL','http://neo4j:kanku@localhost:7474')) // Example for HTTP connection configuration (port is optional)
                ->addConnection('bolt', env('NEO_BOLT_URL','bolt://neo4j:kanku@localhost:7687')) // Example for BOLT connection configuration (port is optional)
                ->build();

            $query = 'CREATE (ee:Person { name: "Emil", from: "Ukraine", klout: 55 })';

            $client->run($query);
        }


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
        return 'tt';
 }


}
