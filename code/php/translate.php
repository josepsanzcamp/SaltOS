<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2016 by Josep Sanz Campderrós
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
function __translate_get_aspell_langs() {
	if(!check_commands(getDefault("commands/aspell"),60)) return array();
	$langs=ob_passthru(getDefault("commands/aspell")." ".getDefault("commands/__aspell_langs__"),getDefault("default/commandtimeout",60));
	$langs=explode("\n",$langs);
	foreach($langs as $key=>$val) {
		$val=trim($val);
		$len=strlen($val);
		if($len==2) {
			$langs[$key]=$val;
		} else {
			unset($langs[$key]);
		}
	}
	return $langs;
}

function __translate_get_apertium_langs() {
	if(!check_commands(getDefault("commands/apertium"),60)) return array();
	$langs=ob_passthru(getDefault("commands/apertium")." ".getDefault("commands/__apertium_langs__"),getDefault("default/commandtimeout",60));
	$langs=explode("\n",$langs);
	foreach($langs as $key=>$val) {
		$val=trim($val);
		$len=strlen($val);
		if($len==5 && $val[2]=="-") {
			$langs[$key]=$val;
		} else {
			unset($langs[$key]);
		}
	}
	return $langs;
}

function __translate_detect_aspell_langs($text,$length=50) {
	if(!check_commands(getDefault("commands/aspell"),60)) return array();
	$words=str_word_count_utf8($text);
	$words=array_slice($words,0,$length);
	$text=implode(" ",$words);
	$input=get_temp_file(getDefault("exts/inputext",".in"));
	file_put_contents($input,$text);
	$langs=__translate_get_aspell_langs();
	$counts=array();
	foreach($langs as $lang) {
		$aspell=ob_passthru(getDefault("commands/aspell")." ".str_replace(array("__LANG__","__INPUT__"),array($lang,$input),getDefault("commands/__aspell__")));
		$counts[$lang]=substr_count($aspell,"*");
	}
	unlink($input);
	arsort($counts,SORT_NUMERIC);
	foreach($counts as $key=>$val) {
		if($val<max($counts)) unset($counts[$key]);
	}
	$langs=array_keys($counts);
	return $langs;
}

function __translate_aspell($text,$lang) {
	if(!check_commands(getDefault("commands/aspell"),60)) return $text;
	$input=get_temp_file(getDefault("exts/inputext",".in"));
	file_put_contents($input,$text);
	$aspell=ob_passthru(getDefault("commands/aspell")." ".str_replace(array("__LANG__","__INPUT__"),array($lang,$input),getDefault("commands/__aspell__")));
	unlink($input);
	$aspell=trim($aspell);
	$aspell=explode("\n",$aspell);
	$bias=0;
	while($bias<mb_strlen($text) && mb_substr($text,$bias,1)=="\n") $bias++;
	$offset=0;
	$suggest="";
	foreach($aspell as $line) {
		$token=strtok($line," ");
		if($token=="&") {
			$word=strtok(" ");
			if(strtoupper(mb_substr($word,0,1))==mb_substr($word,0,1)) continue;
			$number=strtok(" ");
			$offset=strtok(": ");
			$suggest=strtok(", ");
			$text=mb_substr($text,0,$offset+$bias).$suggest.mb_substr($text,$offset+$bias+mb_strlen($word),mb_strlen($text));
			$bias+=mb_strlen($suggest)-mb_strlen($word);
		}
		if($token=="") {
			$bias=$offset+$bias+mb_strlen($suggest);
			$bias=mb_strpos($text,"\n",$bias)+1;
			while($bias<mb_strlen($text) && mb_substr($text,$bias,1)=="\n") $bias++;
			$offset=0;
			$suggest="";
		}
	}
	return $text;
}

function __translate_apertium($text,$langs) {
	if(!check_commands(getDefault("commands/apertium"),60)) return $text;
	$input=get_temp_file(getDefault("exts/inputext",".in"));
	file_put_contents($input,$text);
	$text=ob_passthru(getDefault("commands/apertium")." ".str_replace(array("__LANGS__","__INPUT__"),array($langs,$input),getDefault("commands/__apertium__")));
	unlink($input);
	return $text;
}
?>