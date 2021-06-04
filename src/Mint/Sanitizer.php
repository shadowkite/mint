<?php

namespace Mint;

class Sanitizer {
    public static function address($input) {
        return $input;
        // @TODO Fix & test
        // return filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public static function tokenName($input) {
        return $input;
        // @TODO Fix & test
        // return filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    public static function url($input) {
        return $input;
        // @TODO Fix & test
        //return filter_var($input, FILTER_SANITIZE_URL);
    }

    public static function hex($input) {
        return preg_replace("/[^a-fA-F0-9]/", "", $input);
    }

    public static function network($input) {
        switch($input) {
            case 'mainnet':
            case 'testnet':
                return $input;
            default:
                return 'testnet';
        }
    }

    public static function seed($input) {
        return preg_replace("/[^a-z\ ]/", "", $input);
    }

    public static function derivationPath($input) {
        return preg_replace("/[^m0-9\/\']/", "", $input);
    }
}