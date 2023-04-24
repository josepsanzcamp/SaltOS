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

function semaphore_acquire($name = "", $timeout = INF)
{
    return __semaphore_helper(__FUNCTION__, $name, $timeout);
}

function semaphore_release($name = "")
{
    return __semaphore_helper(__FUNCTION__, $name, null);
}

function semaphore_shutdown()
{
    return __semaphore_helper(__FUNCTION__, null, null);
}

function semaphore_file($name = "")
{
    return __semaphore_helper(__FUNCTION__, $name, null);
}

function __semaphore_helper($fn, $name, $timeout)
{
    static $fds = array();
    if (stripos($fn, "acquire") !== false) {
        if ($name == "") {
            $name = __FUNCTION__;
        }
        $file = get_cache_file($name, ".sem");
        if (!is_writable(dirname($file))) {
            return false;
        }
        if (!isset($fds[$file])) {
            $fds[$file] = null;
        }
        if ($fds[$file]) {
            return false;
        }
        capture_next_error();
        $fds[$file] = fopen($file, "a");
        get_clear_error();
        if (!$fds[$file]) {
            return false;
        }
        chmod_protected($file, 0666);
        init_random();
        for (;;) {
            $result = flock($fds[$file], LOCK_EX | LOCK_NB);
            if ($result) {
                break;
            }
            $timeout -= __semaphore_usleep(rand(0, 1000));
            if ($timeout < 0) {
                fclose($fds[$file]);
                $fds[$file] = null;
                return false;
            }
        }
        ftruncate($fds[$file], 0);
        fwrite($fds[$file], gettrace(array(), true));
        return true;
    } elseif (stripos($fn, "release") !== false) {
        if ($name == "") {
            $name = __FUNCTION__;
        }
        $file = get_cache_file($name, ".sem");
        if (!isset($fds[$file])) {
            $fds[$file] = null;
        }
        if (!$fds[$file]) {
            return false;
        }
        flock($fds[$file], LOCK_UN);
        fclose($fds[$file]);
        $fds[$file] = null;
        return true;
    } elseif (stripos($fn, "shutdown") !== false) {
        foreach ($fds as $file => $fd) {
            if ($fds[$file]) {
                flock($fds[$file], LOCK_UN);
                fclose($fds[$file]);
                $fds[$file] = null;
            }
        }
        return true;
    } elseif (stripos($fn, "file") !== false) {
        if ($name == "") {
            $name = __FUNCTION__;
        }
        $file = get_cache_file($name, ".sem");
        return $file;
    }
    return false;
}

function __semaphore_usleep($usec)
{
    if (function_exists("socket_create")) {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $read = null;
        $write = null;
        $except = array($socket);
        $time1 = microtime(true);
        socket_select($read, $write, $except, intval($usec / 1000000), intval($usec % 1000000));
        $time2 = microtime(true);
        return ($time2 - $time1) * 1000000;
    }
    $time1 = microtime(true);
    usleep($usec);
    $time2 = microtime(true);
    return ($time2 - $time1) * 1000000;
}
