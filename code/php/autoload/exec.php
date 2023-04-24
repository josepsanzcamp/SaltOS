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

define("__WHICH__", "which __INPUT__");

function ob_passthru($cmd, $expires = 0)
{
    if ($expires) {
        $cache = get_cache_file($cmd, ".out");
        if (file_exists($cache) && is_file($cache)) {
            $mtime = filemtime($cache);
            if (time() - $expires < $mtime) {
                return file_get_contents($cache);
            }
        }
    }
    if (!is_disabled_function("passthru")) {
        ob_start();
        passthru($cmd);
        $buffer = ob_get_clean();
    } elseif (!is_disabled_function("system")) {
        ob_start();
        system($cmd);
        $buffer = ob_get_clean();
    } elseif (!is_disabled_function("exec")) {
        $buffer = array();
        exec($cmd, $buffer);
        $buffer = implode("\n", $buffer);
    } elseif (!is_disabled_function("shell_exec")) {
        ob_start();
        $buffer = shell_exec($cmd);
        ob_get_clean();
    } else {
        $buffer = "";
    }
    if ($expires) {
        file_put_contents($cache, $buffer);
        chmod($cache, 0666);
    }
    return $buffer;
}

function check_commands($commands, $expires = 0)
{
    if (!is_array($commands)) {
        $commands = explode(",", $commands);
    }
    $result = 1;
    foreach ($commands as $command) {
        $result &= ob_passthru(str_replace(
            array("__INPUT__"),
            array($command),
            getDefault("commands/__which__", __WHICH__)
        ), $expires) ? 1 : 0;
    }
    return $result;
}

function is_disabled_function($fn = "")
{
    static $disableds_string = null;
    static $disableds_array = array();
    if ($disableds_string === null) {
        $disableds_string = ini_get("disable_functions") . "," . ini_get("suhosin.executor.func.blacklist");
        $disableds_array = $disableds_string ? explode(",", $disableds_string) : array();
        foreach ($disableds_array as $key => $val) {
            $val = strtolower(trim($val));
            if ($val == "") {
                unset($disableds_array[$key]);
            }
            if ($val != "") {
                $disableds_array[$key] = $val;
            }
        }
    }
    return in_array($fn, $disableds_array);
}

function __exec_timeout($cmd)
{
    if (check_commands(getDefault("commands/timeout"), 60)) {
        $cmd = str_replace(
            array("__TIMEOUT__","__COMMAND__"),
            array(getDefault("commandtimeout", 60),$cmd),
            getDefault("commands/__timeout__")
        );
    }
    return $cmd;
}
