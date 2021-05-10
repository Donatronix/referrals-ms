<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    public const TIERS = ['basic', 'bronze', 'silver', 'gold'];

    public const BUSTED_TIME = ['basic'=>1, 'bronze'=>1.2, 'silver'=>1.4, 'gold'=>2];
    public const BUSTED_MONEY = ['basic'=>1, 'bronze'=>1.2, 'silver'=>1.4, 'gold'=>2];

    public const CASHOUT_MIN = ['basic'=>50000, 'bronze'=>35000, 'silver'=>25000, 'gold'=>10000];

    public const FEE = ['basic'=>25, 'bronze'=>0, 'silver'=>0, 'gold'=>0];

    public const REFERRAL_PAYMENT_GET = ['basic'=>8, 'bronze'=>8, 'silver'=>9, 'gold'=>10];
    public const REFERRAL_PAYMENT_GIVE = ['basic'=>5, 'bronze'=>5, 'silver'=>6, 'gold'=>7];

    public const BURSTED_MIN_HOURS = 1000;
    public const BURSTED_MIN_MONEY = 1000000;  // $10,000

    static function getBustedTime($tier) {
        return self::BUSTED_TIME[$tier];
    }

    static function getBustedMoney($tier) {
        return self::BUSTED_MONEY[$tier];
    }

    static function getCacheoutMin($tier) {
        return self::CASHOUT_MIN[$tier];
    }

    static function getFee($tier) {
        return self::FEE[$tier];
    }

    static function getReferralPaymentGet($tier) {
        return self::REFERRAL_PAYMENT_GET[$tier];
    }

    static function getReferralPaymentGive($tier) {
        return self::REFERRAL_PAYMENT_GIVE[$tier];
    }

    static function getBurstedMinHours() {
        return self::BURSTED_MIN_HOURS;
    }

    static function getBurstedMinMoney() {
        return self::BURSTED_MIN_MONEY;
    }

}
