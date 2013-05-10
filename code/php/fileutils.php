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
function saltos_content_type($file) {
	static $mimes=array(
		"css"=>"text/css",
		"js"=>"text/javascript",
		"xml"=>"text/xml",
		"htm"=>"text/html",
		"png"=>"image/png",
		"bmp"=>"image/bmp"
	);
	$ext=strtolower(extension($file));
	if(isset($mimes[$ext])) return $mimes[$ext];
	if(function_exists("mime_content_type")) return mime_content_type($file);
	if(function_exists("finfo_file")) return finfo_file(finfo_open(FILEINFO_MIME_TYPE),$file);
	return "application/octet-stream";
}

function get_directory($key,$default="") {
	$default=$default?$default:getcwd()."/cache";
	$dir=getDefault($key,$default);
	$bar=(substr($dir,-1,1)!="/")?"/":"";
	return $dir.$bar;
}

function get_temp_file($ext="") {
	if($ext=="") $ext=getDefault("exts/defaultext",".dat");
	if($ext[0]!=".") $ext=".".$ext;
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
	if($ext=="") $ext=getDefault("exts/defaultext",".dat");
	if(substr($ext,0,1)!=".") $ext=".".$ext;
	$dir=get_directory("dirs/cachedir");
	$file=$dir.md5($data).$ext;
	return $file;
}

function __addlog_helper($a) {
	return current_datetime_decimals().": ".$a;
}

function addlog($msg,$file="") {
	if(!$file) $file=getDefault("debug/logfile","saltos.log");
	$dir=get_directory("dirs/filesdir",getcwd()."/files");
	$maxlines=intval(getDefault("debug/maxlines",1000));
	if($maxlines>0 && file_exists($dir.$file) && memory_get_free()>filesize($dir.$file)) {
		capture_next_error();
		$numlines=count(file($dir.$file));
		$error=get_clear_error();
		if(!$error && $numlines>$maxlines) {
			$next=1;
			while(file_exists($dir.$file.".".$next)) $next++;
			capture_next_error();
			rename($dir.$file,$dir.$file.".".$next);
			get_clear_error();
		}
	}
	$msg=trim($msg);
	$msg=explode("\n",$msg);
	if(count($msg)==0) $msg=array("");
	$msg=array_map("__addlog_helper",$msg);
	$msg=implode("\n",$msg)."\n";
	file_put_contents($dir.$file,$msg,FILE_APPEND);
	if(memory_get_free()>0) chmod_protected($dir.$file,0666);
}

function semaphore_acquire($file,$timeout=100000) {
	return __semaphore_helper(__FUNCTION__,$file,$timeout);
}

function semaphore_release($file) {
	return __semaphore_helper(__FUNCTION__,$file,null);
}

function semaphore_shutdown() {
	return __semaphore_helper(__FUNCTION__,null,null);
}

function __semaphore_helper($fn,$file,$timeout) {
	static $stack=array();
	if(stripos($fn,"acquire")!==false) {
		$hash=md5($file);
		if(!isset($stack[$hash])) $stack[$hash]=null;
		if($stack[$hash]) return false;
		init_random();
		while($timeout>=0) {
			capture_next_error();
			$stack[$hash]=fopen($file,"a");
			get_clear_error();
			if($stack[$hash]) break;
			$timeout-=usleep_protected(rand(0,1000));
		}
		if($timeout<0) {
			return false;
		}
		chmod_protected($file,0666);
		touch_protected($file);
		while($timeout>=0) {
			capture_next_error();
			$result=flock($stack[$hash],LOCK_EX|LOCK_NB);
			get_clear_error();
			if($result) break;
			$timeout-=usleep_protected(rand(0,1000));
		}
		if($timeout<0) {
			if($stack[$hash]) {
				capture_next_error();
				fclose($stack[$hash]);
				get_clear_error();
				$stack[$hash]=null;
			}
			return false;
		}
		ftruncate($stack[$hash],0);
		fwrite($stack[$hash],getmypid());
		return true;
	} elseif(stripos($fn,"release")!==false) {
		$hash=md5($file);
		if(!isset($stack[$hash])) $stack[$hash]=null;
		if(!$stack[$hash]) return false;
		capture_next_error();
		flock($stack[$hash],LOCK_UN);
		get_clear_error();
		capture_next_error();
		fclose($stack[$hash]);
		get_clear_error();
		$stack[$hash]=null;
		return true;
	} elseif(stripos($fn,"shutdown")!==false) {
		foreach($stack as $hash=>$val) {
			if($stack[$hash]) {
				capture_next_error();
				flock($stack[$hash],LOCK_UN);
				get_clear_error();
				capture_next_error();
				fclose($stack[$hash]);
				get_clear_error();
				$stack[$hash]=null;
			}
		}
		return true;
	}
	return false;
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

function ob_passthru($cmd,$expires=0) {
	static $disableds_string=null;
	static $disableds_array=array();
	if($expires) {
		$cache=get_cache_file($cmd,getDefault("exts/outputext",".out"));
		list($mtime,$error)=filemtime_protected($cache);
		if(file_exists($cache) && !$error && time()-$expires<$mtime) return file_get_contents($cache);
	}
	if($disableds_string===null) {
		$disableds_string=ini_get("disable_functions");
		$disableds_array=$disableds_string?explode(",",$disableds_string):array();
		foreach($disableds_array as $key=>$val) $disableds_array[$key]=strtolower(trim($val));
	}
	if(!in_array("passthru",$disableds_array)) {
		ob_start();
		passthru($cmd);
		$buffer=ob_get_clean();
	} elseif(!in_array("system",$disableds_array)) {
		ob_start();
		system($cmd);
		$buffer=ob_get_clean();
	} elseif(!in_array("exec",$disableds_array)) {
		$buffer=array();
		exec($cmd,$buffer);
		$buffer=implode("\n",$buffer);
	} elseif(!in_array("shell_exec",$disableds_array)) {
		ob_start();
		$buffer=shell_exec($cmd);
		ob_get_clean();
	} else {
		//~ show_php_error(array("phperror"=>"Unknown command shell","details"=>"ini_get(disable_functions)=${disableds_string}"));
		$buffer="";
	}
	if($expires) {
		file_put_contents($cache,$buffer);
		chmod_protected($cache,0666);
	}
	return $buffer;
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

function check_commands($commands,$expires=0) {
	if(!is_array($commands)) $commands=explode(",",$commands);
	$result=1;
	foreach($commands as $command) $result&=ob_passthru(getDefault("commands/which")." ".str_replace(array("__INPUT__"),array($command),getDefault("commands/__which__")),$expires)?1:0;
	return $result;
}

function url_get_contents($url,$type="GET") {
	// PREPARE ARRAY
	$scheme=parse_url($url,PHP_URL_SCHEME);
	if(!$scheme) $url="http://".$url;
	$array=parse_url($url);
	$scheme=$array["scheme"];
	$ports=array("http"=>80,"https"=>443);
	if(!isset($ports[$scheme])) {
		show_php_error(array("phperror"=>"Unknown scheme '$scheme'"));
		return false;
	}
	$port=isset($array["port"])?$array["port"]:$ports[$scheme];
	$host_socket=($scheme=="https"?"ssl://":"").$array["host"];
	$host_port=$array["host"].(in_array($port,$ports)?"":":$port");
	$path=isset($array["path"])?$array["path"]:"/";
	$query_get=isset($array["query"])?"?".$array["query"]:"";
	$query_post=isset($array["query"])?$array["query"]:"";
	$type=strtoupper($type);
	if(!in_array($type,array("GET","POST"))) {
		show_php_error(array("phperror"=>"Unknown type '$type'"));
		return false;
	}
	// OPEN THE SOCKET
	$fp=fsockopen($host_socket,$port);
	if(!$fp) {
		show_php_error(array("phperror"=>"Could not open the socket"));
		return false;
	}
	// SEND REQUEST
	fputs($fp,"${type} ${path}${query_get} HTTP/1.1\r\n");
	fputs($fp,"Host: ${host_port}\r\n");
	if($type=="POST") fputs($fp,"Content-type: application/x-www-form-urlencoded\r\n");
	if($type=="POST") fputs($fp,"Content-length: ".strlen($query_post)."\r\n");
	fputs($fp,"User-Agent: ".get_name_version_revision()."\r\n");
	fputs($fp,"Referer: ".get_base()."\r\n");
	fputs($fp,"Connection: close\r\n\r\n");
	if($type=="POST") fputs($fp,$query_post);
	// READ RESPONSE
	$result="";
	while(!feof($fp)) $result.=fgets($fp,8192);
	// CLOSE SOCKET
	fclose($fp);
	// PREPARE RESPONSE
	$result=explode("\r\n\r\n",$result,2);
	$headers=isset($result[0])?$result[0]:"";
	$body=isset($result[1])?$result[1]:"";
	// CHECK FOR CHUNKED CONTENT
	$headers=explode("\n",$headers);
	foreach($headers as $header) {
		if(stripos($header,"location:")!==false && stripos($header,"-location:")===false) {
			$url2=trim(substr($header,strpos($header,":",stripos($header,"location:"))+1));
			$array2=parse_url($url2);
			if(!isset($array2["scheme"])) $array2["scheme"]=$array["scheme"];
			if(!isset($array2["host"])) $array2["host"]=$array["host"];
			if(!isset($array2["port"]) && isset($array["port"])) $array2["port"]=$array["port"];
			if(!isset($array2["path"])) {
				$array2["path"]="/";
			} elseif(substr($array2["path"],0,1)!="/") {
				if(isset($array["path"])) $array2["path"]=$array["path"]."/".$array2["path"];
				if(!isset($array["path"])) $array2["path"]="/".$array2["path"];
			}
			require_once("lib/wordpress/http_build_url.php");
			$url2=http_build_url($url2,$array2);
			$body=url_get_contents($url2,$type);
			$headers=array();
			break;
		}
	}
	foreach($headers as $header) {
		if(stripos($header,"404 Not Found")!==false) {
			$body="";
			$headers=array();
			break;
		}
	}
	foreach($headers as $header) {
		if(stripos($header,"transfer-encoding:")!==false && stripos($header,"chunked")!==false) {
			$from=0;
			$newbody="";
			for(;;) {
				$pos=strpos($body,"\r\n",$from);
				if($pos===false) breaK;
				$chunked=hexdec(substr($body,$from,$pos-$from));
				$from=$pos+2;
				$newbody.=substr($body,$from,$chunked);
				$from+=$chunked+2;
				if($from>strlen($body)) break;
			}
			$body=$newbody;
			$headers=array();
			break;
		}
	}
	// RETURN RESPONSE
	return $body;
}

function extension($file) {
	return pathinfo($file,PATHINFO_EXTENSION);
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
?>