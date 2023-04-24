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

function memory_get_free($bytes = false)
{
    $memory_limit = normalize_value(ini_get("memory_limit"));
    $memory_usage = memory_get_usage();
    $diff = $memory_limit - $memory_usage;
    if (!$bytes) {
        $diff = ($diff * 100) / $memory_limit;
    }
    return $diff;
}

function time_get_usage($secs = false)
{
    return __time_get_helper(__FUNCTION__, $secs);
}

function time_get_free($secs = false)
{
    return __time_get_helper(__FUNCTION__, $secs);
}

function __time_get_helper($fn, $secs)
{
    static $ini = null;
    if ($ini === null) {
        $ini = microtime(true);
    }
    $cur = microtime(true);
    $max = ini_get("max_execution_time");
    if (!$max) {
        $max = getDefault("ini_set/max_execution_time");
    }
    if (stripos($fn, "usage") !== false) {
        $diff = $cur - $ini;
    } elseif (stripos($fn, "free") !== false) {
        $diff = $max - ($cur - $ini);
    }
    if (!$secs) {
        $diff = ($diff * 100) / $max;
    }
    return $diff;
}

function max_memory_limit()
{
    ini_set("memory_limit", getDefault("server/maxmemorylimit"));
}

function max_execution_time()
{
    ini_set("max_execution_time", getDefault("server/maxexecutiontime"));
}
