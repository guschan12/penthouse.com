<?php

class System
{

    //Возвращает FALSE всегда, если не проходи проверку
    static function post($param, $type = FALSE, $def = false)
    {
        $data = isset($_POST[$param]) ? $_POST[$param] : FALSE;
        $res = self::check($data, $type);
        return $res ? $res : $def;
    }

    static function get($param, $type = FALSE, $def = false)
    {
        $data = isset($_GET[$param]) ? $_GET[$param] : FALSE;
        $res = self::check($data, $type);
        return $res ? $res : $def;
    }

    static function check($data, $type)
    {
        switch ($type) {
            case 'i': // integer
                if (preg_match('/[\d]/', $data))
                    return (int)$data * 1;
                break;
            case 'f': // float
                if (preg_match('/^[0-9.]+$/', $data))
                    return (float).0 + $data;
                break;
            case 's': // string
                return (string)self::strsafe(trim($data));
            case 'h': // html text
                return (string)self::strsafe2(trim($data));
            case 'a': // array
                return $data;
            case 'e': // mail
                if (filter_var($data, FILTER_VALIDATE_EMAIL))
                    return (string)strtolower(trim($data));
                break;
            default :
                return $data;
        }
        return FALSE;
    }

    static public function strsafe($str)
    {
        $pat_p = array(
            '#<script(.*?)>(.*?)</script>#is',
            '@<iframe[^>]*?>.*?</iframe>@siu',
            '@<![\s\S]*?--[ \t\n\r]*>@'
        );
        $rep_p = array('');
        $str = trim(stripslashes($str));
        $str = preg_replace($pat_p, $rep_p, $str);

        return $str;
    }

    static private function strsafe2($str)
    {
        $pat_s = array('€', '  ', '&nbsp; ', '&nbsp;&nbsp;', ' &nbsp;', '<p>&nbsp;</p>', '<p> </p>', '<p></p>', ' - ', '&nbsp;- ', ' &ndash; ', ' .', ' ,', ' :', ' )', '( ', ' ?', '!!!', '!!', ' !', '</p> <br />', '</p><br />');
        $rep_s = array('&euro;', ' ', ' ', '&nbsp;', ' ', '', '', '', '&nbsp;&mdash; ', '&nbsp;&mdash; ', '&nbsp;&mdash; ', '.', ',', ':', ')', '(', '?', '!', '!', '!', '</p>', '</p>');

        $str = self::strsafe($str);
        $str = str_replace($pat_s, $rep_s, $str);

        return self::url_to_link($str);
    }

    static function url_to_link($str)
    {
        $pat_p = array(
            '!(^|([^\'"]\s*))([hf][tps]{2,4}:\/\/[^\s<>"\'()]{4,})!mi',
            '!<a href="([^"]+)[\.:,\]]">!',
            '!([\.:,\]])</a>!'
        );
        $rep_p = array(
            '$2<a href="$3" target="_blank">$3</a>',
            '<a href="$1" target="_blank">',
            '</a>$1'
        );
        $res = preg_replace($pat_p, $rep_p, $str);
        return $res;
    }

    static function formatMoney($babki, $decimals = 2)
    {
        return number_format($babki, $decimals, ".", "'");
    }

    static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        if ($bytes < (1024 * 1024) OR $bytes > (1024 * 1024 * 10))
            $precision = 0;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    static function isAJAX()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return TRUE;
        }
        return FALSE;
    }

    public static function isPost()
    {
        $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false;
    }

    public static function isAdmin()
    {
        if(isset($_SESSION['type']) && $_SESSION['type'] == 99)
        {
            return true;
        }
        else if (isset($_COOKIE['remixid']))
        {
            $admin = new Admin();
            return $admin->do_login($_COOKIE['remixeml'],$_COOKIE['remixps'],true);

        }
        else
        {
            return false;
        }
    }


    static function password($param) {
        return hash('sha256', 'et34gWTw5wert' . $param);
    }

    static function generate($length = 8, $chars = true) {
        $pass = '';
        $str = array(
            '123456789',
            'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'
        );
        $size = strlen($str[$chars]) - 1;

        while ($length--)
            $pass .= $str[$chars][rand(0, $size)];

        return $pass;
    }

    static function getIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    static function captcha() {
        $config = parse_ini_file(CONFIG, true);
        $myCurl = curl_init();
        curl_setopt_array($myCurl, array(
            CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'secret' => $config['SETTINGS']['recaptcha'],
                'response' => self::post('g-recaptcha-response', 's'),
                'remoteip' => self::getIp()
            ))
        ));
        $response = curl_exec($myCurl);
        curl_close($myCurl);
        $res = json_decode($response);
        return !!$res->success;
    }

    static function cookie($name, $value = false) {
        if (!$value)
            setcookie($name, '', time() - 3600, "/", ".franchman.com");
        else
            setcookie($name, '' . $value . '', time() + (60 * 60 * 24 * 90), "/", ".franchman.com");
    }

    static function error($err, $caller) {
        $error = "{$caller}: {$err}. <br>Error initiated in " . System::caller($caller) . ", thrown";
        trigger_error($error, E_USER_ERROR);
    }

    static function caller($class) {
        $trace = debug_backtrace();
        $caller = '';
        foreach ($trace as $t) {
            if (isset($t['class']) && $t['class'] == $class) {
                $caller .= str_replace(APP, '/', $t['file']) . " on line " . $t['line'] . ' ';
            }
        }
        return $caller;
    }

    static function isAuth($admin = FALSE) {
        if ($admin && isset($_SESSION['user_id']) && isset($_SESSION['user_admin']) && $_SESSION['user_admin'])
            return TRUE;
        if (!$admin && isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0)
            return true;
        return false;
    }

    /**
     * @param type $num
     * @param array array('франшиза', 'франшизы', 'франшиз')
     * @param bool $hide - скрывать число из результата
     */
    static function units($num, $cases, $hide = false) {
        $num = abs($num);
        $word = '';
        if (strpos($num, '.') !== false) {
            $word = $cases[1];
        } else {
            $word = ($num % 10 == 1 && $num % 100 != 11 ? $cases[0] : ($num % 10 >= 2 && $num % 10 <= 4 && ($num % 100 < 10 || $num % 100 >= 20) ? $cases[1] : $cases[2]));
        }

        return (!$hide ? $num . ' ' : '') . $word;
    }

    static function arrayToStr($array) {
        $msg = '<ol>';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value))
                    $value = self::arrayToStr($value);
                $msg .= '<li><b>' . $key . '</b>: ' . $value . "</li>";
            }
        } else
            $msg .= $array;
        return $msg . '</ol>';
    }

    static function write_ini_file($assoc_arr, $path, $has_sections = FALSE) {
        $content = "";
        if ($has_sections) {
            foreach ($assoc_arr as $key => $elem) {
                $content .= "[" . $key . "]\n";
                foreach ($elem as $key2 => $elem2) {
                    if (is_array($elem2)) {
                        for ($i = 0; $i < count($elem2); $i++) {
                            $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                        }
                    } else if ($elem2 == "")
                        $content .= $key2 . " = \n";
                    else
                        $content .= $key2 . " = \"" . $elem2 . "\"\n";
                }
            }
        }
        else {
            foreach ($assoc_arr as $key => $elem) {
                if (is_array($elem)) {
                    for ($i = 0; $i < count($elem); $i++) {
                        $content .= $key . "[] = \"" . $elem[$i] . "\"\n";
                    }
                } else if ($elem == "")
                    $content .= $key . " = \n";
                else
                    $content .= $key . " = \"" . $elem . "\"\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return false;
        }

        $success = fwrite($handle, $content);
        fclose($handle);

        return $success;
    }

    static function trim_($string, $length = 200) {
        $string = strip_tags($string);
        $string = substr($string, 0, $length);
        $string = rtrim($string, "!,.-");
        $string = substr($string, 0, strrpos($string, ' '));
        return $string . "… ";
    }

    static function isMobile() {
        include_once 'Mobile_Detect.php';
        $detect = new Mobile_Detect;
        return $detect->isMobile();
    }

}
