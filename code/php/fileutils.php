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
function content_type_from_extension($file) {
	static $mimes=array();
	if(!count($mimes)) {
		$mimes["ez"]="application/andrew-inset";
		$mimes["hqx"]="application/mac-binhex40";
		$mimes["cpt"]="application/mac-compactpro";
		$mimes["doc"]="application/msword";
		$mimes["bin"]="application/octet-stream";
		$mimes["dms"]="application/octet-stream";
		$mimes["lha"]="application/octet-stream";
		$mimes["lzh"]="application/octet-stream";
		$mimes["exe"]="application/octet-stream";
		$mimes["class"]="application/octet-stream";
		$mimes["so"]="application/octet-stream";
		$mimes["dll"]="application/octet-stream";
		$mimes["img"]="application/octet-stream";
		$mimes["iso"]="application/octet-stream";
		$mimes["oda"]="application/oda";
		$mimes["ogg"]="application/ogg";
		$mimes["pdf"]="application/pdf";
		$mimes["ai"]="application/postscript";
		$mimes["eps"]="application/postscript";
		$mimes["ps"]="application/postscript";
		$mimes["rtf"]="application/rtf";
		$mimes["smi"]="application/smil";
		$mimes["smil"]="application/smil";
		$mimes["fm"]="application/vnd.framemaker";
		$mimes["mif"]="application/vnd.mif";
		$mimes["xls"]="application/vnd.ms-excel";
		$mimes["ppt"]="application/vnd.ms-powerpoint";
		$mimes["odc"]="application/vnd.oasis.opendocument.chart";
		$mimes["odb"]="application/vnd.oasis.opendocument.database";
		$mimes["odf"]="application/vnd.oasis.opendocument.formula";
		$mimes["odg"]="application/vnd.oasis.opendocument.graphics";
		$mimes["otg"]="application/vnd.oasis.opendocument.graphics-template";
		$mimes["odi"]="application/vnd.oasis.opendocument.image";
		$mimes["odp"]="application/vnd.oasis.opendocument.presentation";
		$mimes["otp"]="application/vnd.oasis.opendocument.presentation-template";
		$mimes["ods"]="application/vnd.oasis.opendocument.spreadsheet";
		$mimes["ots"]="application/vnd.oasis.opendocument.spreadsheet-template";
		$mimes["odt"]="application/vnd.oasis.opendocument.text";
		$mimes["odm"]="application/vnd.oasis.opendocument.text-master";
		$mimes["ott"]="application/vnd.oasis.opendocument.text-template";
		$mimes["oth"]="application/vnd.oasis.opendocument.text-web";
		$mimes["sxw"]="application/vnd.sun.xml.writer";
		$mimes["stw"]="application/vnd.sun.xml.writer.template";
		$mimes["sxc"]="application/vnd.sun.xml.calc";
		$mimes["stc"]="application/vnd.sun.xml.calc.template";
		$mimes["sxd"]="application/vnd.sun.xml.draw";
		$mimes["std"]="application/vnd.sun.xml.draw.template";
		$mimes["sxi"]="application/vnd.sun.xml.impress";
		$mimes["sti"]="application/vnd.sun.xml.impress.template";
		$mimes["sxg"]="application/vnd.sun.xml.writer.global";
		$mimes["sxm"]="application/vnd.sun.xml.math";
		$mimes["sis"]="application/vnd.symbian.install";
		$mimes["wbxml"]="application/vnd.wap.wbxml";
		$mimes["wmlc"]="application/vnd.wap.wmlc";
		$mimes["wmlsc"]="application/vnd.wap.wmlscriptc";
		$mimes["bcpio"]="application/x-bcpio";
		$mimes["torrent"]="application/x-bittorrent";
		$mimes["bz2"]="application/x-bzip2";
		$mimes["vcd"]="application/x-cdlink";
		$mimes["pgn"]="application/x-chess-pgn";
		$mimes["cpio"]="application/x-cpio";
		$mimes["csh"]="application/x-csh";
		$mimes["dcr"]="application/x-director";
		$mimes["dir"]="application/x-director";
		$mimes["dxr"]="application/x-director";
		$mimes["dvi"]="application/x-dvi";
		$mimes["spl"]="application/x-futuresplash";
		$mimes["gtar"]="application/x-gtar";
		$mimes["gz"]="application/x-gzip";
		$mimes["tgz"]="application/x-gzip";
		$mimes["hdf"]="application/x-hdf";
		$mimes["jar"]="application/x-java-archive";
		$mimes["jnlp"]="application/x-java-jnlp-file";
		$mimes["js"]="application/x-javascript";
		$mimes["kwd"]="application/x-kword";
		$mimes["kwt"]="application/x-kword";
		$mimes["ksp"]="application/x-kspread";
		$mimes["kpr"]="application/x-kpresenter";
		$mimes["kpt"]="application/x-kpresenter";
		$mimes["chrt"]="application/x-kchart";
		$mimes["kil"]="application/x-killustrator";
		$mimes["skp"]="application/x-koan";
		$mimes["skd"]="application/x-koan";
		$mimes["skt"]="application/x-koan";
		$mimes["skm"]="application/x-koan";
		$mimes["latex"]="application/x-latex";
		$mimes["nc"]="application/x-netcdf";
		$mimes["cdf"]="application/x-netcdf";
		$mimes["pl"]="application/x-perl";
		$mimes["rpm"]="application/x-rpm";
		$mimes["sh"]="application/x-sh";
		$mimes["shar"]="application/x-shar";
		$mimes["swf"]="application/x-shockwave-flash";
		$mimes["sit"]="application/x-stuffit";
		$mimes["sv4cpio"]="application/x-sv4cpio";
		$mimes["sv4crc"]="application/x-sv4crc";
		$mimes["tar"]="application/x-tar";
		$mimes["tcl"]="application/x-tcl";
		$mimes["tex"]="application/x-tex";
		$mimes["texinfo"]="application/x-texinfo";
		$mimes["texi"]="application/x-texinfo";
		$mimes["t"]="application/x-troff";
		$mimes["tr"]="application/x-troff";
		$mimes["roff"]="application/x-troff";
		$mimes["man"]="application/x-troff-man";
		$mimes["1"]="application/x-troff-man";
		$mimes["2"]="application/x-troff-man";
		$mimes["3"]="application/x-troff-man";
		$mimes["4"]="application/x-troff-man";
		$mimes["5"]="application/x-troff-man";
		$mimes["6"]="application/x-troff-man";
		$mimes["7"]="application/x-troff-man";
		$mimes["8"]="application/x-troff-man";
		$mimes["me"]="application/x-troff-me";
		$mimes["ms"]="application/x-troff-ms";
		$mimes["ustar"]="application/x-ustar";
		$mimes["src"]="application/x-wais-source";
		$mimes["xhtml"]="application/xhtml+xml";
		$mimes["xht"]="application/xhtml+xml";
		$mimes["zip"]="application/zip";
		$mimes["au"]="audio/basic";
		$mimes["snd"]="audio/basic";
		$mimes["mid"]="audio/midi";
		$mimes["midi"]="audio/midi";
		$mimes["kar"]="audio/midi";
		$mimes["mpga"]="audio/mpeg";
		$mimes["mp2"]="audio/mpeg";
		$mimes["mp3"]="audio/mpeg";
		$mimes["aif"]="audio/x-aiff";
		$mimes["aiff"]="audio/x-aiff";
		$mimes["aifc"]="audio/x-aiff";
		$mimes["m3u"]="audio/x-mpegurl";
		$mimes["ram"]="audio/x-pn-realaudio";
		$mimes["rm"]="audio/x-pn-realaudio";
		$mimes["ra"]="audio/x-realaudio";
		$mimes["wav"]="audio/x-wav";
		$mimes["wma"]="audio/x-ms-wma";
		$mimes["wax"]="audio/x-ms-wax";
		$mimes["pdb"]="chemical/x-pdb";
		$mimes["xyz"]="chemical/x-xyz";
		$mimes["bmp"]="image/bmp";
		$mimes["gif"]="image/gif";
		$mimes["ief"]="image/ief";
		$mimes["jpeg"]="image/jpeg";
		$mimes["jpg"]="image/jpeg";
		$mimes["jpe"]="image/jpeg";
		$mimes["jfif"]="image/jpeg";
		$mimes["png"]="image/png";
		$mimes["tiff"]="image/tiff";
		$mimes["tif"]="image/tiff";
		$mimes["djvu"]="image/vnd.djvu";
		$mimes["djv"]="image/vnd.djvu";
		$mimes["ico"]="image/vnd.microsoft.icon";
		$mimes["wbmp"]="image/vnd.wap.wbmp";
		$mimes["ras"]="image/x-cmu-raster";
		$mimes["fts"]="image/x-fits";
		$mimes["pnm"]="image/x-portable-anymap";
		$mimes["pbm"]="image/x-portable-bitmap";
		$mimes["pgm"]="image/x-portable-graymap";
		$mimes["ppm"]="image/x-portable-pixmap";
		$mimes["rgb"]="image/x-rgb";
		$mimes["tga"]="image/x-targa";
		$mimes["xbm"]="image/x-xbitmap";
		$mimes["xpm"]="image/x-xpixmap";
		$mimes["xwd"]="image/x-xwindowdump";
		$mimes["art"]="message/news";
		$mimes["eml"]="message/rfc822";
		$mimes["mail"]="message/rfc822";
		$mimes["igs"]="model/iges";
		$mimes["iges"]="model/iges";
		$mimes["msh"]="model/mesh";
		$mimes["mesh"]="model/mesh";
		$mimes["silo"]="model/mesh";
		$mimes["wrl"]="model/vrml";
		$mimes["vrml"]="model/vrml";
		$mimes["css"]="text/css";
		$mimes["html"]="text/html";
		$mimes["htm"]="text/html";
		$mimes["asc"]="text/plain";
		$mimes["txt"]="text/plain";
		$mimes["text"]="text/plain";
		$mimes["pm"]="text/plain";
		$mimes["el"]="text/plain";
		$mimes["c"]="text/plain";
		$mimes["h"]="text/plain";
		$mimes["cc"]="text/plain";
		$mimes["hh"]="text/plain";
		$mimes["cxx"]="text/plain";
		$mimes["hxx"]="text/plain";
		$mimes["f90"]="text/plain";
		$mimes["rtx"]="text/richtext";
		$mimes["rtf"]="text/rtf";
		$mimes["sgml"]="text/sgml";
		$mimes["sgm"]="text/sgml";
		$mimes["tsv"]="text/tab-separated-values";
		$mimes["jad"]="text/vnd.sun.j2me.app-descriptor";
		$mimes["wml"]="text/vnd.wap.wml";
		$mimes["wmls"]="text/vnd.wap.wmlscript";
		$mimes["pod"]="text/x-pod";
		$mimes["etx"]="text/x-setext";
		$mimes["vcf"]="text/x-vcard";
		$mimes["xml"]="text/xml";
		$mimes["xsl"]="text/xml";
		$mimes["ent"]="text/xml-external-parsed-entity";
		$mimes["mpeg"]="video/mpeg";
		$mimes["mpg"]="video/mpeg";
		$mimes["mpe"]="video/mpeg";
		$mimes["qt"]="video/quicktime";
		$mimes["mov"]="video/quicktime";
		$mimes["mxu"]="video/vnd.mpegurl";
		$mimes["flv"]="video/x-flv";
		$mimes["asf"]="video/x-ms-asf";
		$mimes["asx"]="video/x-ms-asf";
		$mimes["wm"]="video/x-ms-wm";
		$mimes["wmv"]="video/x-ms-wmv";
		$mimes["wmx"]="video/x-ms-wmx";
		$mimes["wvx"]="video/x-ms-wvx";
		$mimes["avi"]="video/x-msvideo";
		$mimes["movie"]="video/x-sgi-movie";
		$mimes["ice"]="x-conference/x-cooltalk";
		$mimes["json"]="application/json";
	}
	$ext=pathinfo($file,PATHINFO_EXTENSION);
	$ext=strtolower($ext);
	if(isset($mimes[$ext])) return $mimes[$ext];
	return "application/octet-stream";
}

function image_type_from_extension($file) {
	static $mimes=array();
	if(!count($mimes)) {
		$mimes["bmp"]="bmp";
		$mimes["gif"]="gif";
		$mimes["jpeg"]="jpeg";
		$mimes["jpg"]="jpeg";
		$mimes["jpe"]="jpeg";
		$mimes["jfif"]="jpeg";
		$mimes["png"]="png";
		$mimes["tiff"]="tiff";
		$mimes["tif"]="tiff";
	}
	$ext=pathinfo($file,PATHINFO_EXTENSION);
	$ext=strtolower($ext);
	if(isset($mimes[$ext])) return $mimes[$ext];
	return "jpeg";
}

function get_directory($key,$default="") {
	$default=$default?$default:getcwd()."/cache";
	$dir=getDefault($key,$default);
	$bar=(substr($dir,-1,1)!="/")?"/":"";
	return $dir.$bar;
}

function get_temp_file($ext="") {
	if($ext=="") $ext=getDefault("exts/defaultext",".dat");
	if($ext[0]!=".") $ext=".${ext}";
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
	if($ext=="") $ext=pathinfo($data,PATHINFO_EXTENSION);
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
	global $_SEMAPHORE;
	if(!isset($_SEMAPHORE)) $_SEMAPHORE=array();
	$hash=md5($file);
	if(!isset($_SEMAPHORE[$hash])) $_SEMAPHORE[$hash]=null;
	init_random();
	while($timeout>=0) {
		if(!$_SEMAPHORE[$hash]) break;
		$usleep=rand(0,1000);
		usleep($usleep);
		$timeout-=$usleep;
	}
	if($timeout<0) {
		return false;
	}
	while($timeout>=0) {
		capture_next_error();
		$_SEMAPHORE[$hash]=fopen($file,"a");
		get_clear_error();
		if($_SEMAPHORE[$hash]) break;
		$usleep=rand(0,1000);
		usleep($usleep);
		$timeout-=$usleep;
	}
	if($timeout<0) {
		return false;
	}
	chmod_protected($file,0666);
	touch_protected($file);
	while($timeout>=0) {
		capture_next_error();
		$result=flock($_SEMAPHORE[$hash],LOCK_EX|LOCK_NB);
		get_clear_error();
		if($result) break;
		$usleep=rand(0,1000);
		usleep($usleep);
		$timeout-=$usleep;
	}
	if($timeout<0) {
		if($_SEMAPHORE[$hash]) {
			capture_next_error();
			fclose($_SEMAPHORE[$hash]);
			get_clear_error();
			$_SEMAPHORE[$hash]=null;
		}
		return false;
	}
	return true;
}

function semaphore_release($file) {
	global $_SEMAPHORE;
	$hash=md5($file);
	if($_SEMAPHORE[$hash]) {
		capture_next_error();
		flock($_SEMAPHORE[$hash],LOCK_UN);
		get_clear_error();
		capture_next_error();
		fclose($_SEMAPHORE[$hash]);
		get_clear_error();
		$_SEMAPHORE[$hash]=null;
	} else {
		return false;
	}
	return true;
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
	if(is_null($disableds_string)) {
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
		show_php_error(array("phperror"=>"Unknown schema '$schema'"));
		return false;
	}
	$port=isset($array["port"])?$array["port"]:$ports[$scheme];
	$host=$array["host"];
	$host1=($scheme=="https"?"ssl://":"").$host;
	$host2=$host.(in_array($port,$ports)?"":":$port");
	$path=isset($array["path"])?$array["path"]:"";
	$query=isset($array["query"])?$array["query"]:"";
	$type=strtoupper($type);
	if(!in_array($type,array("GET","POST"))) {
		show_php_error(array("phperror"=>"Unknown type '$type'"));
		return false;
	}
	// OPEN THE SOCKET
	$fp=fsockopen($host1,$port);
	if(!$fp) {
		show_php_error(array("phperror"=>"Could not open the socket"));
		return false;
	}
	// SEND REQUEST
	if($type=="GET" && $query!="") fputs($fp,"$type $path?$query HTTP/1.1\r\n");
	if($type=="GET" && $query=="") fputs($fp,"$type $path HTTP/1.1\r\n");
	if($type=="POST") fputs($fp,"$type $path HTTP/1.1\r\n");
	fputs($fp,"Host: $host2\r\n");
	if($type=="POST") fputs($fp,"Content-type: application/x-www-form-urlencoded\r\n");
	if($type=="POST") fputs($fp,"Content-length: ".strlen($query)."\r\n");
	fputs($fp,"User-Agent: ".get_name_version_revision()."\r\n");
	fputs($fp,"Connection: close\r\n\r\n");
	fputs($fp,$query);
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
		if(stripos($header,"location")!==false) {
			$pos=strpos($header,":");
			if($pos!==false) $body=url_get_contents(trim(substr($header,$pos+1)));
		}
		if(stripos($header,"chunked")!==false) {
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
		}
	}
	// RETURN RESPONSE
	return $body;
}
?>