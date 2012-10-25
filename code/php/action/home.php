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
if(getParam("action")=="home") {
	// ALL COLORS
	$header="box ui-widget-header ui-corner-all";
	$colors=array();
	$colors[]="box ui-widget-content ui-corner-all";
	$colors[]="box ui-state-default ui-corner-all";
	$color=0;
	$app2color=array();
	// DO THE QUERY
	$_RESULT=array();
	$query="SELECT * FROM (
		SELECT a.id id,
			a.id_folder id_folder,
			a.id_aplicacion id_aplicacion,
			a.id_registro id_registro,
			e.id_usuario id_usuario,
			d.id_grupo id_grupo,
			(SELECT nombre FROM tbl_aplicaciones WHERE id=a.id_aplicacion) aplicacion,
			(SELECT codigo FROM tbl_aplicaciones WHERE id=a.id_aplicacion) page,
			CASE a.id_aplicacion
				WHEN '".page2id("correo")."' THEN (SELECT email_privated FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE e.id_registro=id))
				ELSE 0
			END email_privated,
			CASE a.id_aplicacion
				WHEN '".page2id("actas")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_actas WHERE id=a.id_registro)
				WHEN '".page2id("agenda")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_agenda WHERE id=a.id_registro)
				WHEN '".page2id("campanyas")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_campanyas WHERE id=a.id_registro)
				WHEN '".page2id("clientes")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_clientes WHERE id=a.id_registro)
				WHEN '".page2id("contactos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_contactos WHERE id=a.id_registro)
				WHEN '".page2id("correo")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',`from`,' - ',CASE WHEN subject='' THEN '".LANG_ESCAPE("sinsubject","correo")."' ELSE subject END,' (', *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || `from` || ' - ' || CASE WHEN subject='' THEN '".LANG_ESCAPE("sinsubject","correo")."' ELSE subject END || ' (' || */
				TRIM(/*MYSQL CONCAT(
				CASE state_sent WHEN 1 THEN '".LANG_ESCAPE("statesent","correo")." ' ELSE '' END,
				CASE state_error WHEN '' THEN '' ELSE '".LANG_ESCAPE("stateerror","correo")." ' END,
				CASE WHEN is_outbox=1 AND state_sent=0 AND state_error='' THEN ' (".LANG_ESCAPE("statenotsent","correo").") ' ELSE '' END,
				CASE state_new WHEN 1 THEN '".LANG_ESCAPE("statenew","correo")." ' ELSE '' END,
				CASE state_reply WHEN 1 THEN '".LANG_ESCAPE("statereply","correo")." ' ELSE '' END,
				CASE state_forward WHEN 1 THEN '".LANG_ESCAPE("stateforward","correo")." ' ELSE '' END,
				CASE state_wait WHEN 1 THEN '".LANG_ESCAPE("statewait","correo")." ' ELSE '' END,
				CASE state_spam WHEN 1 THEN '".LANG_ESCAPE("statespam","correo")." ' ELSE '' END,
				CASE priority WHEN -1 THEN '".LANG_ESCAPE("prioritylow","correo")." ' WHEN 1 THEN '".LANG_ESCAPE("priorityhigh","correo")." ' ELSE '' END,
				CASE sensitivity WHEN 1 THEN '".LANG_ESCAPE("sensitivitypersonal","correo")." ' WHEN 2 THEN '".LANG_ESCAPE("sensitivityprivate","correo")." ' WHEN 3 THEN '".LANG_ESCAPE("sensitivityconfidential","correo")." ' ELSE '' END,
				CASE is_outbox+state_new+state_reply+state_forward+state_wait+state_spam+priority*10+sensitivity WHEN 0 THEN '".LANG_ESCAPE("stateread","correo")." ' ELSE '' END)
				*//*SQLITE
				CASE state_sent WHEN 1 THEN '".LANG_ESCAPE("statesent","correo")." ' ELSE '' END ||
				CASE state_error WHEN '' THEN '' ELSE '".LANG_ESCAPE("stateerror","correo")." ' END ||
				CASE WHEN is_outbox=1 AND state_sent=0 AND state_error='' THEN '".LANG_ESCAPE("statenotsent","correo")." ' ELSE '' END ||
				CASE state_new WHEN 1 THEN '".LANG_ESCAPE("statenew","correo")." ' ELSE '' END ||
				CASE state_reply WHEN 1 THEN '".LANG_ESCAPE("statereply","correo")." ' ELSE '' END ||
				CASE state_forward WHEN 1 THEN '".LANG_ESCAPE("stateforward","correo")." ' ELSE '' END ||
				CASE state_wait WHEN 1 THEN '".LANG_ESCAPE("statewait","correo")." ' ELSE '' END ||
				CASE state_spam WHEN 1 THEN '".LANG_ESCAPE("statespam","correo")." ' ELSE '' END ||
				CASE priority WHEN -1 THEN '".LANG_ESCAPE("prioritylow","correo")." ' WHEN 1 THEN '".LANG_ESCAPE("priorityhigh","correo")." ' ELSE '' END ||
				CASE sensitivity WHEN 1 THEN '".LANG_ESCAPE("sensitivitypersonal","correo")." ' WHEN 2 THEN '".LANG_ESCAPE("sensitivityprivate","correo")." ' WHEN 3 THEN '".LANG_ESCAPE("sensitivityconfidential","correo")." ' ELSE '' END ||
				CASE is_outbox+state_new+state_reply+state_forward+state_wait+state_spam+priority*10+sensitivity WHEN 0 THEN '".LANG_ESCAPE("stateread","correo")." ' ELSE '' END
				*/)
				/*MYSQL ,')') *//*SQLITE || ')' */ FROM tbl_correo WHERE id=a.id_registro)
				WHEN '".page2id("cuentas")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_cuentas WHERE id=a.id_registro)
				WHEN '".page2id("documentos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_documentos WHERE id=a.id_registro)
				WHEN '".page2id("empleados")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_empleados WHERE id=a.id_registro)
				WHEN '".page2id("epigrafes")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_epigrafes WHERE id=a.id_registro)
				WHEN '".page2id("estados")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_estados WHERE id=a.id_registro)
				WHEN '".page2id("facturas")."' THEN (SELECT CASE num WHEN '' THEN /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ','".LANG_ESCAPE("albaran")."',' ',LPAD(id,5,0),' ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || '".LANG_ESCAPE("albaran")."' || ' ' || SUBSTR('00000' || id,-5,5) || ' ' || nombre */ ELSE /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ','".LANG_ESCAPE("factura")."',' ',num,' ',nombre,CASE cobrado WHEN '1' THEN '".LANG_ESCAPE("cobrado","facturas")."' ELSE '<strike>".LANG_ESCAPE("cobrado","facturas")."</strike>' END) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || '".LANG_ESCAPE("factura")."' || ' ' || num || ' ' || nombre || CASE cobrado WHEN '1' THEN '".LANG_ESCAPE("cobrado","facturas")."' ELSE '<strike>".LANG_ESCAPE("cobrado","facturas")."</strike>' END */ END FROM tbl_facturas WHERE id=a.id_registro)
				WHEN '".page2id("feeds")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',(SELECT title FROM tbl_usuarios_f WHERE id=tbl_feeds.id_feed),' - ',title,' (', *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || (SELECT title FROM tbl_usuarios_f WHERE id=tbl_feeds.id_feed) || ' - ' || title || ' (' || */
				TRIM(/*MYSQL CONCAT(
				CASE WHEN state_new=1 AND state_modified=0 THEN '".LANG_ESCAPE("statenew","feeds")." ' ELSE '' END,
				CASE state_modified WHEN 1 THEN '".LANG_ESCAPE("statemodified","feeds")." ' ELSE '' END,
				CASE state_wait WHEN 1 THEN '".LANG_ESCAPE("statewait","feeds")." ' ELSE '' END,
				CASE state_new+state_modified+state_wait WHEN 0 THEN '".LANG_ESCAPE("stateread","feeds")." ' ELSE '' END)
				*//*SQLITE
				CASE WHEN state_new=1 AND state_modified=0 THEN '".LANG_ESCAPE("statenew","feeds")." ' ELSE '' END ||
				CASE state_modified WHEN 1 THEN '".LANG_ESCAPE("statemodified","feeds")." ' ELSE '' END ||
				CASE state_wait WHEN 1 THEN '".LANG_ESCAPE("statewait","feeds")." ' ELSE '' END ||
				CASE state_new+state_modified+state_wait WHEN 0 THEN '".LANG_ESCAPE("stateread","feeds")." ' ELSE '' END
				*/)
				/*MYSQL ,')') *//*SQLITE || ')' */ FROM tbl_feeds WHERE id=a.id_registro)
				WHEN '".page2id("formaspago")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_formaspago WHERE id=a.id_registro)
				WHEN '".page2id("gastos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',descripcion) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || descripcion */ FROM tbl_gastos WHERE id=a.id_registro)
				WHEN '".page2id("grupos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_grupos WHERE id=a.id_registro)
				WHEN '".page2id("importaciones")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_importaciones WHERE id=a.id_registro)
				WHEN '".page2id("incidencias")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_incidencias WHERE id=a.id_registro)
				WHEN '".page2id("partes")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',tarea) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || tarea */ FROM tbl_partes WHERE id=a.id_registro)
				WHEN '".page2id("periodicas")."' THEN (SELECT (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_clientes WHERE id=id_cliente) FROM tbl_periodicas WHERE id=a.id_registro)
				WHEN '".page2id("posiblescli")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_posiblescli WHERE id=a.id_registro)
				WHEN '".page2id("presupuestos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_presupuestos WHERE id=a.id_registro)
				WHEN '".page2id("prioridades")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_prioridades WHERE id=a.id_registro)
				WHEN '".page2id("productos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_productos WHERE id=a.id_registro)
				WHEN '".page2id("proveedores")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_proveedores WHERE id=a.id_registro)
				WHEN '".page2id("proyectos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_proyectos WHERE id=a.id_registro)
				WHEN '".page2id("seguimientos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',comentarios) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || comentarios */ FROM tbl_seguimientos WHERE id=a.id_registro)
				WHEN '".page2id("textos")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_textos WHERE id=a.id_registro)
				WHEN '".page2id("tiposevento")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',nombre) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || nombre */ FROM tbl_tiposevento WHERE id=a.id_registro)
				WHEN '".page2id("usuarios")."' THEN (SELECT /*MYSQL CONCAT(LPAD(a.id_registro,5,0),' - ',login) *//*SQLITE SUBSTR('00000' || a.id_registro,-5,5) || ' - ' || login */ FROM tbl_usuarios WHERE id=a.id_registro)
			END nombre,
			CASE a.id_aplicacion
				WHEN '".page2id("actas")."' THEN 'true'
				WHEN '".page2id("campanyas")."' THEN 'true'
				WHEN '".page2id("clientes")."' THEN 'true'
				WHEN '".page2id("correo")."' THEN 'true'
				WHEN '".page2id("facturas")."' THEN 'true'
				WHEN '".page2id("feeds")."' THEN 'true'
				WHEN '".page2id("partes")."' THEN 'true'
				WHEN '".page2id("posiblescli")."' THEN 'true'
				WHEN '".page2id("presupuestos")."' THEN 'true'
				WHEN '".page2id("proyectos")."' THEN 'true'
				ELSE 'false'
			END action_pdf,
			CASE a.id_aplicacion
				WHEN '".page2id("actas")."' THEN 'true'
				WHEN '".page2id("campanyas")."' THEN 'true'
				WHEN '".page2id("clientes")."' THEN 'true'
				WHEN '".page2id("correo")."' THEN 'true'
				WHEN '".page2id("facturas")."' THEN 'true'
				WHEN '".page2id("feeds")."' THEN 'true'
				WHEN '".page2id("partes")."' THEN 'true'
				WHEN '".page2id("posiblescli")."' THEN 'true'
				WHEN '".page2id("presupuestos")."' THEN 'true'
				WHEN '".page2id("proyectos")."' THEN 'true'
				ELSE 'false'
			END action_view2,
			CASE a.id_aplicacion
				WHEN '".page2id("correo")."' THEN 'false'
				WHEN '".page2id("feeds")."' THEN 'false'
				ELSE 'true'
			END action_edit,
			CASE a.id_aplicacion
				WHEN '".page2id("actas")."' THEN 'true'
				WHEN '".page2id("agenda")."' THEN 'true'
				WHEN '".page2id("facturas")."' THEN 'true'
				WHEN '".page2id("gastos")."' THEN 'true'
				WHEN '".page2id("partes")."' THEN 'true'
				WHEN '".page2id("periodicas")."' THEN 'true'
				WHEN '".page2id("presupuestos")."' THEN 'true'
				WHEN '".page2id("proyectos")."' THEN 'true'
				WHEN '".page2id("seguimientos")."' THEN 'true'
				ELSE 'false'
			END action_copy,
			CASE a.id_aplicacion
				WHEN '".page2id("correo")."' THEN CASE (SELECT is_outbox FROM tbl_correo WHERE id=a.id_registro) WHEN 0 THEN 'true' ELSE 'false' END
				ELSE 'false'
			END action_reply,
			CASE a.id_aplicacion
				WHEN '".page2id("correo")."' THEN CASE (SELECT is_outbox FROM tbl_correo WHERE id=a.id_registro) WHEN 0 THEN CASE WHEN (SELECT COUNT(*) FROM tbl_correo_a WHERE id_correo=a.id_registro AND id_tipo IN (2,3))>1 THEN 'true' ELSE 'false' END ELSE 'false' END
				ELSE 'false'
			END action_replyall,
			CASE a.id_aplicacion
				WHEN '".page2id("correo")."' THEN 'true'
				WHEN '".page2id("feeds")."' THEN 'true'
				ELSE 'false'
			END action_forward,
			CASE a.id_aplicacion WHEN '".page2id("clientes")."' THEN a.id_registro WHEN '".page2id("proyectos")."' THEN (SELECT id_cliente FROM tbl_proyectos WHERE id=a.id_registro) ELSE '0' END id_cliente,
			CASE a.id_aplicacion WHEN '".page2id("proyectos")."' THEN a.id_registro ELSE '0' END id_proyecto,
			CASE a.id_aplicacion WHEN '".page2id("proveedores")."' THEN a.id_registro ELSE '0' END id_proveedor
		FROM tbl_folders_a a
		LEFT JOIN tbl_registros_i e ON e.id_aplicacion=a.id_aplicacion AND e.id_registro=a.id_registro
		LEFT JOIN tbl_usuarios d ON e.id_usuario=d.id) z
		WHERE (TRIM(IFNULL(email_privated,0))='0' OR (TRIM(IFNULL(email_privated,0))='1' AND id_usuario='".current_user()."')) AND (id_folder='".intval(getParam("id_folder"))."')
			AND (id_folder IN (SELECT id FROM tbl_folders WHERE id_usuario='".current_user()."')) AND ".check_sql($page,"list")."
		ORDER BY aplicacion";
	$result=db_query($query);
	// CHECK IF NOT CONTAIN DATA
	if(!db_num_rows($result)) {
		$data=LANG("nodata","home");
		$class="box ui-state-highlight ui-corner-all";
		$style="font-weight:normal";
		$_RESULT["nodata"]=array();
		set_array($_RESULT["nodata"],"row",array("data"=>$data,"class"=>$class,"style"=>$style));
	}
	// PREPARE THE RESULT
	while($row=db_fetch_row($result)) {
		if(!isset($app2color[$row["id_aplicacion"]])) {
			$app2color[$row["id_aplicacion"]]=$color;
			$color=($color+1)%count($colors);
			$data="<b>".$row["aplicacion"]."</b>";
			$urlview="opencontent(\"xml.php?page=".$row["page"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlview'>".LANG("view")."</a>]";
			$urlhide="delappfolder(\"".$row["id_folder"]."\",\"".$row["page"]."\");update_home(".$row["id_folder"].")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlhide'>".LANG("hide","home")."</a>]";
			$class=$header;
			$style="font-weight:normal";
			$_RESULT[$row["page"]]=array();
			set_array($_RESULT[$row["page"]],"row",array("data"=>$data,"class"=>$class,"style"=>$style));
		}
		$data="<b>".$row["nombre"]."</b>";
		$urlview="opencontent(\"xml.php?page=".$row["page"]."&action=form&id=-".$row["id_registro"]."&id_folder=".$row["id_folder"]."\")";
		$data.=" [<a href='javascript:void(0)' onclick='$urlview'>".LANG("view")."</a>]";
		if(eval_bool($row["action_pdf"])) {
			$urlpdf="openurl(\"xml.php?page=".$row["page"]."&action=pdf&id=".$row["id_registro"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlpdf'>".LANG("pdf")."</a>]";
		}
		if(eval_bool($row["action_view2"])) {
			$urlview2="viewpdf(\"page=".$row["page"]."&id=".$row["id_registro"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlview2'>".LANG("view2")."</a>]";
		}
		if(eval_bool($row["action_edit"])) {
			$urledit="opencontent(\"xml.php?page=".$row["page"]."&action=form&id=".$row["id_registro"]."&id_folder=".$row["id_folder"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urledit'>".LANG("edit")."</a>]";
		}
		if(eval_bool($row["action_copy"])) {
			$urlcopy="opencontent(\"xml.php?page=".$row["page"]."&action=form&id=0_copy_".$row["id_registro"]."&id_folder=".$row["id_folder"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlcopy'>".LANG("copy")."</a>]";
		}
		if(eval_bool($row["action_reply"])) {
			$urlreply="opencontent(\"xml.php?page=correo&action=form&id=0_reply_".$row["id_registro"]."&id_folder=".$row["id_folder"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlreply'>".LANG("actionreply","correo")."</a>]";
		}
		if(eval_bool($row["action_replyall"])) {
			$urlreplyall="opencontent(\"xml.php?page=correo&action=form&id=0_replyall_".$row["id_registro"]."&id_folder=".$row["id_folder"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlreplyall'>".LANG("actionreplyall","correo")."</a>]";
		}
		if(eval_bool($row["action_forward"])) {
			$action_real=($row["page"]=="feeds")?"feed":"forward";
			$urlforward="opencontent(\"xml.php?page=correo&action=form&id=0_${action_real}_".$row["id_registro"]."&id_folder=".$row["id_folder"]."\")";
			$data.=" [<a href='javascript:void(0)' onclick='$urlforward'>".LANG("actionforward","correo")."</a>]";
		}
		$urlhide="delregfolder(\"".$row["id_folder"]."\",\"".$row["page"]."\",\"".$row["id_registro"]."\");update_home(".$row["id_folder"].")";
		$data.=" [<a href='javascript:void(0)' onclick='$urlhide'>".LANG("hide","home")."</a>]";
		$class=$colors[$app2color[$row["id_aplicacion"]]];
		$style="font-weight:normal";
		set_array($_RESULT[$row["page"]],"row",array("data"=>$data,"class"=>$class,"style"=>$style));
	}
	db_free($result);
	// ENVIAR XML DE SALIDA
	$buffer="<?xml version='1.0' encoding='UTF-8' ?>\n";
	$buffer.=array2xml($_RESULT);
	output_buffer($buffer,"text/xml");
}
?>