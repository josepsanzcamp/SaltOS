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
// FUNCTION THAT RETURNS THE META ATTRIBUTES
function __explode_meta($html) {
	$result=array();
	$len=strlen($html);
	$pos1=strpos($html,"=");
	while($pos1!==false) {
		for($i=$pos1-1;$i>=0;$i--) if($html[$i]!=" ") break;
		for($j=$i;$j>=0;$j--) if($html[$j]==" ") break;
		$pos2=$j;
		for($i=$pos1+1;$i<$len;$i++) if($html[$i]!=" ") break;
		for($j=$i;$j<$len;$j++) if($html[$j]=='"' || $html[$j]=="'") break;
		$pos3=$j;
		for($k=$j+1;$k<$len;$k++) if($html[$j]==$html[$k]) break;
		$pos4=$k;
		$key=substr($html,$pos2+1,$pos1-$pos2-1);
		$val=substr($html,$pos3+1,$pos4-$pos3-1);
		$result[$key]=$val;
		$pos1=strpos($html,"=",$pos1+1);
	}
	return $result;
}
// FUNCTION THAT RETURNS ALL META TAGS
function __get_metas($html) {
	$result=array();
	$pos1=stripos($html,"<meta");
	while($pos1!==false) {
		$pos2=stripos($html,">",$pos1);
		if($pos2===false) break;
		$result[]=__explode_meta(substr($html,$pos1,$pos2-$pos1+1));
		$pos1=stripos($html,"<meta",$pos2);
	}
	return $result;
}
// CONTINUE
if(getParam("action")=="favoritos") {
	$url=getParam("url");
	$scheme=parse_url($url,PHP_URL_SCHEME);
	if(!$scheme) $url="http://".$url;
	if(substr($url,-1,1)=="/") $url=substr($url,0,-1);
	$query="SELECT id FROM tbl_favoritos WHERE url='${url}'";
	$existe=execute_query($query);
	if($existe===null) {
		capture_next_error();
		$html=url_get_contents($url);
		$error=get_clear_error();
		if(!$error && $html!="") {
			require_once("php/getmail.php");
			// NOMBRE EN TAG TITLE
			$nombre=$url;
			$pos1=stripos($html,"<title>");
			if($pos1!==false) $pos1=strpos($html,">",$pos1);
			$pos2=stripos($html,"</title>");
			if($pos1!==false && $pos2!==false) $nombre=substr($html,$pos1+1,$pos2-$pos1-1);
			// NOMBRE Y DESCRIPCION EN TAGS META
			$descripcion=$url;
			$metas=__get_metas($html);
			foreach($metas as $meta) {
				if(isset($meta["name"]) && $meta["name"]=="description" && isset($meta["content"])) $descripcion=$meta["content"];
				if(isset($meta["property"]) && $meta["property"]=="og:description" && isset($meta["content"])) $descripcion=$meta["content"];
				if(isset($meta["property"]) && $meta["property"]=="og:title" && isset($meta["content"])) $nombre=$meta["content"];
			}
			// INSERT EN TBL_FAVORITOS
			$nombre=addslashes(html_entity_decode(__getmail_getutf8($nombre),ENT_COMPAT,"UTF-8"));
			$descripcion=addslashes(html_entity_decode(__getmail_getutf8($descripcion),ENT_COMPAT,"UTF-8"));
			$query="INSERT INTO tbl_favoritos(`id`,`url`,`nombre`,`descripcion`) VALUES(NULL,'${url}','${nombre}','${descripcion}')";
			db_query($query);
			// INSERT EN TBL_REGISTROS_I
			$id_aplicacion=page2id("favoritos");
			$query="SELECT MAX(id) FROM tbl_favoritos";
			$last_id=execute_query($query);
			$id_usuario=current_user();
			$datetime=current_datetime();
			$query="INSERT INTO tbl_registros_i(`id`,`id_aplicacion`,`id_registro`,`id_usuario`,`datetime`) VALUES(NULL,'${id_aplicacion}','${last_id}','${id_usuario}','${datetime}')";
			db_query($query);
			// RETURN
			javascript_history(0);
		} else {
			// invalid url avisar
			javascript_alert(LANG("invalid_url","favoritos").getParam("url"));
		}
	} else {
		// ya existe avisar
		javascript_alert(LANG("bookmark_ya_existe","favoritos"));
	}
	die();
}
?>