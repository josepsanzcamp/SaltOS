<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2012 by Josep Sanz Campderrós
More information in http://www.saltos.net or info@saltos.net

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if(getParam("action")=="login") {
	$user=getParam("user");
	$pass=getParam("pass");
	$captcha=getParam("captcha");
	$remember=getParam("remember");
	$lang=getParam("lang");
	$style=getParam("style");
	$iconset=getParam("iconset");
	if($user!="" || $pass!="") {
		$query="SELECT * FROM tbl_usuarios WHERE activo='1' AND login='${user}'";
		$result=db_query($query);
		if(db_num_rows($result)==1) {
			$row=db_fetch_row($result);
			if($user==$row["login"] && check_password($pass,$row["password"])) {
				// REGENERATE HASH FOR VALID USERS
				$pass=hash_password($pass);
				$query="UPDATE tbl_usuarios SET password='${pass}' WHERE activo='1' AND login='${user}'";
				db_query($query);
			} elseif($user==$row["login"] && in_array($row["password"],array(md5($pass),sha1($pass)))) {
				// CONVERT FROM MD5/SHA1 TO CRYPT FORMAT
				$pass=hash_password($pass);
				$query="UPDATE tbl_usuarios SET password='${pass}' WHERE activo='1' AND login='${user}'";
				db_query($query);
			}
		}
		db_free($result);
		// CONTINUE WITH NORMAL AUTHENTICATION
		sess_init();
		useSession("user",$user!=""?$user:"null");
		useSession("pass",$pass!=""?$pass:"null");
		sess_close();
		pre_datauser();
		if(current_user() && !check_security() && !check_captcha($captcha)) {
			$user="";
			$pass="";
			sess_init();
			useSession("user",$user!=""?$user:"null");
			useSession("pass",$pass!=""?$pass:"null");
			sess_close();
			$_USER=array("id"=>0);
		}
		check_security($action);
		if(eval_bool(getDefault("security/allowremember"))) {
			if($remember) {
				useCookie("user",$user!=""?$user:"null");
				useCookie("pass",$pass!=""?$pass:"null");
				useCookie("remember",$remember);
			} else {
				useCookie("user","null");
				useCookie("pass","null");
				useCookie("remember","null");
			}
		}
	}
	useCookie("lang",$lang);
	useCookie("style",$style);
	useCookie("iconset",$iconset);
	$querystring=getParam("querystring");
	$querystring=html_entity_decode($querystring,ENT_COMPAT,"UTF-8");
	$querystring=$querystring?"?${querystring}":"";
	javascript_location_base($querystring);
	die();
}
?>