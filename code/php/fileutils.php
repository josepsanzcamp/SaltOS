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
function saltos_content_type($file) {
	static $mimes=array(
		"css"=>"text/css",
		"js"=>"text/javascript",
		"xml"=>"text/xml",
		"htm"=>"text/html",
		"html"=>"text/html",
		"png"=>"image/png",
		"bmp"=>"image/bmp",
		"json"=>"application/json"
	);
	$ext=strtolower(extension($file));
	if(isset($mimes[$ext])) return $mimes[$ext];
	if(function_exists("mime_content_type")) return mime_content_type($file);
	if(function_exists("finfo_file")) return finfo_file(finfo_open(FILEINFO_MIME_TYPE),$file);
	return "application/octet-stream";
}

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
	if(is_array($data)) $data=json_encode($data);
	if($ext=="") $ext=strtolower(extension($data));
	if($ext=="") $ext=".dat";
	if(substr($ext,0,1)!=".") $ext=".".$ext;
	$dir=get_directory("dirs/cachedir");
	$file=$dir.md5($data).$ext;
	return $file;
}

function __addlog_helper($a) {
	return current_datetime_decimals().": ".$a;
}

function checklog($hash,$file="") {
	$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
	if(file_exists($dir.$file) && filesize($dir.$file)<memory_get_free(true)/3) {
		capture_next_error();
		$buffer=file_get_contents($dir.$file);
		$error=get_clear_error();
		if(!$error && strpos($buffer,$hash)!==false) return 1;
	}
	return 0;
}

function addlog($msg,$file="") {
	if(!$file) $file=getDefault("debug/logfile","saltos.log");
	$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
	$maxfilesize=normalize_value(getDefault("debug/maxfilesize","1M"));
	if($maxfilesize>0 && file_exists($dir.$file) && filesize($dir.$file)>=$maxfilesize) {
		$next=1;
		while(file_exists($dir.$file.".".$next)) $next++;
		capture_next_error();
		rename($dir.$file,$dir.$file.".".$next);
		get_clear_error();
	}
	$msg=trim($msg);
	$msg=explode("\n",$msg);
	$msg=array_map("__addlog_helper",$msg);
	$msg=implode("\n",$msg)."\n";
	file_put_contents($dir.$file,$msg,FILE_APPEND);
	chmod_protected($dir.$file,0666);
}

function semaphore_acquire($name="",$timeout=INF) {
	return __semaphore_helper(__FUNCTION__,$name,$timeout);
}

function semaphore_release($name="") {
	return __semaphore_helper(__FUNCTION__,$name,null);
}

function semaphore_shutdown() {
	return __semaphore_helper(__FUNCTION__,null,null);
}

function semaphore_file($name="") {
	return __semaphore_helper(__FUNCTION__,$name,null);
}

function __semaphore_helper($fn,$name,$timeout) {
	static $stack=array();
	if(stripos($fn,"acquire")!==false) {
		if($name=="") $name=__FUNCTION__;
		$file=get_cache_file($name,".sem");
		if(!is_writable(dirname($file))) return false;
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
		if($name=="") $name=__FUNCTION__;
		$file=get_cache_file($name,".sem");
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
	} elseif(stripos($fn,"file")!==false) {
		if($name=="") $name=__FUNCTION__;
		$file=get_cache_file($name,".sem");
		return $file;
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
		$cache=get_cache_file($cmd,".out");
		list($mtime,$error)=filemtime_protected($cache);
		if(file_exists($cache) && !$error && time()-$expires<$mtime) return file_get_contents($cache);
	}
	if(!semaphore_acquire(array(__FUNCTION__,$cmd))) show_php_error(array("phperror"=>"Could not acquire the semaphore"));
	if($disableds_string===null) {
		$disableds_string=ini_get("disable_functions").",".ini_get("suhosin.executor.func.blacklist");
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
	semaphore_release(array(__FUNCTION__,$cmd));
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
	foreach($commands as $command) $result&=ob_passthru(getDefault("commands/which","which")." ".str_replace(array("__INPUT__"),array($command),getDefault("commands/__which__","__INPUT__")),$expires)?1:0;
	return $result;
}

function url_get_contents($url) {
	// CHECK SCHEME
	$scheme=parse_url($url,PHP_URL_SCHEME);
	if(!$scheme) $url="http://".$url;
	// DO THE REQUEST
	require_once("lib/phpclasses/httpclient/http.php");
	$http=new http_class;
	$http->user_agent=get_name_version_revision();
	$http->follow_redirect=1;
	$arguments=array();
	$error=$http->GetRequestArguments($url,$arguments);
	if($error!="") return "";
	$error=$http->Open($arguments);
	if($error!="") return "";
	$error=$http->SendRequest($arguments);
	if($error!="") return "";
	$headers=array();
	$error=$http->ReadReplyHeaders($headers);
	if($error!="") return "";
	$body="";
	$error=$http->ReadWholeReplyBody($body);
	if($error!="") return "";
	$http->Close();
	// RETURN RESPONSE
	return $body;
}

function extension($file) {
	return pathinfo($file,PATHINFO_EXTENSION);
}

function extension2($mime) {
	return saltos_content_type1($mime);
}

function saltos_content_type0($mime) {
	$mime=explode("/",$mime);
	return array_shift($mime);
}

function saltos_content_type1($mime) {
	$mime=explode("/",$mime);
	return array_pop($mime);
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
?>