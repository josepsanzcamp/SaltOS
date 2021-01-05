<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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
if(getParam("action")=="translator") {
	if(!eval_bool(getDefault("enabletranslator"))) die();
	require_once("php/libaction.php");
	// CHECK COMMANDS
	if(!(check_commands(getDefault("commands/translate"),60) || check_commands(getDefault("commands/aspell"),60))) die();
	// GET PARAMETERS
	$text=getParam("text");
	$langs=getParam("langs");
	// CHECK FOR THE OPTIONS REQUEST
	if($langs=="auto") {
		$cache=get_cache_file(array("translator","auto",$text,__translator_get_langs(),__translator_get_aspell_langs()),".txt");
		//if(file_exists($cache)) unlink($cache);
		if(!file_exists($cache)) {
			$langs=__translator_get_aspell_langs();
			$options=__translator_get_options($langs);
			file_put_contents($cache,$options);
			chmod_protected($cache,0666);
		}
		output_handler(array(
			"file"=>$cache,
			"cache"=>true
		));
	}
	// SECURITY CHECKS
	$error=0;
	$langs=strtolower($langs);
	for($i=0;$i<strlen($langs);$i++) {
		$ok=0;
		if($langs[$i]>="a" && $langs[$i]<="z") $ok=1;
		if($langs[$i]=="-") $ok=1;
		if(!$ok) $error=1;
	}
	$langs_array=explode("-",$langs);
	if(!$error && count($langs_array)!=2) $error=1;
	if(!$error && strlen($langs_array[0])!=2) $error=1;
	if(!$error && strlen($langs_array[1])!=2) $error=1;
	if($error) action_denied();
	// CONTINUE
	$cache=get_cache_file(array("translator",$langs,$text,__translator_get_langs(),__translator_get_aspell_langs()),".txt");
	//if(file_exists($cache)) unlink($cache);
	if(!file_exists($cache)) {
		$text=__translator_aspell($text,$langs_array[0]);
		if($langs_array[0]!=$langs_array[1]) $text=__translator($text,$langs);
		file_put_contents($cache,$text);
		chmod_protected($cache,0666);
	}
	output_handler(array(
		"file"=>$cache,
		"cache"=>false
	));
}

?>