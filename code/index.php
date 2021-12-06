<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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

// BEGIN INCLUDING ALL CORE FILES
foreach (glob("php/autoload/*.php") as $file) {
    require $file;
}

// SOME IMPORTANT ITEMS
program_handlers();
check_system();
fix_input_vars();

// NORMAL OPERATION
$_CONFIG = eval_attr(xml2array("files/config.xml"));
if (getDefault("ini_set")) {
    eval_iniset(getDefault("ini_set"));
}
if (getDefault("putenv")) {
    eval_putenv(getDefault("putenv"));
}

// EXECUTE SOME ITEMS
force_ssl();
cache_gc();
db_connect();
db_schema();
db_static();
if (!semaphore_acquire(__FILE__)) {
    show_php_error(array("phperror" => "Could not acquire the semaphore"));
}
sess_init();
check_remember();
check_basicauth();
pre_datauser();
sess_close();
check_security("main");
semaphore_release(__FILE__);

// GET THE LANGUAGE
$lang = get_lang();
$_LANG = eval_attr(xml2Array("xml/lang/${lang}.xml"));
$_CONFIG = eval_attr($_CONFIG);
if (getDefault("info/revision") == "SVN") {
    $_CONFIG["info"]["revision"] = svnversion();
}
if (getDefault("info/revision") == "GIT") {
    $_CONFIG["info"]["revision"] = gitversion();
}

// EXECUTE MORE ITEMS
post_datauser();
check_time();
check_postlimit();

// LOAD THE USER INTERFACE
$engine = getDefault("engine", "default");
if (!file_exists("php/${engine}.php")) {
    $engine = "default";
}
require "php/${engine}.php";
