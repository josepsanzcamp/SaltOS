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

function get_directory($key,$default="") {
	$default=$default?$default:getcwd_protected()."/cache";
	$dir=getDefault($key,$default);
	$bar=(substr($dir,-1,1)!="/")?"/":"";
	return $dir.$bar;
}

function get_temp_file($ext="") {
	if($ext=="") $ext=".dat";
	if(substr($ext,0,1)!=".") $ext=".".$ext;
	$dir=get_directory("dirs/cachedir");
	while(1) {
		$uniqid=get_unique_id_md5();
		$file=$dir.$uniqid.$ext;
		if(!file_exists($file)) break;
	}
	return $file;
}

function cache_exists($cache,$file) {
	if(!file_exists($cache)) return 0;
	if(!is_array($file)) $file=array($file);
	foreach($file as $f) {
		if(!file_exists($f)) return 0;
		list($mtime1,$error1)=filemtime_protected($f);
		list($mtime2,$error2)=filemtime_protected($cache);
		if($error1 || $error2 || $mtime1>=$mtime2) return 0;
	}
	return 1;
}

function get_cache_file($data,$ext="") {
	if(is_array($data)) $data=serialize($data);
	if($ext=="") $ext=strtolower(extension($data));
	if($ext=="") $ext=".dat";
	if(substr($ext,0,1)!=".") $ext=".".$ext;
	$dir=get_directory("dirs/cachedir");
	$file=$dir.md5($data).$ext;
	return $file;
}

function semi_realpath($file) {
	$file=explode("/",$file);
	$count=count($file);
	for($i=1;$i<$count;$i++) {
		if($file[$i]=="..") {
			for($j=$i-1;$j>=0;$j--) {
				if(isset($file[$j]) && $file[$j]!="..") {
					unset($file[$i]);
					unset($file[$j]);
					break;
				}
			}
		}
	}
	$file=implode("/",$file);
	return $file;
}

function chmod_protected($file,$mode) {
	capture_next_error();
	ob_start();
	chmod($file,$mode);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return $error1.$error2;
}

function unlink_protected($file) {
	capture_next_error();
	ob_start();
	unlink($file);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return $error1.$error2;
}

function filemtime_protected($file) {
	capture_next_error();
	ob_start();
	$mtime=filemtime($file);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return array($mtime,$error1.$error2);
}

function touch_protected($file) {
	capture_next_error();
	ob_start();
	touch($file);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return $error1.$error2;
}

function mkdir_protected($dir) {
	capture_next_error();
	ob_start();
	mkdir($dir);
	$error1=ob_get_clean();
	$error2=get_clear_error();
	return $error1.$error2;
}

function url_get_contents($url) {
	// CHECK SCHEME
	$scheme=parse_url($url,PHP_URL_SCHEME);
	if(!$scheme) $url="http://".$url;
	// DO THE REQUEST
	list($body,$headers,$cookies)=__url_get_contents($url);
	// RETURN RESPONSE
	return $body;
}

function __url_get_contents($url,$method="",$values=array(),$cookies=array(),$referer="") {
	require_once("lib/phpclasses/httpclient/http.php");
	$http=new http_class;
	$http->user_agent=get_name_version_revision();
	$http->follow_redirect=1;
	if(count($cookies)) $http->RestoreCookies($cookies);
	$arguments=array();
	$error=$http->GetRequestArguments($url,$arguments);
	if($error!="") return array("",array(),array());
	$error=$http->Open($arguments);
	if($error!="") return array("",array(),array());
	if($method!="") $arguments["RequestMethod"]=strtoupper($method);
	if(count($values)) $arguments["PostValues"]=$values;
	if($referer!="") $arguments["Referer"]=$referer;
	$error=$http->SendRequest($arguments);
	if($error!="") return array("",array(),array());
	$headers=array();
	$error=$http->ReadReplyHeaders($headers);
	if($error!="") return array("",array(),array());
	$body="";
	$error=$http->ReadWholeReplyBody($body);
	if($error!="") return array("",array(),array());
	$http->Close();
	$cookies=array();
	$http->SaveCookies($cookies);
	return array($body,$headers,$cookies);
}

function extension($file) {
	return pathinfo($file,PATHINFO_EXTENSION);
}

function extension2($mime) {
	return saltos_content_type1($mime);
}

function getcwd_protected() {
	$dir=getcwd();
	if($dir=="/") $dir=dirname(getServer("SCRIPT_FILENAME"));
	return $dir;
}

// COPIED FROM http://php.net/manual/es/function.gzread.php#110078
function gzfilesize($filename) {
	$gzfs = FALSE;
	if(($zp = fopen($filename, 'r'))!==FALSE) {
		if(@fread($zp, 2) == "\x1F\x8B") { // this is a gzip'd file
			fseek($zp, -4, SEEK_END);
			if(strlen($datum = @fread($zp, 4))==4)
				extract(unpack('Vgzfs', $datum));
		}
		else // not a gzip'd file, revert to regular filesize function
			$gzfs = filesize($filename);
		fclose($zp);
	}
	return($gzfs);
}

function glob_protected($pattern) {
	$array=glob($pattern);
	return is_array($array)?$array:array();
}

function find_files($dir,$ext="") {
	$files=glob("${dir}/*");
	$result=array();
	foreach($files as $file) {
		if(is_dir($file)) {
			$result=array_merge($result,find_files($file));
		} elseif(is_file($file)) {
			if(!$ext || extension($file)==$ext) $result[]=$file;
		} else {
			show_php_error(array("phperror"=>"Unknown type of archive for '${file}'"));
		}
	}
	return $result;
}

function fix_file($file) {
	if(strpos($file," ")!==false) {
		$file2=get_cache_file($file);
		if(!file_exists($file2)) symlink(realpath($file),$file2);
		$file=$file2;
	}
	return $file;
}

function readfile_protected($file) {
	$fp=fopen($file,"rb");
	while(!feof($fp)) echo fread($fp,1048576);
	fclose($fp);
}

function fsockopen_protected($hostname,$port,&$errno=0,&$errstr="",$timeout=null) {
	if($timeout==null) $timeout=ini_get("default_socket_timeout");
	return stream_socket_client(
		$hostname.":".$port,
		$errno,
		$errstr,
		$timeout,
		STREAM_CLIENT_CONNECT,
		stream_context_create(
			array(
				"ssl"=>array(
					"verify_peer"=>false,
					"verify_peer_name"=>false,
					"allow_self_signed"=>true
				)
			)
		)
	);
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

?>