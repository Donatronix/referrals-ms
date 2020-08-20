<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait AdminUserCheckTrait
{
    private function adminUserCheck(Request $request){
        $userId = $request->header('user-id');

        if ($userId === null) {
            return response()->jsonApi('Unauthorized', 401);
        }

        $adminUsers = explode(',', env('SUMRA_ADMIN_USERS', ''));
        if(empty($adminUsers) || !in_array($userId, $adminUsers)){
            return response()->jsonApi('You have\'nt permissions to access', 200);
        }

        return true;
    }
}
