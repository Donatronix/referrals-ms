<?php

namespace App\Services;

use App\Models\ApplicationKey;
use Illuminate\Support\Facades\Log;
use \Exception;

/**
 * Class Crypt
 *
 * @package App\Services
 */
class Crypt
{
    /**
     * @param $value
     *
     * @return string
     */
    public static function encrypt($value)
    {
        $keyData = self::getKey(config('app.application_version_key'));

        if($keyData === null){
            return response()->jsonApi('Cipher Key not found', 404);
        }

        return base64_encode(openssl_encrypt($value, $keyData->cipher, $keyData->cipher_key, OPENSSL_RAW_DATA));
    }

    /**
     * @param $input
     * @param $versionKey
     *
     * @return false|string
     */
    public static function decrypt($input, $versionKey = null)
    {
        if(is_null($versionKey) || $versionKey === ''){
            $versionKey = config('app.application_version_key');
        }

        $keyData = self::getKey($versionKey);
        if($keyData === null){
            return response()->jsonApi('Cipher Key not found', 404);
        }

        $data = openssl_decrypt(base64_decode($input), $keyData->cipher, $keyData->cipher_key, OPENSSL_RAW_DATA);

        if(!$data){
            Log::info('Decoded data is wrong');

            return response()->jsonApi('Decoded data is wrong', 404);
        }

        return $data;
    }

    /**
     * @param $versionKey
     *
     * @return mixed
     */
    private static function getKey($versionKey){
        $key = ApplicationKey::where('version_key', $versionKey)->first();

        if(!$key || is_null($key)){
            Log::info("Cipher Error. Version Key {$versionKey} not found");
        }

        return $key;
    }
}
