<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2014 by Josep Sanz Campderrós
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
function history($page) {
	$id_usuario=current_user();
	if(!$id_usuario) return;
	$id_aplicacion=page2id($page);
	if(!$id_aplicacion) return;
	$numget=count(array_merge($_POST,$_GET));
	$limpiar=getParam("limpiar");
	if($numget>2 || $limpiar) {
		save_history($id_usuario,$id_aplicacion);
	} else {
		load_history($id_usuario,$id_aplicacion);
	}
}

function save_history($id_usuario,$id_aplicacion) {
	$query="SELECT * FROM tbl_history WHERE `id_usuario`='$id_usuario' AND `id_aplicacion`='$id_aplicacion'";
	$result=db_query($query);
	$numrows=db_num_rows($result);
	db_free($result);
	$querystring=base64_encode(str_replace("+","%20",getServer("QUERY_STRING")));
	if($numrows>1) {
		$query="DELETE FROM tbl_history WHERE `id_usuario`='$id_usuario' AND `id_aplicacion`='$id_aplicacion'";
		db_query($query);
		$numrows=0;
	}
	if(!$numrows) {
		$query="INSERT INTO tbl_history(`id`,`id_usuario`,`id_aplicacion`,`querystring`) VALUES(NULL,'$id_usuario','$id_aplicacion','$querystring')";
		db_query($query);
	} else {
		$query="UPDATE tbl_history SET `querystring`='$querystring' WHERE `id_usuario`='$id_usuario' AND `id_aplicacion`='$id_aplicacion'";
		db_query($query);
	}
}

function load_history($id_usuario,$id_aplicacion) {
	$query="SELECT querystring FROM tbl_history WHERE `id_usuario`='$id_usuario' AND `id_aplicacion`='$id_aplicacion'";
	$result=db_query($query);
	$numrows=db_num_rows($result);
	$row=db_fetch_row($result);
	db_free($result);
	if($numrows) {
		$items=querystring2array(base64_decode($row["querystring"]));
		if(isset($items[""])) unset($items[""]);
		if(isset($items["id_folder"])) unset($items["id_folder"]);
		if(isset($items["is_fichero"])) unset($items["is_fichero"]);
		if(isset($items["is_buscador"])) unset($items["is_buscador"]);
		$_POST=array_merge($_POST,$items);
	}
}

function lastpage($page) {
	$id_usuario=current_user();
	if(!$id_usuario) return "";
	$query="SELECT page FROM tbl_lastpage WHERE id_usuario='$id_usuario' LIMIT 1";
	$lastpage=execute_query($query);
	if(!$page) {
		$page=$lastpage;
	} else {
		if($page!=$lastpage) {
			if(!$lastpage) {
				$query="INSERT INTO tbl_lastpage(`id`,`id_usuario`,`page`) VALUES(NULL,'$id_usuario','$page')";
				db_query($query);
			} else {
				$query="UPDATE tbl_lastpage SET `page`='$page' WHERE `id_usuario`='$id_usuario'";
				db_query($query);
			}
		}
	}
	if(!$page) $page=getDefault("page");
	return $page;
}

function lastfolder($id_folder) {
	$id_usuario=current_user();
	if(!$id_usuario) return "";
	$query="SELECT id_folder FROM tbl_lastfolder WHERE id_usuario='$id_usuario' LIMIT 1";
	$lastfolder=execute_query($query);
	if(!$id_folder) {
		$id_folder=$lastfolder;
	} else {
		if($id_folder!=$lastfolder) {
			if(!$lastfolder) {
				$query="INSERT INTO tbl_lastfolder(`id`,`id_usuario`,`id_folder`) VALUES(NULL,'$id_usuario','$id_folder')";
				db_query($query);
			} else {
				$query="UPDATE tbl_lastfolder SET `id_folder`='$id_folder' WHERE `id_usuario`='$id_usuario'";
				db_query($query);
			}
		}
	}
	if(!$id_folder) {
		$query="SELECT id FROM tbl_folders WHERE id_usuario='$id_usuario' ORDER BY name ASC LIMIT 1";
		$id_folder=execute_query($query);
	}
	return $id_folder;
}
?>