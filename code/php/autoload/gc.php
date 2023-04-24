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

function cache_gc()
{
    if (!eval_bool(getDefault("cache/cachegcenabled"))) {
        return;
    }
    init_random();
    if (rand(0, intval(getDefault("cache/cachegcdivisor"))) > intval(getDefault("cache/cachegcprobability"))) {
        return;
    }
    if (!semaphore_acquire(__FUNCTION__, getDefault("semaphoretimeout", 100000))) {
        return;
    }
    $cachedir = get_directory("dirs/cachedir");
    $files1 = glob_protected($cachedir . "*"); // FICHEROS VISIBLES
    $files2 = glob_protected($cachedir . ".*"); // FICHEROS OCULTOS
    $files2 = array_diff(
        $files2,
        array($cachedir . ".",$cachedir . "..",$cachedir . ".htaccess")
    ); // QUITAR EXCEPCIONES
    $files = array_merge($files1, $files2);
    $delta = time() - intval(getDefault("cache/cachegctimeout"));
    foreach ($files as $file) {
        if (file_exists($file) && is_file($file) && filemtime($file) < $delta) {
            unlink($file);
        }
    }
    semaphore_release(__FUNCTION__);
}
