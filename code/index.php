<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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
program_handlers();
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
if(!semaphore_acquire(__FILE__)) show_php_error(array("phperror"=>"Could not acquire the semaphore"));
sess_init();
check_remember();
check_basicauth();
pre_datauser();
sess_close();
check_security("main");
semaphore_release(__FILE__);
// GET THE LANGUAGE
$lang=getDefault("lang");
$lang=useCookie("lang","",$lang);
$lang=use_table_cookies("lang","",$lang);
if(!load_lang($lang)) $lang=getDefault("lang");
$lang=getDefault("forcelang",$lang);
$_LANG=eval_attr(xml2Array("xml/lang/${lang}.xml"));
$_CONFIG=eval_attr($_CONFIG);
if(getDefault("info/revision")=="SVN") $_CONFIG["info"]["revision"]=svnversion();
if(getDefault("info/revision")=="GIT") $_CONFIG["info"]["revision"]=gitversion();
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
if(!load_style($style)) $style=solve_style($style);
$style=getDefault("forcestyle",$style);
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
// TRICK FOR JSTREE
$jstree=detect_light_or_dark_from_style($style);
$jstreepre=getDefault("jstreepre");
$jstreepost=getDefault("jstreepost");
if(load_style($style)) set_array($_RESULT["styles"],"include",$jstreepre.$jstree.$jstreepost);
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
// MORE TO PREPARE THE OUTPUT
if(isset($_ALERT)) $_RESULT["alerts"]=$_ALERT;
if(isset($_ERROR)) $_RESULT["errors"]=$_ERROR;
$_RESULT["info"]["color"]=color_style($style);
$_RESULT["info"]["usejscache"]=getDefault("cache/usejscache");
$_RESULT["info"]["usecsscache"]=getDefault("cache/usecsscache");
$_RESULT["info"]["lang"]=$lang;
$_RESULT["info"]["dir"]=$_LANG["dir"];
// THE XSLT PROCESSOR CODE
$xsl=getDefault("forcexsl","default");
$buffer=__XML_HEADER__.array2xml($_RESULT);
$buffer=__HTML_DOCTYPE__.xml2html($buffer,"xsl/${xsl}.xsl");
// FLUSH THE OUTPUT NOW
output_handler(array(
	"data"=>$buffer,
	"type"=>"text/html",
	"cache"=>false
));
?>