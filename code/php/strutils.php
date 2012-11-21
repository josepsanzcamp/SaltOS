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
function getString($string) {
	if(ini_get("magic_quotes_gpc")!=1) $string=addslashes($string);
	return $string;
}

function getParam($index,$default="") {
	if(substr($index,-5,5)=="_file") {
		$prefix=substr($index,0,-5);
		if(file_exists(__getParam_helper($prefix."_temp"))) {
			move_uploaded_file(__getParam_helper($prefix."_temp"),get_directory("dirs/filesdir").__getParam_helper($prefix."_file"));
		}
	}
	return __getParam_helper($index,$default);
}

function __getParam_helper($index,$default="") {
	if(isset($_POST[$index])) return getString($_POST[$index]);
	if(isset($_GET[$index])) return getString($_GET[$index]);
	return $default;
}

function getParamAsArray($prefix,$default="") {
	$len=strlen($prefix);
	$result=array();
	foreach(array_merge($_POST,$_GET) as $key=>$val) {
		if(substr($key,0,$len)==$prefix) $result[substr($key,$len)]=getParam($key,$default);
	}
	return $result;
}

function setParam($index,$value="") {
	if(isset($_POST[$index])) $_POST[$index]=$value;
	elseif(isset($_GET[$index])) $_GET[$index]=$value;
	else $_POST[$index]=$value;
}

function useSession($name,$value="",$default="") {
	if($value!="") $_SESSION[$name]=($value=="null")?"":$value;
	elseif(isset($_SESSION[$name]) && $_SESSION[$name]!="") $value=$_SESSION[$name];
	else $value=$default;
	return $value;
}

function useCookie($name,$value="",$default="") {
	if($value!="") __useCookie_setcookie($name,$value=="null"?"":$value,time()+getDefault("security/cookietimeout"));
	elseif(isset($_COOKIE[$name]) && $_COOKIE[$name]!="") $value=$_COOKIE[$name];
	else $value=$default;
	return $value;
}

function __useCookie_setcookie($name,$value,$expire) {
	setcookie($name,$value,$expire,dirname(getServer("SCRIPT_NAME")),"",eval_bool(getDefault("server/forcessl")),false);
	setcookie("__".$name."__",$value==""?"":$expire,$expire,dirname(getServer("SCRIPT_NAME")),"",eval_bool(getDefault("server/forcessl")),false);
}

function convert_date($date) {
	$year=substr($date,0,4);
	$month=substr($date,5,2);
	$day=substr($date,8,2);
	$newdate="${day}/${month}/${year}";
	return $newdate;
}

function invert_date($date) {
	$day=substr($date,0,2);
	$month=substr($date,3,2);
	$year=substr($date,6,4);
	$newdate="$year-$month-$day";
	return $newdate;
}

function current_datetime($offset=0) {
	return current_date($offset)." ".current_time($offset);
}

function current_date($offset=0) {
	return date("Y-m-d",time()+$offset);
}

function current_time($offset=0) {
	return date("H:i:s",time()+$offset);
}

function current_decimals($offset=0) {
	$decimals=explode(".",microtime(true)+$offset);
	return substr((isset($decimals[1])?$decimals[1]:"")."0000",0,4);
}

function current_datetime_decimals($offset=0) {
	return current_datetime($offset).".".current_decimals($offset);
}

function encode_bad_chars_file($file) {
	$file=strrev($file);
	$file=explode(".",$file,2);
	// EXISTS MULTIPLE STRREV TO PREVENT UTF8 DATA LOST
	foreach($file as $key=>$val) $file[$key]=strrev(encode_bad_chars(strrev($val)));
	$file=implode(".",$file);
	$file=strrev($file);
	return $file;
}

function encode_bad_chars($cad,$pad="_") {
	static $orig=array(
		"á","à","ä","é","è","ë","í","ì","ï","ó","ò","ö","ú","ù","ü","ñ","ç",
		"Á","À","Ä","É","È","Ë","Í","Ì","Ï","Ó","Ò","Ö","Ú","Ù","Ü","Ñ","Ç");
	static $dest=array(
		"a","a","a","e","e","e","i","i","i","o","o","o","u","u","u","n","c",
		"a","a","a","e","e","e","i","i","i","o","o","o","u","u","u","n","c");
	$cad=str_replace($orig,$dest,$cad);
	$cad=strtolower($cad);
	$len=strlen($cad);
	for($i=0;$i<$len;$i++) {
		$letter=$cad[$i];
		$replace=1;
		if($letter>="a" && $letter<="z") $replace=0;
		if($letter>="0" && $letter<="9") $replace=0;
		if($replace) $cad[$i]=" ";
	}
	$cad=encode_words($cad,$pad);
	return $cad;
}

function encode_words($cad,$pad=" ") {
	$cad=trim($cad);
	$count=1;
	while($count) $cad=str_replace("  "," ",$cad,$count);
	$cad=str_replace(" ",$pad,$cad);
	return $cad;
}

function encode_search($cad,$pad=" ") {
	static $bad_chars=null;
	if(is_null($bad_chars)) for($i=0;$i<32;$i++) $bad_chars[]=chr($i);
	$cad=str_replace($bad_chars,$pad,$cad);
	$cad=encode_words($cad,$pad);
	return $cad;
}

function querystring2array($querystring) {
	$items=explode("&",$querystring);
	$result=array();
	foreach($items as $key=>$val) {
		$par=explode("=",$val,2);
		if(!isset($par[1])) $par[1]="";
		$par[1]=rawurldecode($par[1]);
		$result[$par[0]]=$par[1];
	}
	return $result;
}

function sprintr($array) {
	ob_start();
	print_r($array);
	$buffer=ob_get_clean();
	$buffer=explode("\n",$buffer);
	foreach($buffer as $key=>$val) if(in_array(trim($val),array("(",")",""))) unset($buffer[$key]);
	$buffer=implode("\n",$buffer)."\n";
	return $buffer;
}

function get_base() {
	// MAIN VARIABLES
	$protocol="http://";
	$servername=getServer("SERVER_NAME");
	$added="";
	$scriptname=getServer("SCRIPT_NAME");
	// SOME CHECKS
	$serverport=getServer("SERVER_PORT");
	$porthttp=getDefault("server/porthttp",80);
	$porthttps=getDefault("server/porthttps",443);
	if($serverport==$porthttp) {
		$protocol="http://";
		if($porthttp!=80) $added=":$serverport";
	}
	if($serverport==$porthttps) {
		$protocol="https://";
		if($porthttp!=443) $added=":$serverport";
	}
	// CONTINUE
	$url=$protocol.$servername.$added.$scriptname;
	return $url;
}

function sign($n) {
	return $n==abs($n)?1:-1;
}

function color2dec($color,$component) {
	static $offset=array("R"=>1,"G"=>3,"B"=>5);
	if(!isset($offset[$component])) show_php_error(array("phperror"=>"Unknown component on color2dec function"));
	return hexdec(substr($color,$offset[$component],2));
}

function dateval($value) {
	static $expr=array("-",":",",",".","/");
	$value=str_replace($expr," ",$value);
	$value=encode_words($value," ");
	$temp=explode(" ",$value);
	foreach($temp as $key=>$val) $temp[$key]=intval($val);
	for($i=0;$i<3;$i++) if(!isset($temp[$i])) $temp[$i]=0;
	if($temp[2]>1900) {
		$value=sprintf("%04d-%02d-%02d",$temp[2],$temp[1],$temp[0]);
	} else {
		$value=sprintf("%04d-%02d-%02d",$temp[0],$temp[1],$temp[2]);
	}
	return $value;
}

function timeval($value) {
	static $expr=array("-",":",",",".","/");
	$value=str_replace($expr," ",$value);
	$value=encode_words($value," ");
	$temp=explode(" ",$value);
	foreach($temp as $key=>$val) $temp[$key]=intval($val);
	for($i=0;$i<3;$i++) if(!isset($temp[$i])) $temp[$i]=0;
	$value=sprintf("%02d:%02d:%02d",$temp[0],$temp[1],$temp[2]);
	return $value;
}

function datetimeval($value) {
	static $expr=array("-",":",",",".","/");
	$value=str_replace($expr," ",$value);
	$value=encode_words($value," ");
	$temp=explode(" ",$value);
	foreach($temp as $key=>$val) $temp[$key]=intval($val);
	for($i=0;$i<6;$i++) if(!isset($temp[$i])) $temp[$i]=0;
	if($temp[2]>1900) {
		$value=sprintf("%04d-%02d-%02d %02d:%02d:%02d",$temp[2],$temp[1],$temp[0],$temp[3],$temp[4],$temp[5]);
	} else {
		$value=sprintf("%04d-%02d-%02d %02d:%02d:%02d",$temp[0],$temp[1],$temp[2],$temp[3],$temp[4],$temp[5]);
	}
	return $value;
}

function get_unique_id_md5() {
	init_random();
	return md5(uniqid(rand(),true));
}

function intelligence_cut($txt,$max) {
	$len=strlen($txt);
	if($len>$max) {
		while($max>0 && $txt[$max]!=" ") $max--;
		if($max==0) while($max<$len && $txt[$max]!=" ") $max++;
		if($max>0) if(in_array($txt[$max-1],array(",",".","-","("))) $max--;
		$preview=($max==$len)?$txt:substr($txt,0,$max)."...";
	} else {
		$preview=$txt;
	}
	return $preview;
}

function header_powered() {
	header("X-Powered-By: ".get_name_version_revision());
}

function header_expires($cache=true) {
	if($cache) {
		header("Expires: ".gmdate("D, d M Y H:i:s",time()+getDefault("cache/cachegctimeout"))." GMT");
		header("Cache-Control: max-age=".getDefault("cache/cachegctimeout"));
		header("Pragma: public");
		if(is_string($cache) && strlen($cache)==32) header("ETag: $cache");
	} else {
		header("Expires: -1");
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
	}
}

function header_etag($hash) {
	$etag=getServer("HTTP_IF_NONE_MATCH");
	if($etag==$hash) {
		ob_start_protected(getDefault("obhandler"));
		header_powered();
		header_expires();
		header("HTTP/1.0 304 Not Modified");
		ob_end_flush();
		die();
	}
}

function xml2html($buffer,$usecache=true) {
	// SOME CHECKS
	if(!class_exists("DomDocument")) show_php_error(array("phperror"=>"Class DomDocument not found","details"=>"Try to install php-xml package"));
	if(!class_exists("XsltProcessor")) show_php_error(array("phperror"=>"Class XsltProcessor not found","details"=>"Try to install php-xsl package"));
	// GET THE STYLESHEET
	$pos1=strpos($buffer,"<?xml-stylesheet");
	$pos2=strpos($buffer,"href=",$pos1);
	if(substr($buffer,$pos2+5,1)=='"') $pos3=strpos($buffer,'"',$pos2+6);
	if(substr($buffer,$pos2+5,1)=="'") $pos3=strpos($buffer,"'",$pos2+6);
	if(!isset($pos3)) show_php_error(array("phperror"=>"Unknown XSL file","details"=>"Could not find the xml-stylesheet tag in XML"));
	$xslfile=substr($buffer,$pos2+6,$pos3-$pos2-6);
	$pos4=strpos($xslfile,"?");
	if($pos4!==false) $xslfile=substr($xslfile,0,$pos4);
	if(!file_exists($xslfile)) show_php_error(array("phperror"=>"Unknown XSL file","details"=>"File '$xslfile' not found"));
	// CACHE MANAGEMENT
	$usecache=$usecache && eval_bool(getDefault("cache/usexml2htmlcache",true));
	$usehtmlminify=eval_bool(getDefault("cache/usehtmlminify",true));
	if($usecache) {
		$cache=get_cache_file(array($buffer,$usehtmlminify),getDefault("exts/htmlext",".htm"));
		if(cache_exists($cache,$xslfile)) return file_get_contents($cache);
	}
	// BEGIN THE TRANSFORMATION
	$doc=new DomDocument();
	$xsl=new XsltProcessor();
	$xsldata=file_get_contents($xslfile);
	if($usehtmlminify) $xsldata=str_replace('indent="yes"','indent="no"',$xsldata);
	if(!$usehtmlminify) $xsldata=str_replace('indent="no"','indent="yes"',$xsldata);
	$doc->loadXML($xsldata,LIBXML_COMPACT);
	$xsl->importStylesheet($doc);
	$doc->loadXML($buffer,LIBXML_COMPACT);
	$buffer=$xsl->transformToXML($doc);
	if($usecache) {
		file_put_contents($cache,$buffer);
		chmod_protected($cache,0666);
	}
	return $buffer;
}

function is_mobile() {
	static $ismobile=null;
	if(is_null($ismobile)) {
		include("lib/mobiledetect/Mobile_Detect.php");
		if(!isset($_SERVER["HTTP_ACCEPT"])) $_SERVER["HTTP_ACCEPT"]="";
		if(!isset($_SERVER["HTTP_USER_AGENT"])) $_SERVER["HTTP_USER_AGENT"]="";
		$detect=new Mobile_Detect();
		$ismobile=$detect->isMobile();
	}
	return $ismobile;
}

function normalize_value($value) {
	$number=intval(substr($value,0,-1));
	$letter=strtoupper(substr($value,-1,1));
	if($letter=="K") $value=$number*1024;
	if($letter=="M") $value=$number*1024*1024;
	if($letter=="G") $value=$number*1024*1024*1024;
	return $value;
}

define("__INFO_NAME__","SaltOS");
define("__INFO_VERSION__","3.1");
define("__INFO_REVISION__",svnversion("../code"));
define("__INFO_COPYRIGHT__","Copyright (C) 2012 by Josep Sanz Campderrós");

function get_name_version_revision($copyright=false) {
	return getDefault("info/name",__INFO_NAME__)." v".getDefault("info/version",__INFO_VERSION__)." r".getDefault("info/revision",__INFO_REVISION__).($copyright?", ".getDefault("info/copyright",__INFO_COPYRIGHT__):"");
}

function getServer($index,$default="") {
	return isset($_SERVER[$index])?$_SERVER[$index]:$default;
}

function str_word_count_utf8($subject) {
	static $pattern="/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u";
	$matches=array();
	preg_match_all($pattern,$subject,$matches);
	return $matches[0];
}

function output_buffer($buffer,$type) {
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires(false);
	header("Content-type: $type");
	echo $buffer;
	ob_end_flush();
	die();
}

function output_file($file) {
	$hash=md5(file_get_contents($file));
	header_etag($hash);
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires($hash);
	$type=saltos_content_type($file);
	header("Content-Type: $type");
	readfile($file);
	ob_end_flush();
	die();
}

function minify_css($buffer) {
	include_once("lib/minify/cssmin-v3.0.1.php");
	capture_next_error();
	try {
		$buffer2=CssMin::minify($buffer);
	} catch(Exception $e) {
		__exception_handler($e);
	}
	$error=get_clear_error();
	$buffer=$error?$buffer:$buffer2;
	return $buffer;
}

function minify_js($buffer) {
	include_once("lib/minify/jsmin-1.1.2.php");
	capture_next_error();
	try {
		$buffer2=JSMin::minify($buffer);
	} catch(Exception $e) {
		__exception_handler($e);
	}
	$error=get_clear_error();
	$buffer=$error?$buffer:$buffer2;
	return $buffer;
}

function inline_images($buffer) {
	$pos=strpos($buffer,"url(");
	while($pos!==false) {
		$pos2=strpos($buffer,")",$pos+4);
		$img=substr($buffer,$pos+4,$pos2-$pos-4);
		if(file_exists($img)) {
			$type=saltos_content_type($img);
			if(substr($type,0,5)=="image") {
				$data="data:$type;base64,".base64_encode(file_get_contents($img));
				$buffer=substr_replace($buffer,$data,$pos+4,$pos2-$pos-4);
				$pos2=$pos2-strlen($img)+strlen($data);
			}
		}
		$pos=strpos($buffer,"url(",$pos2+1);
	}
	return $buffer;
}

function svnversion($dir) {
	$rev=0;
	$dir=realpath($dir);
	for(;;) {
		// FOR SUBVERSION >= 12
		$file="$dir/.svn/wc.db";
		if(file_exists($file)) {
			$data=file_get_contents($file);
			$pos=strpos($data,"normalfile");
			if($pos!==false) $rev=ord($data[$pos-1])+ord($data[$pos-2])*256;
			break;
		}
		// FOR SUBVERSION <= 11
		$file="$dir/.svn/entries";
		if(file_exists($file)) {
			$data=file($file);
			if(isset($data[3])) $rev=intval($data[3]);
			break;
		}
		if($dir=="/") break;
		$dir=realpath($dir."/..");
	}
	return $rev;
}

function check_password($pass,$hash) {
	require_once("lib/phpass/PasswordHash.php");
	$t_hasher=new PasswordHash(8,true);
	$result=$t_hasher->CheckPassword($pass,$hash);
	unset($t_hasher);
	return $result;
}

function hash_password($pass) {
	require_once("lib/phpass/PasswordHash.php");
	$t_hasher=new PasswordHash(8,true);
	$result=$t_hasher->HashPassword($pass);
	unset($t_hasher);
	return $result;
}

function password_strength($pass) {
	require_once("lib/wolfsoftware/password_strength.class.php");
	$ps=new Password_Strength();
	$ps->set_password($pass);
	$ps->calculate();
	$score=round($ps->get_score(),0);
	unset($ps);
	return $score;
}

function ob_start_protected($param="") {
	capture_next_error();
	if($param=="") ob_start();
	if($param!="") ob_start($param);
	$error=get_clear_error();
	if($error) ob_start();
}

function isphp54() {
	return version_compare(PHP_VERSION,"5.4","ge");
}
?>