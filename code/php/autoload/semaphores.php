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
		if(!isset($stack[$file])) $stack[$file]=null;
		if($stack[$file]) return false;
		init_random();
		while($timeout>=0) {
			capture_next_error();
			$stack[$file]=fopen($file,"a");
			get_clear_error();
			if($stack[$file]) break;
			$timeout-=__semaphore_usleep(rand(0,1000));
		}
		if($timeout<0) {
			return false;
		}
		chmod_protected($file,0666);
		touch_protected($file);
		while($timeout>=0) {
			capture_next_error();
			$result=flock($stack[$file],LOCK_EX|LOCK_NB);
			get_clear_error();
			if($result) break;
			$timeout-=__semaphore_usleep(rand(0,1000));
		}
		if($timeout<0) {
			if($stack[$file]) {
				capture_next_error();
				fclose($stack[$file]);
				get_clear_error();
				$stack[$file]=null;
			}
			return false;
		}
		ftruncate($stack[$file],0);
		fwrite($stack[$file],gettrace());
		return true;
	} elseif(stripos($fn,"release")!==false) {
		if($name=="") $name=__FUNCTION__;
		$file=get_cache_file($name,".sem");
		if(!isset($stack[$file])) $stack[$file]=null;
		if(!$stack[$file]) return false;
		capture_next_error();
		flock($stack[$file],LOCK_UN);
		get_clear_error();
		capture_next_error();
		fclose($stack[$file]);
		get_clear_error();
		capture_next_error();
		unlink($file);
		get_clear_error();
		$stack[$file]=null;
		return true;
	} elseif(stripos($fn,"shutdown")!==false) {
		foreach($stack as $file=>$val) {
			if($stack[$file]) {
				capture_next_error();
				flock($stack[$file],LOCK_UN);
				get_clear_error();
				capture_next_error();
				fclose($stack[$file]);
				get_clear_error();
				capture_next_error();
				unlink($file);
				get_clear_error();
				$stack[$file]=null;
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

function __semaphore_usleep($usec) {
	if(function_exists("socket_create")) {
		$socket=socket_create(AF_UNIX,SOCK_STREAM,0);
		$read=null;
		$write=null;
		$except=array($socket);
		capture_next_error();
		$time1=microtime(true);
		socket_select($read,$write,$except,intval($usec/1000000),intval($usec%1000000));
		$time2=microtime(true);
		get_clear_error();
		return ($time2-$time1)*1000000;
	}
	$time1=microtime(true);
	usleep($usec);
	$time2=microtime(true);
	return ($time2-$time1)*1000000;
}

?>