<?php

/**
 * ===========================================================
 *  PAGE REQUEST
 * ===========================================================
 *
 * -- CODE: --------------------------------------------------
 *
 *    [1]. if(Request::post()) {
 *             echo Request::post('name');
 *             echo Request::post('foo.bar');
 *             echo Request::post('foo.bar', 'Failed.');
 *         }
 *
 *    [2]. if(Request::get()) { ... }
 *
 * -----------------------------------------------------------
 *
 */

class Request {

    public static function post($param = null, $fallback = false) {
        if(is_null($param)) {
            return $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST) && ! empty($_POST) ? $_POST : $fallback;
        }
        $output = Mecha::eat($_POST)->vomit($param, $fallback);
        return ! empty($output) ? $output : $fallback;
    }

    public static function get($param = null, $fallback = false) {
        if(is_null($param)) {
            return $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET) && ! empty($_GET) ? $_GET : $fallback;
        }
        $output = Mecha::eat($_GET)->vomit($param, $fallback);
        return ! empty($output) ? $output : $fallback;
    }

}