<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

function __libsqlite_group_concat_step($context, $rows, $string, $separator = ",")
{
    if ($context != "") {
        $context .= $separator;
    }
    $context .= $string;
    return $context;
}

function __libsqlite_group_concat_finalize($context, $rows)
{
    return $context;
}

function __libsqlite_replace($subject, $search, $replace)
{
    return str_replace($search, $replace, $subject);
}

function __libsqlite_lpad($input, $length, $char)
{
    return str_pad($input, $length, $char, STR_PAD_LEFT);
}

function __libsqlite_concat()
{
    $array = func_get_args();
    return implode("", $array);
}

function __libsqlite_unix_timestamp($date)
{
    return strtotime($date);
}

function __libsqlite_from_unixtime($timestamp)
{
    return date("Y-m-d H:i:s", $timestamp);
}

function __libsqlite_year($date)
{
    return date("Y", strtotime($date));
}

function __libsqlite_month($date)
{
    return date("m", strtotime($date));
}

function __libsqlite_week($date, $mode)
{
    $mode = $mode * 86400;
    return date("W", strtotime($date) + $mode);
}

function __libsqlite_truncate($n, $d)
{
    $d = pow(10, $d);
    return intval($n * $d) / $d;
}

function __libsqlite_day($date)
{
    return intval(date("d", strtotime($date)));
}

function __libsqlite_dayofyear($date)
{
    return date("z", strtotime($date)) + 1;
}

function __libsqlite_dayofweek($date)
{
    return date("w", strtotime($date)) + 1;
}

function __libsqlite_hour($date)
{
    return intval(date("H", strtotime($date)));
}

function __libsqlite_minute($date)
{
    return intval(date("i", strtotime($date)));
}

function __libsqlite_second($date)
{
    return intval(date("s", strtotime($date)));
}

function __libsqlite_md5($temp)
{
    return md5($temp);
}

function __libsqlite_repeat($str, $count)
{
    return str_repeat($str, $count);
}

function __libsqlite_find_in_set($str, $strlist)
{
    return in_array($str, explode(",", $strlist)) ? 1 : 0;
}

function __libsqlite_if($condition, $value_if_true, $value_if_false)
{
    return $condition ? $value_if_true : $value_if_false;
}

function __libsqlite_pow($base, $exp)
{
    return pow($base, $exp);
}
