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
if(!check_user()) action_denied();
// FUNCTION DEFINITIONS
function __signature_from2id($from) {
	require_once("php/sendmail.php");
	list($email_from,$email_name)=__sendmail_parser($from);
	$query="SELECT id FROM tbl_usuarios_c WHERE email_from='$email_from'";
	$result=execute_query($query);
	return $result;
}
function __signature_getfile($id) {
	if(!$id) return null;
	$query="SELECT * FROM tbl_usuarios_c WHERE id='$id'";
	$row=execute_query($query);
	if(!$row) return null;
	if(!$row["email_signature_file"]) return null;
	$id=$row["id"];
	$name=$row["email_signature"];
	$file=$row["email_signature_file"];
	$type=$row["email_signature_type"];
	$size=$row["email_signature_size"];
	$data=file_get_contents(get_directory("dirs/filesdir").$file);
	$alt=$row["email_name"]." (".$row["email_from"].")";
	return array("id"=>$id,"name"=>$name,"file"=>$file,"type"=>$type,"size"=>$size,"data"=>$data,"alt"=>$alt);
}
function __signature_getauto($file) {
	require_once("php/defines.php");
	if(!$file) return null;
	if(!$file["file"]) return null;
	if($file["type"]=="text/plain") {
		$file["auto"]=str_replace("\n",__HTML_NEW_LINE__,trim($file["data"]));
	} elseif($file["type"]=="text/html") {
		$file["auto"]=trim($file["data"]);
	} elseif(substr($file["type"],0,6)=="image/") {
		if(eval_bool(getDefault("cache/useimginline"))) {
			$data=base64_encode($file["data"]);
			$file["src"]="data:image/png;base64,${data}";
		} else {
			$file["src"]="xml.php?action=signature&id=${file["id"]}";
		}
		$file["auto"]="<img alt=\"${file["alt"]}\" border=\"0\" src=\"${file["src"]}\" />";
	} else {
		$file["auto"]="Name: ${file["name"]}".__HTML_NEW_LINE__."Type: ${file["type"]}".__HTML_NEW_LINE__."Size: ${file["size"]}";
	}
	$file["auto"]=__HTML_NEW_LINE__.__HTML_NEW_LINE__."<span ".__CSS_SIGNATURE__.">--".__HTML_NEW_LINE__."${file["auto"]}</span>";
	return $file;
}
// NORMAL ACTION CODE
if(getParam("action")=="signature") {
	require_once("php/defines.php");
	// DETECT IF IS A REQUEST FOR REPLACE BODY, CC AND STATE_CRT
	if(getParam("old") && getParam("new")) {
		require_once("php/sendmail.php");
		$old=stripslashes(getParam("old"));
		$new=stripslashes(getParam("new"));
		$body=stripslashes(getParam("body"));
		$cc=stripslashes(getParam("cc"));
		$state_crt=intval(getParam("state_crt"));
		// FIND THE OLD AND NEW SIGNATURES
		$cuenta_old=__signature_from2id($old);
		$cuenta_new=__signature_from2id($new);
		$file_old=__signature_getauto(__signature_getfile($cuenta_old));
		$file_new=__signature_getauto(__signature_getfile($cuenta_new));
		// REPLACE THE BODYES SIGNATURES
		if($file_old && $file_new) {
			$auto_old=$file_old["auto"];
			$auto_new=$file_new["auto"];
		} elseif($file_old && !$file_new) {
			$auto_old=$file_old["auto"];
			$auto_new=__HIDDEN_SIGNATURE__;
		} elseif(!$file_old && $file_new) {
			$auto_old=__HIDDEN_SIGNATURE__;
			$auto_new=$file_new["auto"];
		} elseif(!$file_old && !$file_new) {
			$auto_old=__HIDDEN_SIGNATURE__;
			$auto_new=__HIDDEN_SIGNATURE__;
		}
		$hash1=md5($body);
		$body=str_replace($auto_old,$auto_new,$body);
		$hash2=md5($body);
		if($hash1==$hash2) {
			// CKEDITOR CORRECTION
			$auto_old=str_replace("&","&amp;",$auto_old);
			$auto_new=str_replace("&","&amp;",$auto_new);
			$body=str_replace($auto_old,$auto_new,$body);
		}
		// FIND THE OLD AND NEW CC'S AND STATE_CRT'S
		$query="SELECT * FROM tbl_usuarios_c WHERE id='".$cuenta_old."'";
		$result_old=execute_query($query);
		$query="SELECT * FROM tbl_usuarios_c WHERE id='".$cuenta_new."'";
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
		$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
		$buffer.=array2xml($_RESULT);
		// FLUSH THE OUTPUT
		output_buffer($buffer,"text/xml");
	}
	// NORMAL REQUEST SIGNATURE CODE
	$file=__signature_getfile(getParam("id"));
	if(!$file) die();
	if(!$file["file"]) die();
	ob_start(getDefault("obhandler"));
	header_powered();
	header_expires(false);
	$type=$file["type"];
	$type1=strtok($type,"/");
	$type2=strtok(" ");
	if($type2=="plain") $type="text/html";
	header("Content-Type: ${type}");
	header("x-frame-options: SAMEORIGIN");
	if($type1=="text") echo __PAGE_HTML_OPEN__;
	if($type1=="text") echo ($type2=="plain")?__TEXT_PLAIN_OPEN__:__TEXT_HTML_OPEN__;
	echo $file["data"];
	if($type1=="text") echo ($type2=="plain")?__TEXT_PLAIN_CLOSE__:__TEXT_HTML_CLOSE__;
	if($type1=="text") echo __PAGE_HTML_CLOSE__;
	ob_end_flush();
	die();
}
?>