<?php


namespace App\Services;


class TestService
{
    public static function showDataFromRemoteMembership ()
    {
        return [
              "id" => "2561dbee-2207-30ff-9241-b1b5ee79a03d",
              "program_type_id" => 1,
              "enabled" => 0,
              "level_id" => "a39b4c05-ed3f-39e5-91da-53cdbcb98a75",
              "created_at" => "2021-09-02T10:02:35.000000Z",
              "updated_at" => "2021-09-02T10:02:35.000000Z",
              "program_type" => [
                    "id" => 1,
                "name" => "pioneer",
                "key" => "pioneer",
                "created_at" => "2021-09-02T10:02:35.000000Z",
                "updated_at" => "2021-09-02T10:02:35.000000Z",
              ],
              "level" => [
                    "id" => "a39b4c05-ed3f-39e5-91da-53cdbcb98a75",
                "name" => "bronze",
                "price" => 99.0,
                "currency" => "BDT",
                "period" => "month",
                "program_type_id" => 1,
              ],
              "key" => "pioneer.get_give",
              "title" => "For each Referral you get $8. Your referred contacts give $5. Earn Unlimited",
              "value" => [
                    0 => 8,
                1 => 5,
              ],
              "format" => "$",
        ];
    }
}
