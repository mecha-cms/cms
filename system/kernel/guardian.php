<?php

class Guardian {

    public static $token = 'mecha_token';
    public static $login = 'mecha_login';
    public static $cache = 'mecha_form';
    public static $math = 'mecha_math';
    public static $captcha = 'mecha_captcha';

    protected function __construct() {}
    protected function __clone() {}

    /**
     * ============================================================
     *  GET USER DETAILS
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::get('name');
     *
     *    var_dump(Guardian::get());
     *
     * ------------------------------------------------------------
     *
     */

    public static function get($key = null, $fallback = "") {
        $log = Session::get(self::$login);
        if( ! is_null($key)) {
            return isset($log[$key]) ? $log[$key] : $fallback;
        }
        return $log;
    }

    /**
     * ============================================================
     *  GENERATE A UNIQUE TOKEN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::makeToken();
     *
     * ------------------------------------------------------------
     *
     */

    public static function makeToken() {
        $file = SYSTEM . DS . 'log' . DS . Text::parse(self::get('username'))->to_slug_moderate . '.token.txt';
        $token = File::exist($file) ? File::open($file)->read() : sha1(uniqid(mt_rand(), true));
        Session::set(self::$token, $token);
        return $token;
    }

    /**
     * ============================================================
     *  CHECKS FOR INVALID SECURITY TOKEN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if($req = Request::post()) {
     *        Guardian::checkToken($req['token']);
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkToken($token, $redirect = null) {
        if(Session::get(self::$token) === "" || Session::get(self::$token) !== $token) {
            Notify::error(Config::speak('notify_invalid_token'));
            self::reject()->kick(is_null($redirect) ? Config::get('manager')->slug . '/login' : trim($redirect, '/'));
        }
    }

    /**
     * ============================================================
     *  CHECKS FOR INVALID MATH ANSWER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::checkMath('your answer goes here')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkMath($answer = "") {
        return is_numeric($answer) && Guardian::check((int) $answer, Session::get(self::$math))->this_is_correct;
    }

    /**
     * ============================================================
     *  CHECKS FOR INVALID CAPTCHA ANSWER
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::checkCaptcha('your answer goes here')) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function checkCaptcha($answer = "", $case_sensitive = true) {
        $answer = (string) $answer;
        $answer_key = (string) Session::get(self::$captcha);
        if( ! $case_sensitive) {
            return Guardian::check(strtolower($answer), strtolower($answer_key))->this_is_correct;
        }
        return Guardian::check($answer, $answer_key)->this_is_correct;
    }

    /**
     * ============================================================
     *  DELETE SECURITY TOKEN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::deleteToken();
     *
     * ------------------------------------------------------------
     *
     */

    public static function deleteToken() {
        File::open(SYSTEM . DS . 'log' . DS . Text::parse(self::get('username'))->to_slug_moderate . '.token.txt')->delete();
        Session::kill(self::$token);
    }

    /**
     * ============================================================
     *  INPUT VALIDATION CHECKS
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::check('email@domain.com')->this_is_email) {
     *        echo 'OK.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function check($input, $compare = "") {
        return (object) array(
            'this_is_IP' => filter_var($input, FILTER_VALIDATE_IP),
            'this_is_URL' => filter_var($input, FILTER_VALIDATE_URL),
            'this_is_email' => filter_var($input, FILTER_VALIDATE_EMAIL),
            'this_is_boolean' => filter_var($input, FILTER_VALIDATE_BOOLEAN),
            'this_is_correct' => $input === $compare ? true : false,
            'this_is_regex' => @preg_match($input, null) !== false
        );
    }

    /**
     * ============================================================
     *  URL REDIRECTION
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::kick('manager/login');
     *
     * ------------------------------------------------------------
     *
     */

    public static function kick($path = "") {
        if(strpos($path, '://') === false) {
            $path = Config::get('url') . '/' . trim($path, '/');
        }
        $info = array(
            'url' => $path,
            'execution_time' => time(),
            'error' => Notify::errors()
        );
        Weapon::fire('before_kick', array($info));
        header('Location: ' . $path);
        exit;
    }

    /**
     * ============================================================
     *  STORE THE POSTED DATA INTO SESSION
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Request::post()) {
     *        Guardian::memorize();
     *        // do another stuff ...
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function memorize($memo = "") {
        if(empty($memo)) {
            $memo = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST : "";
        }
        if(is_object($memo)) {
            $memo = Mecha::A($memo);
        }
        Session::set(self::$cache, $memo);
    }

    /**
     * ============================================================
     *  DELETE THE STORED POST DATA
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::forget();
     *
     * ------------------------------------------------------------
     *
     */

    public static function forget() {
        Session::kill(self::$cache);
    }

    /**
     * ============================================================
     *  SPELL THE STORED DATA
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::wayback('name');
     *
     * ------------------------------------------------------------
     *
     */

    public static function wayback($name = null, $fallback = "") {
        $cache = Session::get(self::$cache);
        if(is_null($name)) return $cache;
        $value = isset($cache[$name]) ? $cache[$name] : $fallback;
        Session::set(self::$cache . '.' . $name, ""); // :)
        return $value;
    }

    /**
     * ============================================================
     *  LOGGING IN ...
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Request::post()) {
     *        Guardian::authorize()->kick('manager/article');
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function authorize() {
        $config = Config::get();
        $speak = Config::speak();
        $users = Text::toArray(File::open(SYSTEM . DS . 'log' . DS . 'users.txt')->read());
        $authors = array();
        foreach($users as $user => $detail) {
            preg_match('#(.*?) +\((.*?)\:(pilot|[a-z0-9]+)\)#', $detail, $matches);
            $authors[$user] = array(
                'password' => trim($matches[1]),
                'name' => trim($matches[2]),
                'status' => trim($matches[3])
            );
        }
        self::checkToken($_POST['token']);
        if(isset($_POST['username']) && isset($_POST['password']) && ! empty($_POST['username']) && ! empty($_POST['password'])) {
            if(isset($authors[$_POST['username']]) && $_POST['password'] === $authors[$_POST['username']]['password']) {
                $token = self::makeToken();
                Session::set(self::$login, array(
                    'token' => $token,
                    'username' => $_POST['username'],
                    // 'password' => $authors[$_POST['username']]['password'],
                    'author' => $authors[$_POST['username']]['name'],
                    'status' => $authors[$_POST['username']]['status']
                ));
                File::write($token)->saveTo(SYSTEM . DS . 'log' . DS . Text::parse($_POST['username'])->to_slug_moderate . '.token.txt', 0600);
                File::open(SYSTEM . DS . 'log' . DS . 'users.txt')->setPermission(0600);
            } else {
                Notify::error($speak->notify_error_username_or_password);
                self::kick($config->manager->slug . '/login');
            }
        } else {
            Notify::error($speak->notify_error_empty_fields);
            self::kick($config->manager->slug . '/login');
        }
        return new static;
    }

    /**
     * ============================================================
     *  LOGGING OUT ...
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if($user_is_invalid) {
     *        Guardian::reject()->kick('manager/login');
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function reject() {
        self::deleteToken();
        Session::kill(self::$login);
        return new static;
    }

    /**
     * ============================================================
     *  LOGGED IN
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    if(Guardian::happy()) {
     *        echo 'You are logged in.';
     *    }
     *
     * ------------------------------------------------------------
     *
     */

    public static function happy() {
        $file = SYSTEM . DS . 'log' . DS . Text::parse(self::get('username'))->to_slug_moderate . '.token.txt';
        $auth = Session::get(self::$login);
        return isset($auth['token']) && File::exist($file) && $auth['token'] === File::open($file)->read() ? true : false;
    }

    /**
     * ============================================================
     *  SOMETHING GOES WRONG
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::abort('Configuration file not found.');
     *
     * ------------------------------------------------------------
     *
     */

    public static function abort($reasons = "") {
        echo '<div style="font:normal normal 18px/1.4 Helmet,FreeSans,Sans-Serif;background-color:#333;color:#FFA;padding:1em 1.2em">' . $reasons . '</div>';
        exit;
    }

    /**
     * ============================================================
     *  MATH CHALLENGE
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::math();
     *
     * ------------------------------------------------------------
     *
     */

    public static function math($min = 1, $max = 10, $extra = "") {
        $x = mt_rand($min, $max);
        $y = mt_rand($min, $max);
        if($x - $y > 0) {
            $question = $x . ' - ' . $y;
            Session::set(self::$math, $x - $y);
        } else {
            $question = $x . ' + ' . $y;
            Session::set(self::$math, $x + $y);
        }
        return $question . $extra;
    }

    /**
     * ============================================================
     *  CAPTCHA IMAGE
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::captcha();
     *
     * ------------------------------------------------------------
     *
     */

    public static function captcha($bg = '333333', $color = 'FFFFFF', $width = 100, $height = 30, $padding = 7, $size = 16, $length = 7, $font = 'special-elite-regular.ttf') {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        Session::set(self::$captcha, substr(str_shuffle($str), 0, $length));
        $params = array();
        if($bg != '333333') $params[] = $bg === false ? 'bg=false' : 'bg=' . (string) $bg;
        if($color != 'FFFFFF') $params[] = 'color=' . (string) $color;
        if($width !== 100) $params[] = 'width=' . (string) $width;
        if($height !== 30) $params[] = 'height=' . (string) $height;
        if($padding !== 7) $params[] = 'padding=' . (string) $padding;
        if($size !== 16) $params[] = 'size=' . (string) $size;
        if($length !== 7) $params[] = 'length=' . (string) $length;
        if($font != 'special-elite-regular.ttf') $params[] = 'font=' . (string) $font;
        return '<img class="captcha" width="' . $width . '" height="' . $height . '" src="' . Config::get('url') . '/captcha.png' . ( ! empty($params) ? '?' . implode('&amp;', $params) : "") . '" alt="captcha"' . ES;
    }

    /**
     * ============================================================
     *  SET HTTP RESPONSE STATUS
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    Guardian::setResponseStatus(404); // 404 Not Found
     *
     * ------------------------------------------------------------
     *
     */

    public static function setResponseStatus($status = 200) {
        $messages = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // RFC2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', // RFC4918
            208 => 'Already Reported', // RFC5842
            226 => 'IM Used', // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC2324
            422 => 'Unprocessable Entity', // RFC4918
            423 => 'Locked', // RFC4918
            424 => 'Failed Dependency', // RFC4918
            425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
            426 => 'Upgrade Required', // RFC2817
            428 => 'Precondition Required', // RFC6585
            429 => 'Too Many Requests', // RFC6585
            431 => 'Request Header Fields Too Large', // RFC6585
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)', // RFC2295
            507 => 'Insufficient Storage', // RFC4918
            508 => 'Loop Detected', // RFC5842
            510 => 'Not Extended', // RFC2774
            511 => 'Network Authentication Required', // RFC6585
        );
        if(isset($messages[$status])) header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status . ' ' . $messages[$status]);
    }

    /**
     * ============================================================
     *  GET CLIENT IP ADDRESS
     * ============================================================
     *
     * -- CODE: ---------------------------------------------------
     *
     *    echo Guardian::IP();
     *
     * ------------------------------------------------------------
     *
     */

    public static function IP() {
        $ip = 'N/A';
        if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
                $addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($addresses[0]);
            } else {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return self::check($ip)->this_is_IP ? $ip : 'N/A';
    }

}