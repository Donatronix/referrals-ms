<?php

namespace App\Services;

use App\Models\CryptoKey;
use Illuminate\Http\Request;

class Crypt
{
    public static function encrypt($value, Request $request)
    {
        $keyData = self::getKey($request);

        return base64_encode(openssl_encrypt($value, $keyData->cipher, $keyData->cipher_key, OPENSSL_RAW_DATA));
    }

    public static function decrypt($input, Request $request)
    {
        $keyData = self::getKey($request);

        $data = openssl_decrypt(base64_decode($input), $keyData->cipher, $keyData->cipher_key, OPENSSL_RAW_DATA);

        if(!$data){
            abort(400, 'Decoded data is wrong');
        }

        return $data;
    }

    private static function getKey(Request $request){
        $key = CryptoKey::where('version_key', $request->get('version_key', null))->first();

        if(!$key){
            abort(400, 'Cipher Key not found');
        }

        return $key;
    }
}
