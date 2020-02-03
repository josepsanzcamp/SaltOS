<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2020 by Josep Sanz Campderrós
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

function capture_next_error() {
	global $_ERROR_HANDLER;
	if(!isset($_ERROR_HANDLER["level"])) show_php_error(array("phperror"=>"error_handler without levels availables"));
	$_ERROR_HANDLER["level"]++;
	array_push($_ERROR_HANDLER["msg"],"");
}

function get_clear_error() {
	global $_ERROR_HANDLER;
	if($_ERROR_HANDLER["level"]<=0) show_php_error(array("phperror"=>"error_handler without levels availables"));
	$_ERROR_HANDLER["level"]--;
	return array_pop($_ERROR_HANDLER["msg"]);
}

function do_message_error($array,$format) {
	static $dict=array(
		"html"=>array(array("<h3>","</h3>"),array("<pre>","</pre>"),"<br/>"),
		"text"=>array(array("***** "," *****\n"),array("","\n"),"\n")
	);
	if(!isset($dict[$format])) die("Unknown format $format");
	static $types=array(
		"dberror"=>"DB Error",
		"phperror"=>"PHP Error",
		"xmlerror"=>"XML Error",
		"jserror"=>"JS Error",
		"dbwarning"=>"DB Warning",
		"phpwarning"=>"PHP Warning",
		"xmlwarning"=>"XML Warning",
		"jswarning"=>"JS Warning",
		"source"=>"Source",
		"exception"=>"Exception",
		"details"=>"Details",
		"query"=>"Query",
		"backtrace"=>"Backtrace",
		"debug"=>"Debug",
	);
	$msg=array();
	foreach($array as $type=>$data) {
		switch($type) {
			case "dberror":
				$privated=array(getDefault("db/host"),getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name"));
				$data=str_replace($privated,"...",$data);
				break;
			case "backtrace":
				foreach($data as $key=>$item) {
					$temp=$key." => ".$item["function"];
					if(isset($item["class"])) $temp.=" (in class ".$item["class"].")";
					if(isset($item["file"]) && isset($item["line"])) $temp.=" (in file ".basename($item["file"])." at line ".$item["line"].")";
					$data[$key]=$temp;
				}
				$data=implode($dict[$format][2],$data);
				break;
			case "debug":
				foreach($data as $key=>$item) $data[$key]="${key} => ${item}";
				$data=implode($dict[$format][2],$data);
				break;
		}
		if(isset($types[$type])) $msg[]=array($types[$type],$data);
	}
	foreach($msg as $key=>$item) $msg[$key]=$dict[$format][0][0].$item[0].$dict[$format][0][1].$dict[$format][1][0].$item[1].$dict[$format][1][1];
	$msg=implode($msg);
	return $msg;
}

function show_php_error($array=null) {
	global $_ERROR_HANDLER;
	static $backup=null;
	if($array===null && $backup!==null) {
		while($_ERROR_HANDLER["level"]>0) get_clear_error();
		show_php_error($backup);
	}
	// ADD BACKTRACE AND DEBUG IF NOT FOUND
	if(!isset($array["backtrace"])) $array["backtrace"]=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	if(!isset($array["debug"])) $array["debug"]=session_backtrace();
	// CREATE THE MESSAGE ERROR USING HTML ENTITIES AND PLAIN TEXT
	$msg_html=do_message_error($array,"html");
	$msg_text=do_message_error($array,"text");
	$msg=getServer("SHELL")?$msg_text:$msg_html;
	// REFUSE THE DEPRECATED WARNINGS
	if(isset($array["phperror"]) && stripos($array["phperror"],"deprecated")!==false) {
		$hash=md5($msg_text);
		$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
		if(is_writable($dir)) {
			$file=isset($array["file"])?$array["file"]:getDefault("debug/deprecatedfile","deprecated.log");
			if(checklog($hash,$file)) $msg_text="";
			addlog("${msg_text}***** ${hash} *****",$file);
		}
		return;
	}
	// CHECK IF CAPTURE ERROR WAS ACTIVE
	if($_ERROR_HANDLER["level"]>0) {
		$old=array_pop($_ERROR_HANDLER["msg"]);
		array_push($_ERROR_HANDLER["msg"],$old.$msg);
		$backup=$array;
		return;
	}
	// ADD THE MSG_ALT TO THE ERROR LOG FILE
	$hash=md5($msg_text);
	$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
	if(is_writable($dir)) {
		$file=isset($array["file"])?$array["file"]:getDefault("debug/errorfile","error.log");
		static $types=array(
			array("dberror","debug/dberrorfile","dberror.log"),
			array("phperror","debug/phperrorfile","phperror.log"),
			array("xmlerror","debug/xmlerrorfile","xmlerror.log"),
			array("jserror","debug/jserrorfile","jserror.log"),
			array("dbwarning","debug/dbwarningfile","dbwarning.log"),
			array("phpwarning","debug/phpwarningfile","phpwarning.log"),
			array("xmlwarning","debug/xmlwarningfile","xmlwarning.log"),
			array("jswarning","debug/jswarningfile","jswarning.log"),
		);
		foreach($types as $type) {
			if(isset($array[$type[0]])) {
				$file=getDefault($type[1],$type[2]);
				break;
			}
		}
		if(checklog($hash,$file)) $msg_text="";
		addlog("${msg_text}***** ${hash} *****",$file);
	}
	// CHECK FOR CANCEL_DIE
	if(isset($array["cancel"]) && eval_bool($array["cancel"])) return;
	if(isset($array["die"]) && !eval_bool($array["die"])) return;
	while(ob_get_level()) ob_end_clean(); // TRICK TO CLEAR SCREEN
	// PREPARE THE FINAL REPORT (ONLY IN NOT SHELL MODE)
	if(!getServer("SHELL")) {
		$msg=pretty_html_error($msg);
		if(!headers_sent()) {
			output_handler(array(
				"data"=>$msg,
				"type"=>"text/html",
				"cache"=>false
			));
		}
	}
	// DUMP TO STDOUT
	echo $msg;
	die();
}

function pretty_html_error($msg) {
	$html=__HTML_DOCTYPE__;
	$html.="<html>";
	$html.="<head>";
	$html.="<title>".get_name_version_revision()."</title>";
	$html.="<style>";
	$html.=".phperror { color:#fff; background:#00f; margin:0; padding:10px; font-family:monospace; }";
	$html.=".phperror form { display:inline; float:right; }";
	$html.=".phperror input { background:#fff; color:#00f; font-weight:bold; border:0; padding:10px 20px; font-family:monospace; margin-left:10px; }";
	$html.=".phperror input:hover { background:#000; color:#fff; cursor:pointer; }";
	$html.=".phperror h1 { display:inline; }";
	$html.=".phperror pre { white-space:normal; }";
	$html.="</style>";
	$html.="</head>";
	$html.="<body class='phperror'>";
	$html.=__pretty_html_error_helper("",array("page"=>"home"),LANG_LOADED()?LANG("gotohome"):"Go to home");
	$html.=__pretty_html_error_helper("",array("page"=>"support","subject"=>(LANG_LOADED()?LANG("notifybug"):"Notify bug").": ".get_name_version_revision(),"comentarios"=>$msg),LANG_LOADED()?LANG("notifybug"):"Notify bug");
	$html.="<h1>".get_name_version_revision()."</h1>";
	$html.=$msg;
	$html.="</body>";
	$html.="</html>";
	return $html;
}

function __pretty_html_error_helper($action,$hiddens,$submit) {
	$html="";
	$html.="<form action='${action}' method='post'>";
	foreach($hiddens as $key=>$val) {
		$val=htmlentities($val,ENT_COMPAT,"UTF-8");
		$html.="<input type=\"hidden\" name=\"${key}\" value=\"${val}\"/>";
	}
	$html.="<input type='submit' value='${submit}'/>";
	$html.="</form>";
	return $html;
}

function __error_handler($type,$message,$file,$line) {
	show_php_error(array("phperror"=>"${message} (code ${type})","details"=>"Error on file '".basename($file)."' at line ${line}","backtrace"=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
}

function __exception_handler($e) {
	show_php_error(array("exception"=>$e->getMessage()." (code ".$e->getCode().")","details"=>"Error on file '".basename($e->getFile())."' at line ".$e->getLine(),"backtrace"=>$e->getTrace()));
}

function __shutdown_handler() {
	global $_ERROR_HANDLER;
	if($_ERROR_HANDLER["level"]>0) show_php_error();
	$error=error_get_last();
	$types=array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR);
	if(is_array($error) && isset($error["type"]) && in_array($error["type"],$types)) {
		show_php_error(array("phperror"=>"${error["message"]}","details"=>"Error on file '".basename($error["file"])."' at line ${error["line"]}","backtrace"=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
	}
	semaphore_shutdown();
}

function program_handlers() {
	global $_ERROR_HANDLER;
	$_ERROR_HANDLER=array("level"=>0,"msg"=>array());
	// IMPORTANT CHECK
	if(!ini_get("date.timezone")) ini_set("date.timezone","Europe/Madrid");
	// CONTINUE
	error_reporting(E_ALL);
	set_error_handler("__error_handler");
	set_exception_handler("__exception_handler");
	register_shutdown_function("__shutdown_handler");
	time_get_usage(true);
}

function upload_error2string($error) {
	static $errors=array(
		UPLOAD_ERR_OK=>"UPLOAD_ERR_OK",					// 0
		UPLOAD_ERR_INI_SIZE=>"UPLOAD_ERR_INI_SIZE",		// 1
		UPLOAD_ERR_FORM_SIZE=>"UPLOAD_ERR_FORM_SIZE",	// 2
		UPLOAD_ERR_PARTIAL=>"UPLOAD_ERR_PARTIAL",		// 3
		UPLOAD_ERR_NO_FILE=>"UPLOAD_ERR_NO_FILE",		// 4
		UPLOAD_ERR_NO_TMP_DIR=>"UPLOAD_ERR_NO_TMP_DIR",	// 6
		UPLOAD_ERR_CANT_WRITE=>"UPLOAD_ERR_CANT_WRITE",	// 7
		UPLOAD_ERR_EXTENSION=>"UPLOAD_ERR_EXTENSION"	// 8
	);
	if(isset($errors[$error])) return $errors[$error];
	return "UPLOAD_ERR_UNKWOWN";
}

?>