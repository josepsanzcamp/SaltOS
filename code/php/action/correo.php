<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
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
if($page=="correo") {
	$ids=check_ids(getParam("id"));
	if($ids) {
		$numids=count(explode(",",$ids));
		$query="SELECT id FROM tbl_correo a WHERE id IN ($ids) AND id IN (SELECT id_registro FROM tbl_registros_i WHERE id_aplicacion='".page2id("correo")."' AND id_registro=a.id AND id_usuario='".current_user()."')";
		$result=execute_query_array($query);
		$numresult=count($result);
		if($numresult==$numids) {
			$action2=explode("=",getParam("action2"));
			// CONTINUAR
			if($action2[0]=="leidos") {
				// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
				$query="SELECT COUNT(*) FROM tbl_correo WHERE id IN ($ids) AND state_new!='${action2[1]}' AND is_outbox='0'";
				$numids=execute_query($query);
				// PONER STATE_NEW=0 EN LOS CORREOS SELECCIONADOS
				$query=make_update_query("tbl_correo",array(
					"state_new"=>$action2[1]
				),"id IN (${ids}) AND state_new!='${action2[1]}' AND is_outbox='0'");
				db_query($query);
				// MOSTRAR RESULTADO
				session_alert(LANG($action2[1]?"msgnumnoleidos":"msgnumsileidos","correo").$numids.LANG("message".min($numids,2),"correo"));
			} elseif($action2[0]=="wait") {
				// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
				$query="SELECT COUNT(*) FROM tbl_correo WHERE id IN ($ids) AND state_wait!='${action2[1]}'";
				$numids=execute_query($query);
				// PONER STATE_WAIT=1 EN LOS CORREOS SELECCIONADOS
				$query=make_update_query("tbl_correo",array(
					"state_new"=>"0",
					"state_wait"=>$action2[1]
				),"id IN (${ids}) AND state_wait!='${action2[1]}'");
				db_query($query);
				// MOSTRAR RESULTADO
				session_alert(LANG($action2[1]?"msgnumsiwait":"msgnumnowait","correo").$numids.LANG("message".min($numids,2),"correo"));
			} elseif($action2[0]=="spam") {
				// BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
				$query="SELECT COUNT(*) FROM tbl_correo WHERE id IN ($ids) AND state_spam!='${action2[1]}' AND is_outbox='0'";
				$numids=execute_query($query);
				// PONER STATE_SPAM=1 EN LOS CORREOS SELECCIONADOS
				$query=make_update_query("tbl_correo",array(
					"state_new"=>"0",
					"state_spam"=>$action2[1]
				),"id IN (${ids}) AND state_spam!='${action2[1]}' AND is_outbox='0'");
				db_query($query);
				// MOSTRAR RESULTADO
				session_alert(LANG($action2[1]?"msgnumsispam":"msgnumnospam","correo").$numids.LANG("message".min($numids,2),"correo"));
			} elseif($action2[0]=="delete") {
				// CREAR DATOS EN TABLA DE CORREOS BORRADOS (SOLO LOS DEL INBOX)
				$query=make_insert_query("tbl_correo_d",make_select_query("tbl_correo",array(
					"id_cuenta",
					"uidl",
					"datetime"
				),make_where_query(array(
					"is_outbox"=>0
				),"AND",array(
					"id IN (${ids})"
				))),array(
					"id_cuenta",
					"uidl",
					"datetime"
				));
				db_query($query);
				// BORRAR FICHEROS .EML.GZ DEL INBOX
				$query="SELECT CONCAT('".get_directory("dirs/inboxdir")."',id_cuenta,'/',uidl,'".getDefault("exts/emailext",".eml").getDefault("exts/gzipext",".gz")."') action_delete FROM tbl_correo WHERE id IN ($ids) AND is_outbox='0'";
				$result=execute_query_array($query);
				foreach($result as $delete) if(file_exists($delete)) unlink($delete);
				// BORRAR FICHEROS .EML.GZ DEL OUTBOX
				$query="SELECT CONCAT('".get_directory("dirs/outboxdir")."',id_cuenta,'/',uidl,'".getDefault("exts/emailext",".eml").getDefault("exts/gzipext",".gz")."') action_delete FROM tbl_correo WHERE id IN ($ids) AND is_outbox='1'";
				$result=execute_query_array($query);
				foreach($result as $delete) if(file_exists($delete)) unlink($delete);
				// BORRAR FICHEROS .OBJ DEL OUTBOX
				$query="SELECT CONCAT('".get_directory("dirs/outboxdir")."',id_cuenta,'/',uidl,'".getDefault("exts/objectext",".obj")."') action_delete FROM tbl_correo WHERE id IN ($ids) AND is_outbox='1'";
				$result=execute_query_array($query);
				foreach($result as $delete) if(file_exists($delete)) unlink($delete);
				// BORRAR CORREOS
				$query=make_delete_query("tbl_correo","id IN (${ids})");
				db_query($query);
				// BORRAR DIRECCIONES DE LOS CORREOS
				$query=make_delete_query("tbl_correo_a","id_correo IN (${ids})");
				db_query($query);
				// BORRAR FICHEROS ADJUNTOS DE LOS CORREOS
				$query=make_delete_query("tbl_ficheros","id_registro IN (${ids}) AND id_aplicacion='".page2id("correo")."'");
				db_query($query);
				// BORRAR REGISTRO DE LOS CORREOS
				make_control(page2id("correo"),$ids);
				make_indexing(page2id("correo"),$ids);
				// BORRAR FOLDERS RELACIONADOS
				$query=make_delete_query("tbl_folders_a","id_registro IN (${ids}) AND id_aplicacion='".page2id("correo")."'");
				db_query($query);
				// MOSTRAR RESULTADO
				session_alert(LANG("msgnumdelete","correo").$numids.LANG("message".min($numids,2),"correo"));
			}
		} else {
			session_error(LANG("msgpropietario","correo"));
		}
	} else {
		session_error(LANG("msgnotfound","correo"));
	}
	javascript_history(-1);
	die();
}
if(in_array($page,array("profile","usuarios"))) {
	global $correo_new;
	$temp_key="correo_new_0_email_signature_new";
	if($correo_new && !getParam($temp_key)) {
		$outputdir=get_directory("dirs/filesdir");
		$temp_data=str_replace("SaltOS","<a href='http://www.saltos.org'>SaltOS</a>",LANG("sentfromsaltos"));
		$temp_name="email_signature".getDefault("exts/htmlext",".htm");
		$temp_file=time()."_".get_unique_id_md5()."_".encode_bad_chars_file($temp_name);
		$temp_size=strlen($temp_data);
		$temp_type="text/html";
		file_put_contents($outputdir.$temp_file,$temp_data);
		setParam($temp_key,$temp_name);
		setParam($temp_key."_file",$temp_file);
		setParam($temp_key."_size",$temp_size);
		setParam($temp_key."_type",$temp_type);
	}
}
?>