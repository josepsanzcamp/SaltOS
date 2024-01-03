<?php

/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2024 by Josep Sanz Campderrós
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

$user = getParam("user");
$pass = getParam("pass");
$captcha = getParam("captcha");
$remember = getParam("remember");
$lang = getParam("lang");
$style = getParam("style");
if ($user != "" || $pass != "") {
    $check1 = check_security("retries");
    $check2 = check_captcha($captcha);
    if (!$check1 && !$check2) {
        setParam("action", "logout");
        require "php/action/logout.php";
    }
    $pass = password_remake($user, $pass);
    sess_init();
    setSession("user", $user);
    setSession("pass", $pass);
    sess_close();
    pre_datauser();
    check_security("login");
    if (eval_bool(getDefault("security/allowremember"))) {
        if ($remember) {
            setCookie2("user", $user);
            setCookie2("pass", $pass);
            setCookie2("remember", $remember);
        } else {
            setCookie2("user", "");
            setCookie2("pass", "");
            setCookie2("remember", "");
        }
    }
    if (!$check1 && $check2) {
        check_security("captcha");
    }
}
setCookie2("lang", $lang);
setCookie2("style", $style);
require "php/action/_islogin.php";
die();
