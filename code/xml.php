<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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
// BEGIN INCLUDING ALL CORE FILES
include("php/database.php");
include("php/sessions.php");
include("php/functions.php");
include("php/strutils.php");
include("php/sqlutils.php");
include("php/jsutils.php");
include("php/fileutils.php");
include("php/xml2array.php");
include("php/array2xml.php");
include("php/history.php");
// SOME IMPORTANT ITEMS
program_error_handler();
check_system();
fix_input_vars();
// NORMAL OPERATION
$_CONFIG=eval_attr(xml2array("files/config.xml"));
if(getDefault("ini_set")) eval_iniset(getDefault("ini_set"));
if(getDefault("putenv")) eval_putenv(getDefault("putenv"));
// EXECUTE SOME ITEMS
force_ssl();
cache_gc();
db_connect();
db_schema();
db_static();
sess_init();
check_remember();
check_basicauth();
pre_datauser();
sess_close();
check_security("main");
// GET THE LANGUAGE
$lang=getDefault("lang");
$lang=useCookie("lang","",$lang);
$lang=use_table_cookies("lang","",$lang);
if(!load_lang($lang)) $lang=getDefault("lang");
$lang=getDefault("forcelang",$lang);
$_LANG=eval_attr(xml2Array("xml/lang/${lang}.xml"));
$_CONFIG=eval_attr($_CONFIG);
// EXECUTE MORE ITEMS
post_datauser();
check_time();
check_postlimit();
// GET THE STYLES
$style=getDefault("style");
$style=useCookie("style","",$style);
$style=use_table_cookies("style","",$style);
$stylepre=getDefault("stylepre");
$stylepost=getDefault("stylepost");
if(!load_style($style)) $style=getDefault("style");
$style=getDefault("forcestyle",$style);
// GET THE ICONSET
$iconset=getDefault("iconset");
$iconset=useCookie("iconset","",$iconset);
$iconset=use_table_cookies("iconset","",$iconset);
$iconsetpre=getDefault("iconsetpre");
$iconsetpost=getDefault("iconsetpost");
if(!load_iconset($iconset)) $iconset=getDefault("iconset");
$iconset=getDefault("forceiconset",$iconset);
// GET THE FLOW PARAMETERS
$page=getParam("page",getDefault("page"));
$action=getParam("action",getDefault("action"));
$id=intval(getParam("id",getDefault("id")));
if(file_exists("php/action/${action}.php")) include("php/action/${action}.php");
$page=getParam("page");
if(!file_exists("xml/${page}.xml")) $page="";
if(in_array($action,array("list","form"))) $page=lastpage($page);
if(!file_exists("xml/${page}.xml")) $page=getDefault("page");
// PREPARE THE OUTPUT
$_RESULT=array();
$_RESULT["info"]=getDefault("info");
$_RESULT["styles"]=getDefault("styles");
$_RESULT["javascript"]=getDefault("javascript");
add_css_page($_RESULT,getDefault("forcecss","default"));
add_js_page($_RESULT,getDefault("forcejs","default"));
if(load_style($style)) set_array($_RESULT["styles"],"include",$stylepre.$style.$stylepost);
if(load_iconset($iconset)) set_array($_RESULT["styles"],"include",$iconsetpre.$iconset.$iconsetpost);
// SWITCH FOR EACH CASE
if(!check_user()) {
	$_LANG["default"]="login,common";
	$_CONFIG["login"]=eval_attr(xml2array("xml/login.xml"));
	$_RESULT["form"]=getDefault("login/form");
	add_css_js_page($_RESULT["form"],"login");
} elseif(check_user($page,"menu")) {
	$_LANG["default"]="${page},menu,common";
	$_CONFIG["menu"]=eval_attr(xml2array("xml/menu.xml"));
	$_RESULT["menu"]=getDefault("menu");
	if(file_exists("xml/${page}.xml")) {
		$_CONFIG[$page]=xml2array("xml/${page}.xml");
		$php="default";
		if(file_exists("php/${page}.php")) $php=$page;
		if(getDefault("$page/default")) $_CONFIG[$page]["default"]=eval_attr(getDefault("$page/default"));
		if($action=="list") history($page);
		include("php/${php}.php");
	} else {
		set_array($_ERROR,"error",LANG("permdenied"));
	}
} else {
	$_LANG["default"]="denied,menu,common";
	$_CONFIG["menu"]=eval_attr(xml2array("xml/menu.xml"));
	$_RESULT["menu"]=getDefault("menu");
	$_CONFIG["denied"]=eval_attr(xml2array("xml/denied.xml"));
	$_RESULT["form"]=getDefault("denied/form");
	add_css_js_page($_RESULT["form"],"denied");
	set_array($_ERROR,"error",LANG("permdenied"));
}
// GET ALERTS AND ERRORS FROM SESSION
sess_init();
if(isset($_SESSION["alerts"])) {
	foreach($_SESSION["alerts"] as $val) set_array($_ALERT,"alert",$val);
	unset($_SESSION["alerts"]);
}
if(isset($_SESSION["errors"])) {
	foreach($_SESSION["errors"] as $val) set_array($_ERROR,"error",$val);
	unset($_SESSION["errors"]);
}
sess_close();
// SOME DEBUG ISSUES
if(eval_bool(getDefault("debug/alertdebug"))) set_array($_ALERT,"alert","Alert debug ON");
if(eval_bool(getDefault("debug/errordebug"))) set_array($_ERROR,"error","Error debug ON");
// MORE TO PREPARE THE OUTPUT
if(isset($_ALERT)) $_RESULT["alerts"]=$_ALERT;
if(isset($_ERROR)) $_RESULT["errors"]=$_ERROR;
$_RESULT["info"]["color"]=getDefault("themeroller/themes/$style");
$_RESULT["info"]["usejscache"]=getDefault("cache/usejscache");
$_RESULT["info"]["usecsscache"]=getDefault("cache/usecsscache");
$_RESULT["info"]["lang"]=$lang;
$_RESULT["info"]["dir"]=$_LANG["dir"];
// THE XSLT PROCESSOR CODE
$xsl="default";
if(file_exists("xsl/${page}.xsl")) $xsl=$page;
if(getDefault("forcexsl")) $xsl=getDefault("forcexsl");
$_RESULT["info"]["xslt"]="xsl/${xsl}.xsl";
$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
$href=$_RESULT["info"]["xslt"]."?r=".$_RESULT["info"]["revision"];
$buffer.="<?xml-stylesheet type='text/xsl' href='${href}' ?>\n";
$buffer.=array2xml($_RESULT);
$format=strtolower(getDefault("format","xml"));
if(defined("__FORCE_FORMAT__")) $format=strtolower(__FORCE_FORMAT__);
if(!in_array($format,array("xml","html"))) show_php_error(array("phperror"=>"Unknown format '$format'"));
if($format=="html") $buffer=xml2html($buffer);
// FLUSH THE OUTPUT NOW
output_buffer($buffer,"text/$format");
?>