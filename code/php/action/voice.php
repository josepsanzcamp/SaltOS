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
if(!check_user()) action_denied();
if(getParam("action")=="voice") {
	require_once("php/translate.php");
	// SOME CHECKS
	if(!check_commands(array(getDefault("commands/text2wave"),getDefault("commands/ffmpeg")),60)) action_denied();
	// NORMAL OPERATION
	$text=getParam("text");
	$dirhash=get_directory("dirs/cachedir").md5(serialize(array("voice",$text)));
	$cache=$dirhash.getDefault("exts/mp3ext",".mp3");
	//if(file_exists($cache)) unlink($cache);
	if(!file_exists($cache)) {
		// DETECT THE LANG AND OVERWRITE THE COMMAND/__TEXT2WAVE__
		$langs=__translate_detect_aspell_langs(stripslashes($text));
		$__text2wave__="__text2wave__";
		if(getDefault("commands/__text2wave_${langs[0]}__")) $__text2wave__="__text2wave_${langs[0]}__";
		// CONVERT THE TEXT 2 VOICE IN WAV FORMAT
		$textcache=$dirhash.getDefault("exts/textext",".txt");
		file_put_contents($textcache,utf8_decode($text));
		$wavcache=$dirhash.getDefault("exts/wavext",".wav");
		ob_passthru(getDefault("commands/text2wave")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($textcache,$wavcache),getDefault("commands/${__text2wave__}")));
		unlink($textcache);
		if(!file_exists($wavcache)) action_denied();
		// CONVERT THE WAV TO MP3 FORMAT
		ob_passthru(getDefault("commands/ffmpeg")." ".str_replace(array("__INPUT__","__OUTPUT__"),array($wavcache,$cache),getDefault("commands/__ffmpeg__")));
		unlink($wavcache);
		if(!file_exists($cache)) action_denied();
		chmod_protected($cache,0666);
	}
	output_file($cache);
}
?>