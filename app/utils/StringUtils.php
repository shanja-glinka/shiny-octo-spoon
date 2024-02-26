<?php

namespace app\utils;


class StringUtils
{

    /**
     * @param string|array $s
     * @return string|array
     */
    public static function strTrim(&$s)
    {
        if (!is_array($s)) {
            $s = trim($s);
        } else
            foreach ($s as $i => $v) {
                StringUtils::strTrim($s[$i]);
            }
    }

    /**
     * @param string|array $s
     * @return string|array
     */
    public static function htmlChars($s)
    {

        if (!is_array($s)) {
            $s = htmlspecialchars($s);
        } else {
            foreach ($s as $i => $v) {
                $s[$i] = StringUtils::htmlChars($s[$i]);
            }
        }
        return $s;
    }
}
