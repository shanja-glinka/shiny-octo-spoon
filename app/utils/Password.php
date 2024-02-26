<?php

namespace app\utils;

class Password
{
    public static function hash($password)
    {
        return sha1($password . '(#%Qw)@89q30)');
    }
}
