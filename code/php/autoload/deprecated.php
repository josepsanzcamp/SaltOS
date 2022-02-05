<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2022 by Josep Sanz Campderrós
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

function chmod_protected($file, $mode)
{
    addtrace(array(
        "phperror" => "Deprecated function " . __FUNCTION__,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
    ), getDefault("debug/deprecated", "deprecated.log"));

    capture_next_error();
    ob_start();
    chmod($file, $mode);
    $error1 = ob_get_clean();
    $error2 = get_clear_error();
    return $error1 . $error2;
}

function unlink_protected($file)
{
    addtrace(array(
        "phperror" => "Deprecated function " . __FUNCTION__,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
    ), getDefault("debug/deprecated", "deprecated.log"));

    capture_next_error();
    ob_start();
    unlink($file);
    $error1 = ob_get_clean();
    $error2 = get_clear_error();
    return $error1 . $error2;
}

function filemtime_protected($file)
{
    addtrace(array(
        "phperror" => "Deprecated function " . __FUNCTION__,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
    ), getDefault("debug/deprecated", "deprecated.log"));

    capture_next_error();
    ob_start();
    $mtime = filemtime($file);
    $error1 = ob_get_clean();
    $error2 = get_clear_error();
    return array($mtime,$error1 . $error2);
}

function mkdir_protected($dir)
{
    addtrace(array(
        "phperror" => "Deprecated function " . __FUNCTION__,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
    ), getDefault("debug/deprecated", "deprecated.log"));

    capture_next_error();
    ob_start();
    mkdir($dir);
    $error1 = ob_get_clean();
    $error2 = get_clear_error();
    return $error1 . $error2;
}

function readfile_protected($file)
{
    addtrace(array(
        "phperror" => "Deprecated function " . __FUNCTION__,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
    ), getDefault("debug/deprecated", "deprecated.log"));

    $fp = fopen($file, "rb");
    while (!feof($fp)) {
        echo fread($fp, 1048576);
    }
    fclose($fp);
}
