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

function check_password($pass, $hash)
{
    require_once "lib/phpass/PasswordHash.php";
    $t_hasher = new PasswordHash(8, true);
    $result = $t_hasher->CheckPassword($pass, $hash);
    unset($t_hasher);
    return $result;
}

function hash_password($pass)
{
    require_once "lib/phpass/PasswordHash.php";
    $t_hasher = new PasswordHash(8, true);
    // TO PREVENT /DEV/URANDOM RESTRICTION ACCESS ERRORS
    capture_next_error();
    $result = $t_hasher->HashPassword($pass);
    // TO PREVENT /DEV/URANDOM RESTRICTION ACCESS ERRORS
    get_clear_error();
    unset($t_hasher);
    return $result;
}

function password_strength($pass)
{
    require_once "lib/wolfsoftware/password_strength.class.php";
    $ps = new Password_Strength();
    $ps->set_password($pass);
    $ps->calculate();
    $score = round($ps->get_score(), 0);
    unset($ps);
    return $score;
}
