<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2019 by Josep Sanz Campderrós
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
if(getParam("action")=="purge") {
	if(!eval_bool(getDefault("enablepurge"))) die();
	// CHECK THE SEMAPHORE
	if(!semaphore_acquire(getParam("action"),getDefault("semaphoretimeout",100000))) die();
	// GET THE PURGE CONFIGURATION
	$rows=xml2array("xml/purge.xml");
	$fields=array(
		"id_aplicacion",
		"id_usuario",
		"id_cuenta",
		"id_feed",
		"days"
    );
	if(!is_array($rows)) $rows=array();
	$total=0;
	foreach($rows as $row) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
		// SOME CHECKS
		if(!is_array($row)) $row=array();
		foreach($fields as $field) if(!isset($row[$field])) $row[$field]="";
		if($row["id_aplicacion"]) {
			if(!is_numeric($row["id_aplicacion"]) && !page2id($row["id_aplicacion"])) show_php_error(array("phperror"=>"id_aplicacion ".$row["id_aplicacion"]." not found"));
			if(is_numeric($row["id_aplicacion"]) && !id2page($row["id_aplicacion"])) show_php_error(array("phperror"=>"id_aplicacion ".$row["id_aplicacion"]." not found"));
			if(!is_numeric($row["id_aplicacion"])) $row["id_aplicacion"]=page2id($row["id_aplicacion"]);
		}
		if($row["id_usuario"]) {
			if(!is_numeric($row["id_usuario"]) && !user2id($row["id_usuario"])) show_php_error(array("phperror"=>"id_usuario ".$row["id_usuario"]." not found"));
			if(is_numeric($row["id_usuario"]) && !id2user($row["id_usuario"])) show_php_error(array("phperror"=>"id_usuario ".$row["id_usuario"]." not found"));
			if(!is_numeric($row["id_usuario"])) $row["id_usuario"]=user2id($row["id_usuario"]);
		}
		if($row["id_cuenta"]) {
			if($row["id_aplicacion"]!=page2id("correo")) show_php_error(array("phperror"=>"id_cuenta only can be used for correo application"));
			if(!$row["id_usuario"]) show_php_error(array("phperror"=>"id_cuenta need id_usuario too"));
			if(!is_numeric($row["id_cuenta"])) {
				$temp=execute_query("SELECT id FROM tbl_usuarios_c WHERE id_usuario='".$row["id_usuario"]."' AND email_from='".$row["id_cuenta"]."'");
				if(is_array($temp)) show_php_error(array("phperror"=>"id_cuenta ".$row["id_cuenta"]." found multiples times"));
				if(is_null($temp)) show_php_error(array("phperror"=>"id_cuenta ".$row["id_cuenta"]." not found"));
				$row["id_cuenta"]=$temp;
			}
			if(!execute_query("SELECT id FROM tbl_usuarios_c WHERE id='".$row["id_cuenta"]."'")) show_php_error(array("phperror"=>"id_cuenta ".$row["id_cuenta"]." not found"));
		}
		if($row["id_feed"]) {
			if($row["id_aplicacion"]!=page2id("feeds")) show_php_error(array("phperror"=>"id_feed only can be used for feeds application"));
			if(!$row["id_usuario"]) show_php_error(array("phperror"=>"id_feed need id_usuario too"));
			if(!is_numeric($row["id_feed"])) {
				$temp=execute_query("SELECT id FROM tbl_usuarios_f WHERE id_usuario='".$row["id_usuario"]."' AND title='".$row["id_feed"]."'");
				if(is_array($temp)) show_php_error(array("phperror"=>"id_feed ".$row["id_feed"]." found multiples times"));
				if(is_null($temp)) show_php_error(array("phperror"=>"id_feed ".$row["id_feed"]." not found"));
				$row["id_feed"]=$temp;
			}
			if(!execute_query("SELECT id FROM tbl_usuarios_f WHERE id='".$row["id_feed"]."'")) show_php_error(array("phperror"=>"id_feed ".$row["id_feed"]." not found"));
		}
		if(!$row["days"]) show_php_error(array("phperror"=>"days needed by purge action"));
		//~ echo "<pre>".sprintr($row)."</pre>";
		//~ continue;
		// CONTINUE
		for(;;) {
			if(time_get_usage()>getDefault("server/percentstop")) break;
			$query="
				SELECT a.id_aplicacion,a.id_registro,a.id_usuario,IFNULL(MAX(b.datetime),a.datetime) datetime2
				FROM tbl_registros a
				LEFT JOIN tbl_registros b ON a.id_aplicacion=b.id_aplicacion AND a.id_registro=b.id_registro AND b.first=0
				WHERE
					a.first=1 AND
					(a.id_aplicacion='".$row["id_aplicacion"]."' OR ''='".$row["id_aplicacion"]."') AND
					(a.id_usuario='${row["id_usuario"]}' OR ''='${row["id_usuario"]}') AND
					((a.id_aplicacion='".page2id("correo")."' AND a.id_registro IN (SELECT id FROM tbl_correo c WHERE c.id_cuenta='${row["id_cuenta"]}')) OR ''='${row["id_cuenta"]}') AND
					((a.id_aplicacion='".page2id("feeds")."' AND a.id_registro IN (SELECT id FROM tbl_feeds d WHERE d.id_feed='${row["id_feed"]}')) OR ''='${row["id_feed"]}')
				GROUP BY id_aplicacion,id_registro
				HAVING
					(datetime2<'".current_datetime(-$row["days"]*86400)."')
				LIMIT 1000";
			$rows2=execute_query_array($query);
			//~ echo "<pre>".sprintr($query)."</pre>";
			//~ echo "<pre>".sprintr($rows2)."</pre>";
			//~ die();
			if(!count($rows2)) break;
			foreach($rows2 as $row2) {
				if(time_get_usage()>getDefault("server/percentstop")) break;
				// BORRAR FICHEROS DEL SISTEMA DE FICHEROS
				if($row2["id_aplicacion"]==page2id("correo")) {
					$query="
					SELECT CONCAT('".get_directory("dirs/inboxdir")."',id_cuenta,'/',uidl,'.eml.gz') action_delete FROM tbl_correo WHERE id='${row2["id_registro"]}' AND is_outbox='0'
					UNION
					SELECT CONCAT('".get_directory("dirs/outboxdir")."',id_cuenta,'/',uidl,'.eml.gz') action_delete FROM tbl_correo WHERE id='${row2["id_registro"]}' AND is_outbox='1'
					UNION
					SELECT CONCAT('".get_directory("dirs/outboxdir")."',id_cuenta,'/',uidl,'.obj') action_delete FROM tbl_correo WHERE id='${row2["id_registro"]}' AND is_outbox='1'";
					$rows3=execute_query_array($query);
					foreach($rows3 as $delete) if(file_exists($delete) && is_file($delete)) unlink_protected($delete);
				}
				// BORRAR DATOS DE LA TABLA PRINCIPAL
				$tabla=id2table($row2["id_aplicacion"]);
				$query="DELETE FROM $tabla WHERE id='".$row2["id_registro"]."'";
				db_query($query);
				// BORRAR DATOS DE LAS SUBTABLAS
				$subtablas=id2subtables($row2["id_aplicacion"]);
				if($subtablas!="") {
					foreach(explode(",",$subtablas) as $subtabla) {
						$tabla=strtok($subtabla,"(");
						$campo=strtok(")");
						$query="DELETE FROM $tabla WHERE ${campo}='".$row2["id_registro"]."'";
						db_query($query);
					}
				}
				// BORRAR FICHEROS DEL SISTEMA DE FICHEROS
				if($row2["id_aplicacion"]!=page2id("correo")) {
					$query="SELECT CONCAT('".get_directory("dirs/filesdir")."',fichero_file) action_delete FROM tbl_ficheros WHERE id_aplicacion='".$row2["id_aplicacion"]."' AND id_registro='".$row2["id_registro"]."'";
					$rows3=execute_query_array($query);
					foreach($rows3 as $delete) if(file_exists($delete) && is_file($delete)) unlink_protected($delete);
				}
				// BORRAR DATOS DE LAS TABLAS GENERICAS
				$tablas=array("tbl_ficheros","tbl_comentarios","tbl_registros");
				foreach($tablas as $tabla) {
					$query="DELETE FROM $tabla WHERE id_aplicacion='".$row2["id_aplicacion"]."' AND id_registro='".$row2["id_registro"]."'";
					db_query($query);
				}
				// BORRAR DATOS DE LAS TABLAS INDEXACION
				$page=id2page($row2["id_aplicacion"]);
				$query="DELETE FROM idx_${page} WHERE id='".$row2["id_registro"]."'";
				db_query($query);
				// CONTINUE
				$total++;
			}
		}
	}
	// SEND RESPONSE
	if($total) javascript_alert($total.LANG("msgregisterspurged".min($total,2)));
	// RELEASE SEMAPHORE
	semaphore_release(getParam("action"));
	javascript_headers();
	die();
}

?>