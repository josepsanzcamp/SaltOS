<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2017 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="voice") {
	if(!eval_bool(getDefault("enablevoice"))) die();
	require_once("php/libaction.php");
	// SOME CHECKS
	if(!check_commands(array(getDefault("commands/text2wave"),getDefault("commands/wavetomp3")),60)) die();
	// NORMAL OPERATION
	$text=getParam("text");
	$dirhash=get_directory("dirs/cachedir").md5(json_encode(array("voice",$text)));
	$cache=$dirhash.".mp3";
	//if(file_exists($cache)) unlink($cache);
	if(!file_exists($cache)) {
		// CONVERT THE TEXT 2 VOICE IN WAV FORMAT
		$textcache=$dirhash.".txt";
		file_put_contents($textcache,utf8_decode($text));
		$wavcache=$dirhash.".wav";
		// DETECT THE LANG TO USE THE APPROPRIATE COMMAND/__TEXT2WAVE_XX__
		$langs=__translate_detect_aspell_langs($text);
		if(getDefault("commands/__text2wave_${langs[0]}__")) ob_passthru(getDefault("commands/text2wave")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($textcache,$wavcache),getDefault("commands/__text2wave_${langs[0]}__")));
		// CONTINUE WITH DEFAULT COMMAND/__TEXT2WAVE__ IF NEEDED
		if(!file_exists($wavcache)) ob_passthru(getDefault("commands/text2wave")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($textcache,$wavcache),getDefault("commands/__text2wave__")));
		unlink($textcache);
		if(!file_exists($wavcache)) die();
		// CONVERT THE WAV TO MP3 FORMAT
		ob_passthru(getDefault("commands/wavetomp3")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($wavcache,$cache),getDefault("commands/__wavetomp3__")));
		unlink($wavcache);
		if(!file_exists($cache)) die();
		chmod_protected($cache,0666);
	}
	output_handler(array(
		"file"=>$cache,
		"cache"=>true
	));
}
?>