<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2018 by Josep Sanz Campderrós
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
// DEFINES
define("__XML_HEADER__","<?xml version='1.0' encoding='UTF-8' ?>\n");

// FUNCTIONS
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
	if(isset($_POST[$index])) return $_POST[$index];
	if(isset($_GET[$index])) return $_GET[$index];
	return $default;
}

function getParamWithoutPrefix($index,$default="") {
	return getParam($index,$default);
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
	setcookie($name,$value,$expire,dirname(getDefault("server/pathname",getServer("SCRIPT_NAME"))),"",eval_bool(getDefault("server/forcessl")),false);
	setcookie("__".$name."__",$value==""?"":$expire,$expire,dirname(getDefault("server/pathname",getServer("SCRIPT_NAME"))),"",eval_bool(getDefault("server/forcessl")),false);
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
	$decimals=microtime(true)+$offset;
	$decimals-=intval($decimals);
	return substr($decimals,2,4);
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
	$cad=prepare_words($cad,$pad);
	return $cad;
}

function prepare_words($cad,$pad=" ") {
	$cad=trim($cad);
	$count=1;
	while($count) $cad=str_replace("  "," ",$cad,$count);
	$cad=str_replace(" ",$pad,$cad);
	return $cad;
}

function querystring2array($querystring) {
	$items=explode("&",$querystring);
	$result=array();
	foreach($items as $item) {
		$par=explode("=",$item,2);
		if(!isset($par[1])) $par[1]="";
		$par[1]=rawurldecode($par[1]);
		$result[$par[0]]=$par[1];
	}
	return $result;
}

function sprintr($array,$oneline=false) {
	ob_start();
	print_r($array);
	$buffer=ob_get_clean();
	$buffer=explode("\n",$buffer);
	foreach($buffer as $key=>$val) if(in_array(trim($val),array("(",")",""))) unset($buffer[$key]);
	$buffer=implode($oneline?"":"\n",$buffer)."\n";
	return $buffer;
}

function get_base() {
	// MAIN VARIABLES
	$protocol="http://";
	$servername=getDefault("server/hostname",getServer("SERVER_NAME"));
	$addedport="";
	$scriptname=getDefault("server/pathname",getServer("SCRIPT_NAME"));
	// SOME CHECKS
	if(substr($scriptname,0,1)!="/") $scriptname="/".$scriptname;
	if(basename($scriptname)==getDefault("server/dirindex","index.php")) {
		$scriptname=dirname($scriptname);
		if(substr($scriptname,-1,1)!="/") $scriptname.="/";
	}
	// SOME CHECKS
	$serverport=getServer("SERVER_PORT");
	$porthttp=getDefault("server/porthttp",80);
	$porthttps=getDefault("server/porthttps",443);
	if($serverport==$porthttp) {
		$protocol="http://";
		if($porthttp!=80) $addedport=":$serverport";
	}
	if($serverport==$porthttps) {
		$protocol="https://";
		if($porthttp!=443) $addedport=":$serverport";
	}
	// CONTINUE
	$url=$protocol.$servername.$addedport.$scriptname;
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
	$value=prepare_words($value);
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
	$value=prepare_words($value);
	$temp=explode(" ",$value);
	foreach($temp as $key=>$val) $temp[$key]=intval($val);
	for($i=0;$i<3;$i++) if(!isset($temp[$i])) $temp[$i]=0;
	$value=sprintf("%02d:%02d:%02d",$temp[0],$temp[1],$temp[2]);
	return $value;
}

function datetimeval($value) {
	static $expr=array("-",":",",",".","/");
	$value=str_replace($expr," ",$value);
	$value=prepare_words($value);
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

function intelligence_cut($txt,$max,$end="...") {
	$len=strlen($txt);
	if($len>$max) {
		while($max>0 && $txt[$max]!=" ") $max--;
		if($max==0) while($max<$len && $txt[$max]!=" ") $max++;
		if($max>0) if(in_array($txt[$max-1],array(",",".","-","("))) $max--;
		$preview=($max==$len)?$txt:substr($txt,0,$max).$end;
	} else {
		$preview=$txt;
	}
	return $preview;
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
	if($usecache) {
		$cache=get_cache_file($buffer,".htm");
		if(cache_exists($cache,$xslfile)) return file_get_contents($cache);
	}
	// BEGIN THE TRANSFORMATION
	$doc=new DomDocument();
	$xsl=new XsltProcessor();
	$xsldata=file_get_contents($xslfile);
	$doc->loadXML($xsldata,LIBXML_COMPACT);
	$xsl->importStylesheet($doc);
	$doc->loadXML($buffer,LIBXML_COMPACT);
	capture_next_error();
	$buffer=$xsl->transformToXML($doc);
	$error=get_clear_error();
	// TO PREVENT A BUG IN LIBXML 2.9.1
	if($error!="" && !words_exists("id already defined",$error)) show_php_error();
	if($usecache) {
		file_put_contents($cache,$buffer);
		chmod_protected($cache,0666);
	}
	return $buffer;
}

function ismobile() {
	static $ismobile=null;
	if($ismobile===null) {
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

function get_name_version_revision($copyright=false) {
	$result=getDefault("info/name","SaltOS");
	$result.=" v".getDefault("info/version","3.5");
	if(!is_array(getDefault("info/revision","SVN"))) $result.=" r".getDefault("info/revision","SVN");
	if($copyright) $result.=", ".getDefault("info/copyright","Copyright (C) 2007-2018 by Josep Sanz Campderrós");
	return $result;
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

function output_handler($array) {
	$file=isset($array["file"])?$array["file"]:"";
	$data=isset($array["data"])?$array["data"]:"";
	$type=isset($array["type"])?$array["type"]:"";
	$cache=isset($array["cache"])?$array["cache"]:"";
	$name=isset($array["name"])?$array["name"]:"";
	$extra=isset($array["extra"])?$array["extra"]:array();
	$die=isset($array["die"])?$array["die"]:true;
	if($file!="") {
		if(!file_exists($file) || !is_file($file)) show_php_error(array("phperror"=>"file ${file} not found"));
		if($data=="" && filesize($file)<memory_get_free(true)/3) $data=file_get_contents($file);
		if($type=="") $type=saltos_content_type($file);
	}
	if($type==="") show_php_error(array("phperror"=>"output_handler requires the type parameter"));
	if($cache==="") show_php_error(array("phperror"=>"output_handler requires the cache parameter"));
	header("X-Powered-By: ".get_name_version_revision());
	if($cache) {
		$hash1=getServer("HTTP_IF_NONE_MATCH");
		if($file!="" && $data=="") {
			$hash2=md5_file($file);
		} else {
			$hash2=md5($data);
		}
		if($hash1==$hash2) {
			header("HTTP/1.1 304 Not Modified");
			die();
		}
	}
	if($file!="" && $data=="") {
		header("Content-Encoding: none");
	} else {
		$encoding=getServer("HTTP_ACCEPT_ENCODING");
		if(stripos($encoding,"gzip")!==false && function_exists("gzencode")) {
			header("Content-Encoding: gzip");
			$data=gzencode($data);
		} elseif(stripos($encoding,"deflate")!==false && function_exists("gzdeflate")) {
			header("Content-Encoding: deflate");
			$data=gzdeflate($data);
		} else {
			header("Content-Encoding: none");
		}
		header("Vary: Accept-Encoding");
	}
	if($file!="" && $data=="") {
		$size=filesize($file);
	} else {
		$size=strlen($data);
	}
	if($cache) {
		header("Expires: ".gmdate("D, d M Y H:i:s",time()+getDefault("cache/cachegctimeout"))." GMT");
		header("Cache-Control: max-age=".getDefault("cache/cachegctimeout").", no-transform");
		header("Pragma: public");
		header("ETag: ${hash2}");
	} else {
		header("Expires: -1");
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform");
		header("Pragma: no-cache");
	}
	header("Content-Type: ${type}");
	header("Content-Length: ${size}");
	if($name!="") header("Content-disposition: attachment; filename=\"${name}\"");
	foreach($extra as $temp) header($temp,false);
	header("Connection: keep-alive, close");
	if($file!="" && $data=="") {
		readfile_protected($file);
	} else {
		echo $data;
	}
	if($die) die();
}

function inline_images($buffer) {
	$pos=strpos($buffer,"url(");
	while($pos!==false) {
		$pos2=strpos($buffer,")",$pos+4);
		$img=substr($buffer,$pos+4,$pos2-$pos-4);
		if(in_array(substr($img,0,1),array("'",'"'))) $img=substr($img,1);
		if(in_array(substr($img,-1,1),array("'",'"'))) $img=substr($img,0,-1);
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

function svnversion($dir=".") {
	if($dir=="." && file_exists("../code")) $dir="../code";
	// USING REGULAR FILE
	if(file_exists("${dir}/svnversion")) {
		return intval(file_get_contents("${dir}/svnversion"));
	}
	// USING SVNVERSION
	if(check_commands("svnversion",getDefault("default/commandexpires",60))) {
		return intval(ob_passthru("cd ${dir}; svnversion",getDefault("default/commandexpires",60)));
	}
	// NOTHING TO DO
	return 0;
}

function gitversion($dir=".") {
	if($dir=="." && file_exists("../code")) $dir="../code";
	// USING REGULAR FILE
	if(file_exists("${dir}/gitversion")) {
		return intval(file_get_contents("${dir}/gitversion"));
	}
	// USING GIT
	if(check_commands("git",getDefault("default/commandexpires",60))) {
		return intval(ob_passthru("cd ${dir}; git rev-list HEAD --count",getDefault("default/commandexpires",60)));
	}
	// NOTHING TO DO
	return 0;
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
	// TO PREVENT /DEV/URANDOM RESTRICTION ACCESS ERRORS
	capture_next_error();
	$result=$t_hasher->HashPassword($pass);
	// TO PREVENT /DEV/URANDOM RESTRICTION ACCESS ERRORS
	get_clear_error();
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

function isphp($version) {
	return version_compare(PHP_VERSION,$version,">=");
}

function ishhvm() {
	return defined("HHVM_VERSION");
}

function ismsie($version=null) {
	$useragent=getServer("HTTP_USER_AGENT");
	if($version===null) {
		return strpos($useragent,"MSIE")!==false;
	} elseif(is_string($version)) {
		return strpos($useragent,"MSIE ${version}")!==false;
	} elseif(is_array($version)) {
		foreach($version as $v) if(strpos($useragent,"MSIE ${v}")!==false) return true;
		return false;
	}
}

// USING ROUNDCUBEMAIL FEATURES
function html2text($html) {
	require_once("lib/roundcube/rcube_html2text.php");
	$h2t=new rcube_html2text($html);
	capture_next_error();
	$text=$h2t->get_text();
	get_clear_error();
	return $text;
}

// RETURN THE UTF-8 CONVERTED STRING IF IT'S NEEDED
function getutf8($str) {
	if(!mb_check_encoding($str,"UTF-8")) {
		ob_start();
		$str=mb_convert_encoding($str,"UTF-8",implode(",",mb_detect_order()));
		ob_get_clean();
	}
	return $str;
}

// USING WORDPRESS FEATURES
function saltos_make_clickable($temp) {
	global $allowedentitynames;
	require_once("lib/wordpress/wordpress.php");
	$temp=make_clickable($temp);
	return $temp;
}

// FOR SOME HREF REPLACEMENTS
function href_replace($temp) {
	// REPLACE THE INTERNALS LINKS TO OPENCONTENT CALLS
	$orig="href='".get_base();
	$dest=str_replace("href=","__href__=",$orig);
	$onclick="onclick='parent.opencontent(this.href);return false' ";
	$orig=array($orig,str_replace("'",'"',$orig),str_replace("'",'',$orig));
	$dest=array($onclick.$dest,$onclick.str_replace("'",'"',$dest),$onclick.str_replace("'",'',$dest));
	$temp=str_replace($orig,$dest,$temp);
	// REPLACE THE MAILTO LINKS TO MAILTO CALLS
	$orig="href='mailto:";
	$dest=str_replace("href=","__href__=",$orig);
	$onclick="onclick='parent.mailto(parent.substr(this.href,7));return false' ";
	$orig=array($orig,str_replace("'",'"',$orig),str_replace("'",'',$orig));
	$dest=array($onclick.$dest,$onclick.str_replace("'",'"',$dest),$onclick.str_replace("'",'',$dest));
	$temp=str_replace($orig,$dest,$temp);
	// REPLACE THE REST OF LINKS TO OPENWIN CALLS
	$orig="href='";
	$dest=str_replace("href=","__href__=",$orig);
	$onclick="onclick='parent.openwin(this.href);return false' ";
	$orig=array($orig,str_replace("'",'"',$orig),str_replace("'",'',$orig));
	$dest=array($onclick.$dest,$onclick.str_replace("'",'"',$dest),$onclick.str_replace("'",'',$dest));
	$temp=str_replace($orig,$dest,$temp);
	// RESTORE THE __HREF__= TO HREF=
	$temp=str_replace("__href__=","href=",$temp);
	return $temp;
}

// REMOVE ALL SCRIPT TAGS
function remove_script_tag($temp) {
	$temp=preg_replace("@<script[^>]*?.*?</script>@siu","",$temp);
	return $temp;
}

function remove_style_tag($temp) {
	$temp=preg_replace("@<style[^>]*?.*?</style>@siu","",$temp);
	return $temp;
}

function multi_explode($del,$str) {
	$del0=substr($del,0,1);
	$del1=substr($del,1);
	$ret0=explode($del0,$str);
	if($del1!="") {
		$ret2=array();
		foreach($ret0 as $ret1) $ret2=array_merge($ret2,multi_explode($del1,$ret1));
		$ret0=$ret2;
	}
	return $ret0;
}

function highlight_geshi($html,$lang="") {
	include_once("lib/geshi/geshi.php");
	if($lang=="") {
		static $open1="<pre>\n<code";
		static $open2=">";
		static $close="</code></pre>";
		$lenopen1=strlen($open1);
		$lenopen2=strlen($open2);
		$lenclose=strlen($close);
		$pos1=strpos($html,$open1);
		while($pos1!==false) {
			$pos2=strpos($html,$open2,$pos1+$lenopen1);
			$lang=substr($html,$pos1+$lenopen1,$pos2-$pos1-$lenopen1);
			$lang=trim($lang);
			$lang=str_replace(array('class="language-','"'),"",$lang);
			if($lang=="") $lang="text";
			$pos3=strpos($html,$close,$pos2);
			$html2=substr($html,$pos2+$lenopen2,$pos3-$pos2-$lenopen2);
			$html2=html_entity_decode($html2,ENT_COMPAT,"UTF-8");
			$geshi=new GeSHi($html2,$lang);
			$html3=$geshi->parse_code();
			$html=substr_replace($html,$html3,$pos1,$pos3+$lenclose-$pos1);
			$pos1=strpos($html,$open1,$pos3);
		}
	} else {
		$geshi=new GeSHi($html,$lang);
		$html=$geshi->parse_code();
	}
	return $html;
}

function is_array_key_val($array) {
	$count=0;
	foreach($array as $key=>$val) {
		if(!is_numeric($key)) return true;
		if($key!=$count) return true;
		$count++;
	}
	return false;
}

function words_exists($words,$buffer) {
	if(!is_array($words)) $words=explode(" ",$words);
	foreach($words as $word) if(stripos($buffer,$word)===false) return false;
	return true;
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
