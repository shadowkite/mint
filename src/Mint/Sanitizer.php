<?php

namespace Mint;

/**
 * Class Sanitizer
 * @package Mint
 */
class Sanitizer {

    /**
     * @param $input
     * @return mixed
     */
    public static function address($input) {
        return $input;
        // @TODO Fix & test
        // return filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * @param $input
     * @return mixed
     */
    public static function tokenName($input) {
        return $input;
        // @TODO Fix & test
        // return filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * @param $input
     * @return mixed
     */
    public static function url($input) {
        return $input;
        // @TODO Fix & test
        //return filter_var($input, FILTER_SANITIZE_URL);
    }

    /**
     * @param $input
     * @return string|null
     */
    public static function hex($input) {
        return preg_replace("/[^a-fA-F0-9]/", "", $input);
    }

    /**
     * @param $input
     * @return string
     */
    public static function network($input) {
        switch($input) {
            case Slp::NETWORK_MAIN:
            case Slp::NETWORK_TEST:
                return $input;
            default:
                return Slp::NETWORK_TEST;
        }
    }

    /**
     * @param $input
     * @return string|null
     */
    public static function seed($input) {
        return preg_replace("/[^a-z\ ]/", "", $input);
    }

    /**
     * @param $input
     * @return string|null
     */
    public static function derivationPath($input) {
        return preg_replace("/[^m0-9\/\']/", "", $input);
    }
}