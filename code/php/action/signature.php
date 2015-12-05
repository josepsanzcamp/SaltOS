<?php
declare(ticks=1000);
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
More information in http://www.saltos.org or info@saltos.org

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
if(!check_user()) action_denied();
if(getParam("action")=="signature") {
	require_once("php/libaction.php");
	// DETECT IF IS A REQUEST FOR REPLACE BODY, CC AND STATE_CRT
	if(getParam("old") && getParam("new")) {
		require_once("php/sendmail.php");
		$old=getParam("old");
		$new=getParam("new");
		$body=getParam("body");
		$cc=getParam("cc");
		$state_crt=intval(getParam("state_crt"));
		// REPLACE THE SIGNATURE BODY
		$file=__signature_getauto(__signature_getfile($new));
		$auto=$file?$file["auto"]:"";
		$pos1=strpos($body,"<signature>");
		if($pos1!==false) $pos1=strpos($body,">",$pos1);
		$pos2=strpos($body,"</signature>");
		if($pos1!==false && $pos2!==false) $body=substr_replace($body,$auto,$pos1+1,$pos2-$pos1-1);
		// FIND THE OLD AND NEW CC'S AND STATE_CRT'S
		$query="SELECT * FROM tbl_usuarios_c WHERE id='".$old."'";
		$result_old=execute_query($query);
		$query="SELECT * FROM tbl_usuarios_c WHERE id='".$new."'";
		$result_new=execute_query($query);
		// REPLACE THE CC
		if($result_old && $result_new) {
			$cc=explode(";",$cc);
			foreach($cc as $key=>$val) {
				$val=trim($val);
				if($val) $cc[$key]=$val; else unset($cc[$key]);
			}
			if($result_old["email_addmetocc"]) {
				foreach($cc as $key=>$val) {
					list($email_from,$email_name)=__sendmail_parser($val);
					if($result_old["email_from"]==$email_from && $result_old["email_name"]==$email_name) unset($cc[$key]);
				}
			}
			if($result_new["email_addmetocc"]) {
				foreach($cc as $key=>$val) {
					list($email_from,$email_name)=__sendmail_parser($val);
					if($result_new["email_from"]==$email_from && $result_new["email_name"]==$email_name) unset($cc[$key]);
				}
				array_unshift($cc,$result_new["email_name"]." <".$result_new["email_from"].">");
			}
			$cc=implode("; ",$cc);
			if($cc) $cc.="; ";
		}
		// REPLACE THE STATE_CRT
		if($result_old && $result_new) {
			if($result_old["email_crt"]==$state_crt) {
				$state_crt=$result_new["email_crt"];
			}
		}
		// PREPARE THE OUTPUT
		$row=array("body"=>$body,"cc"=>$cc,"state_crt"=>$state_crt);
		$_RESULT=array("rows"=>array());
		set_array($_RESULT["rows"],"row",$row);
		// PREPARE THE OUTPUT
		$_RESULT["rows"]=array_values($_RESULT["rows"]);
		$buffer=json_encode($_RESULT);
		// CONTINUE
		output_buffer($buffer,"application/json");
	}
	// NORMAL REQUEST SIGNATURE CODE
	$file=__signature_getfile(getParam("id"));
	if(!$file) die();
	if(!$file["file"]) die();
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires(false);
	$type=$file["type"];
	if($type=="text/plain") {
		$file["data"]=htmlentities($file["data"],ENT_COMPAT,"UTF-8");
		$file["data"]=str_replace(array(" ","\t","\n"),array("&nbsp;",str_repeat("&nbsp;",8),"<br/>"),$file["data"]);
		$type="text/html";
	}
	header("Content-Type: ${type}");
	header("x-frame-options: SAMEORIGIN");
	require_once("php/getmail.php");
	if($type=="text/html") echo __HTML_PAGE_OPEN__.__HTML_TEXT_OPEN__;
	if($type=="text/plain") echo __HTML_PAGE_OPEN__.__PLAIN_TEXT_OPEN__;
	echo $file["data"];
	if($type=="text/html") echo __HTML_TEXT_CLOSE__.__HTML_PAGE_CLOSE__;
	if($type=="text/plain") echo __PLAIN_TEXT_CLOSE__.__HTML_PAGE_CLOSE__;
	ob_end_flush();
	die();
}
?>