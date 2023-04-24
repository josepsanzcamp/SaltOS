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

function current_date($offset = 0)
{
    return date("Y-m-d", time() + (int)$offset);
}

function current_time($offset = 0)
{
    return date("H:i:s", time() + (int)$offset);
}

function current_datetime($offset = 0)
{
    return current_date($offset) . " " . current_time($offset);
}

function current_decimals($offset = 0, $size = 4)
{
    $decimals = microtime(true) + (int)$offset;
    $decimals -= intval($decimals);
    $decimals = substr($decimals, 2, $size);
    $decimals = str_pad($decimals, $size, "0");
    return $decimals;
}

function current_datetime_decimals($offset = 0, $size = 4)
{
    return current_datetime($offset) . "." . current_decimals($offset, $size);
}

function dateval($value)
{
    static $expr = array("-",":",",",".","/");
    $value = str_replace($expr, " ", $value);
    $value = prepare_words($value);
    $temp = explode(" ", $value);
    foreach ($temp as $key => $val) {
        $temp[$key] = intval($val);
    }
    for ($i = 0; $i < 3; $i++) {
        if (!isset($temp[$i])) {
            $temp[$i] = 0;
        }
    }
    if ($temp[2] > 1900) {
        $temp[2] = min(9999, max(0, $temp[2]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[0] = min(__days_of_a_month($temp[2], $temp[1]), max(0, $temp[0]));
        $value = sprintf("%04d-%02d-%02d", $temp[2], $temp[1], $temp[0]);
    } else {
        $temp[0] = min(9999, max(0, $temp[0]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[2] = min(__days_of_a_month($temp[0], $temp[1]), max(0, $temp[2]));
        $value = sprintf("%04d-%02d-%02d", $temp[0], $temp[1], $temp[2]);
    }
    return $value;
}

function __days_of_a_month($year, $month)
{
    return date("t", strtotime(sprintf("%04d-%02d-%02d", $year, $month, 1)));
}

function timeval($value)
{
    static $expr = array("-",":",",",".","/");
    $value = str_replace($expr, " ", $value);
    $value = prepare_words($value);
    $temp = explode(" ", $value);
    foreach ($temp as $key => $val) {
        $temp[$key] = intval($val);
    }
    for ($i = 0; $i < 3; $i++) {
        if (!isset($temp[$i])) {
            $temp[$i] = 0;
        }
    }
    $temp[0] = min(24, max(0, $temp[0]));
    $temp[1] = min(59, max(0, $temp[1]));
    $temp[2] = min(59, max(0, $temp[2]));
    $value = sprintf("%02d:%02d:%02d", $temp[0], $temp[1], $temp[2]);
    return $value;
}

function datetimeval($value)
{
    static $expr = array("-",":",",",".","/");
    $value = str_replace($expr, " ", $value);
    $value = prepare_words($value);
    $temp = explode(" ", $value);
    foreach ($temp as $key => $val) {
        $temp[$key] = intval($val);
    }
    for ($i = 0; $i < 6; $i++) {
        if (!isset($temp[$i])) {
            $temp[$i] = 0;
        }
    }
    if ($temp[2] > 1900) {
        $temp[2] = min(9999, max(0, $temp[2]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[0] = min(__days_of_a_month($temp[2], $temp[1]), max(0, $temp[0]));
        $temp[3] = min(23, max(0, $temp[3]));
        $temp[4] = min(59, max(0, $temp[4]));
        $temp[5] = min(59, max(0, $temp[5]));
        $value = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $temp[2], $temp[1], $temp[0], $temp[3], $temp[4], $temp[5]);
    } else {
        $temp[0] = min(9999, max(0, $temp[0]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[2] = min(__days_of_a_month($temp[0], $temp[1]), max(0, $temp[2]));
        $temp[3] = min(23, max(0, $temp[3]));
        $temp[4] = min(59, max(0, $temp[4]));
        $temp[5] = min(59, max(0, $temp[5]));
        $value = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5]);
    }
    return $value;
}

function __time2secs($time)
{
    $time = explode(":", $time);
    $secs = intval($time[0]) * 3600 + intval($time[1]) * 60 + intval($time[2]);
    return $secs;
}

function __secs2time($secs)
{
    $time = sprintf("%02d:%02d:%02d", intval($secs / 3600), intval(($secs / 60) % 60), intval($secs % 60));
    return $time;
}
