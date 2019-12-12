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
if($page=="incidencias") {
	require_once("php/report.php");
	require_once("php/sendmail.php");
	require_once("php/libaction.php");
	// DATOS SMPT
	if(!CONFIG("id_cuenta_incidencias")) {
		session_error(LANG("msgnotsmtpemail"));
		javascript_history(-1);
		die();
	}
	// DATOS EMAIL
	$id_incidencia=intval(($action=="insert")?execute_query("SELECT MAX(id) FROM tbl_incidencias"):getParam("id"));
	$query="SELECT ".make_extra_query_with_field("email","d.")." mailto,".make_extra_query_with_login("d.")." usuario
			FROM tbl_incidencias_u a
			LEFT JOIN tbl_usuarios d ON a.id_usuario=d.id
			WHERE a.id_incidencia='$id_incidencia'";
	$result2=db_query($query);
	if(db_num_rows($result2)) {
		$to=array();
		while($row2=db_fetch_row($result2)) {
			$to[]=$row2["usuario"]." <".$row2["mailto"].">";
		}
		db_free($result2);
		$subject=($action=="insert")?LANG_ESCAPE("forminsert"):LANG_ESCAPE("formupdate");
		$body=__report_begin2($subject);
		$body.="<table cellpadding='0' cellspacing='0' border='0'><tr><td valign='top'>";
		$body.=__report_begin3($subject);
		// DATOS GENERALES
		$id_aplicacion=page2id($page);
		// DATOS INCIDENCIA
		$query="SELECT a.nombre nombre,
				a.descripcion descripcion,
				CASE a.id_cliente WHEN '0' THEN '".LANG_ESCAPE("sincliente")."' ELSE b.nombre END cliente,
				CASE id_proyecto WHEN '0' THEN '".LANG_ESCAPE("sinproyecto")."' ELSE c.nombre END proyecto,
				d.nombre estado,
				".make_extra_query_with_login("f.")." username,
				e.datetime datetime,
				g.nombre prioridad
			FROM tbl_incidencias a
			LEFT JOIN tbl_clientes b ON a.id_cliente=b.id
			LEFT JOIN tbl_proyectos c ON a.id_proyecto=c.id
			LEFT JOIN tbl_estados d ON a.id_estado=d.id
			LEFT JOIN tbl_registros e ON e.id_aplicacion='$id_aplicacion' AND e.id_registro=a.id AND e.first=1
			LEFT JOIN tbl_usuarios f ON e.id_usuario=f.id
			LEFT JOIN tbl_prioridades g ON g.id=a.id_prioridad
			WHERE a.id='$id_incidencia'";
		$result2=db_query($query);
		if(db_num_rows($result2)) {
			$row2=db_fetch_row($result2);
			db_free($result2);
			// MEJORAR SUBJECT REAL
			$subject.=": ".$row2["nombre"];
			// BODY INCIDENCIAS
			$campos=array("username","datetime","cliente","proyecto","nombre","estado","prioridad","descripcion");
			$tipos=array("text","text","text","text","text","text","text","textarea");
			$body.=__incidencias_packreport($campos,$tipos,$row2);
			// DATOS COMENTARIOS
			$query="SELECT comentarios,datetime,
					".make_extra_query_with_login("b.")." username
				FROM tbl_comentarios a
				LEFT JOIN tbl_usuarios b ON a.id_usuario=b.id
				WHERE a.id_aplicacion='$id_aplicacion' AND a.id_registro='$id_incidencia'";
			$result2=db_query($query);
			// BODY COMENTARIOS
			$campos=array("username","datetime","comentarios");
			$tipos=array("text","text","textarea");
			$body.=__report_end3();
			$body.="</td><td valign='top'>";
			$body.=__report_begin3("&nbsp;");
			while($row2=db_fetch_row($result2)) {
				$body.=__incidencias_packreport($campos,$tipos,$row2);
			}
			db_free($result2);
			// DATOS USUARIOS
			$query="SELECT
					REPLACE(GROUP_CONCAT(".make_extra_query_with_login("b.")."),',','; ') usersdata
				FROM tbl_incidencias_u a
				LEFT JOIN tbl_usuarios b ON a.id_usuario=b.id
				WHERE id_incidencia='$id_incidencia'";
			$result2=db_query($query);
			// BODY USUARIOS
			$campos=array("usersdata");
			$tipos=array("text");
			while($row2=db_fetch_row($result2)) {
				$body.=__incidencias_packreport($campos,$tipos,$row2);
			}
			db_free($result2);
			// CERRAR BODY
			$temp=LANG("view")." ".LANG("incidencia");
			$body.=__report_link($temp,get_base()."?page=incidencias&action=form&id=-$id_incidencia",$temp);
			$temp=LANG("edit")." ".LANG("incidencia");
			$body.=__report_link($temp,get_base()."?page=incidencias&action=form&id=$id_incidencia",$temp);
			$body.=__report_end3();
			$body.="</td></tr></table>";
			$body.=__report_end2();
			// PARA DEBUGAR
			//~ echo "<pre>SUBJECT=$subject BODY=$body TO=".sprintr($to)."</pre>";
			//~ die();
			//~ echo $body;
			//~ die();
			// ENVIAR EMAIL
			$error=sendmail(CONFIG("id_cuenta_incidencias"),$to,$subject,$body);
			if($error) {
				session_error($error);
			} else {
				session_alert(LANG("msgsendemail"));
			}
		}
	}
}
if($page=="correo") {
	if($action=="form") {
		require_once("php/getmail.php");
		// DATOS CORREO
		$ok=0;
		$email_support=CONFIG("email_support");
		if($email_support) {
			$id_correo=abs($id);
			if(__getmail_checkperm($id_correo)) {
				$decoded=__getmail_getmime($id_correo);
				if($decoded) {
					$info=__getmail_getinfo(__getmail_getnode("0",$decoded));
					foreach($info["emails"] as $email) {
						if($email["valor"]!="" && strpos($email_support,$email["valor"])!==false) $ok=1;
					}
				}
			}
		}
	}
	if($action=="incidencias") {
		require_once("php/getmail.php");
		require_once("php/libaction.php");
		// DATOS CORREO
		$id_correo=abs($id);
		if(!__getmail_checkperm($id_correo)) action_denied();
		$decoded=__getmail_getmime($id_correo);
		if(!$decoded) {
			session_error(LANG("msgopenerrorpop3email","correo"));
			javascript_history(-1);
			die();
		}
		// CHECK SI EXISTE LA INCIDENCIA
		$query="SELECT id FROM tbl_incidencias WHERE id_correo='${id_correo}'";
		$id_incidencia=execute_query($query);
		if($id_incidencia) {
			session_error(LANG("incidenciaexists","correo").__incidencias_codigo($id_incidencia));
			javascript_history(-1);
			die();
		}
		// COJER DATOS DEL EMAIL
		$info=__getmail_getinfo(__getmail_getnode("0",$decoded));
		$body=__getmail_gettextbody(__getmail_getnode("0",$decoded));
		$result=__getmail_getfullbody(__getmail_getnode("0",$decoded));
		$files=__getmail_getfiles(__getmail_getnode("0",$decoded));
		// CHECK EMAIL_SUPPORT
		$email_support=CONFIG("email_support");
		if(!$email_support) {
			session_error(LANG("notsupportdefined","correo"));
			javascript_history(-1);
			die();
		}
		$ok=0;
		foreach($info["emails"] as $email) {
			if($email["valor"]!="" && strpos($email_support,$email["valor"])!==false) $ok=1;
		}
		if(!$ok) {
			session_error(LANG("notsupportfound","correo"));
			javascript_history(-1);
			die();
		}
		// HACER INSERT INCIDENCIA
		$subject=$info["subject"];
		if(strlen($subject)>=255) $subject=substr($subject,0,251)."...";
		$query=make_insert_query("tbl_incidencias",array(
			"nombre"=>$subject,
			"descripcion"=>$body,
			"id_correo"=>$id_correo
		));
		db_query($query);
		$query="SELECT MAX(id) FROM tbl_incidencias";
		$id_incidencia=execute_query($query);
		// HACER INSERT REGISTRO CONTROL
		$id_aplicacion=page2id("incidencias");
		$id_usuario=current_user();
		$datetime=current_datetime();
		make_control($id_aplicacion,$id_incidencia);
		make_indexing($id_aplicacion,$id_incidencia);
		// AÑADIR PDF CON CORREO ORIGINAL
		$action="pdf";
		setParam("action",$action);
		$_GET["id"]=$id_correo;
		ob_start();
		if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
		include("php/action/pdf.php");
		$pdf=ob_get_clean();
		$name=encode_bad_chars_file(LANG("correo","menu")." ".__incidencias_codigo($id_correo)." ".$subject.".pdf");
		$file=time()."_".get_unique_id_md5()."_".$name;
		$size=strlen($pdf);
		$type="application/pdf";
		file_put_contents(get_directory("dirs/filesdir").$file,$pdf);
		$query=make_insert_query("tbl_ficheros",array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_incidencia,
			"id_usuario"=>$id_usuario,
			"datetime"=>$datetime,
			"fichero"=>$name,
			"fichero_file"=>$file,
			"fichero_size"=>$size,
			"fichero_type"=>$type
		));
		db_query($query);
		// AÑADIR IMAGENES INLINE
		foreach($result as $index=>$node) {
			$disp=$node["disp"];
			$type=$node["type"];
			if(!__getmail_processplainhtml($disp,$type) && !__getmail_processmessage($disp,$type)) {
				$cid=$node["cid"];
				if($cid!="") {
					$name=$node["cname"];
					$file=time()."_".get_unique_id_md5()."_".encode_bad_chars_file($node["cname"]);
					file_put_contents(get_directory("dirs/filesdir").$file,$node["body"]);
					$size=$node["csize"];
					$type=$node["ctype"];
					$query=make_insert_query("tbl_ficheros",array(
						"id_aplicacion"=>$id_aplicacion,
						"id_registro"=>$id_incidencia,
						"id_usuario"=>$id_usuario,
						"datetime"=>$datetime,
						"fichero"=>$name,
						"fichero_file"=>$file,
						"fichero_size"=>$size,
						"fichero_type"=>$type
					));
					db_query($query);
				}
			}
		}
		// AÑADIR ADJUNTOS
		foreach($files as $node) {
			$name=$node["cname"];
			$file=time()."_".get_unique_id_md5()."_".encode_bad_chars_file($node["cname"]);
			file_put_contents(get_directory("dirs/filesdir").$file,$node["body"]);
			$size=$node["csize"];
			$type=$node["ctype"];
			$query=make_insert_query("tbl_ficheros",array(
				"id_aplicacion"=>$id_aplicacion,
				"id_registro"=>$id_incidencia,
				"id_usuario"=>$id_usuario,
				"datetime"=>$datetime,
				"fichero"=>$name,
				"fichero_file"=>$file,
				"fichero_size"=>$size,
				"fichero_type"=>$type
			));
			db_query($query);
		}
		// REBOTAR AL FORMULARIO PARA CONTESTAR
		session_alert(LANG("addincidenciaok").__incidencias_codigo($id_incidencia));
		javascript_location_page("correo&action=form&id=0_replyall_".$id_correo);
		die();
	}
}

?>
