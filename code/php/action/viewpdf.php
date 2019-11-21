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
if(getParam("action")=="viewpdf") {
	// CREATE REPORT FROM DATABASE
	$_RESULT=array("rows"=>array());
	if(getParam("page") && !getParam("id") && !getParam("cid")) {
		$file="doc/${lang}/${page}.pdf";
		if(!file_exists($file)) {
			$files=glob("doc/*/${page}.pdf");
			if(isset($files[0])) $file=$files[0];
		}
		if(!file_exists($file)) {
			$files=glob("doc/${lang}/404.pdf");
			if(isset($files[0])) $file=$files[0];
		}
		if(!file_exists($file)) {
			$files=glob("doc/*/404.pdf");
			if(isset($files[0])) $file=$files[0];
		}
		$data=file_get_contents($file);
		$hash=md5($data);
		$data=base64_encode($data);
		set_array($_RESULT["rows"],"row",array("title"=>LANG("help"),"hash"=>$hash,"data"=>$data));
	} elseif(getParam("page") && getParam("id") && !getParam("cid")) {
		// DATOS FACTURA/ACTA/PARTE/PRESUPUESTO
		$where="WHERE id IN (".check_ids(getParam("id")).")";
		// CALCULAR EL HASH
		if($page=="facturas") $queryes=array(
			"SELECT * FROM tbl_facturas $where",
			"SELECT * FROM tbl_facturas_c WHERE id_factura IN (SELECT id FROM tbl_facturas $where)",
			"SELECT * FROM tbl_facturas_v WHERE id_factura IN (SELECT id FROM tbl_facturas $where)"
		);
		if($page=="actas") $queryes=array(
			"SELECT * FROM tbl_actas $where"
		);
		if($page=="partes") $queryes=array(
			"SELECT * FROM tbl_partes $where"
		);
		if($page=="presupuestos") $queryes=array(
			"SELECT * FROM tbl_presupuestos $where",
			"SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos $where)",
			"SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos $where)"
		);
		if($page=="clientes") $queryes=array(
			"SELECT * FROM tbl_clientes $where",
			"SELECT * FROM tbl_comentarios WHERE id_aplicacion='".page2id("clientes")."' AND id_registro IN (SELECT id FROM tbl_clientes $where)",
			"SELECT * FROM tbl_contactos WHERE id_aplicacion='".page2id("clientes")."' AND id_registro IN (SELECT id FROM tbl_clientes $where) AND '".getParam("contactos")."'='1'",
			"SELECT * FROM tbl_incidencias WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("incidencias")."'='1'",
			"SELECT * FROM tbl_proyectos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("proyectos")."'='1'",
			"SELECT * FROM tbl_proyectos_t WHERE id_proyecto IN (SELECT id FROM tbl_proyectos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("proyectos")."'='1')",
			"SELECT * FROM tbl_proyectos_p WHERE id_proyecto IN (SELECT id FROM tbl_proyectos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("proyectos")."'='1')",
			"SELECT * FROM tbl_partes WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("partes")."'='1'",
			"SELECT * FROM tbl_facturas WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("facturas")."'='1'",
			"SELECT * FROM tbl_facturas_c WHERE id_factura IN (SELECT id FROM tbl_facturas WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("facturas")."'='1')",
			"SELECT * FROM tbl_gastos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("gastos")."'='1'",
			"SELECT * FROM tbl_agenda WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("agenda")."'='1'",
			"SELECT * FROM tbl_presupuestos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("presupuestos")."'='1'",
			"SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_actas WHERE id_cliente IN (SELECT id FROM tbl_clientes $where) AND '".getParam("actas")."'='1'"
		);
		if($page=="proyectos") $queryes=array(
			"SELECT * FROM tbl_proyectos $where",
			"SELECT * FROM tbl_proyectos_t WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where)",
			"SELECT * FROM tbl_proyectos_p WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where)",
			"SELECT * FROM tbl_comentarios WHERE id_aplicacion='".page2id("proyectos")."' AND id_registro IN (SELECT id FROM tbl_proyectos $where)",
			"SELECT * FROM tbl_contactos WHERE id_aplicacion='".page2id("proyectos")."' AND id_registro IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("contactos")."'='1'",
			"SELECT * FROM tbl_seguimientos WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("seguimientos")."'='1'",
			"SELECT * FROM tbl_incidencias WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("incidencias")."'='1'",
			"SELECT * FROM tbl_partes WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("partes")."'='1'",
			"SELECT * FROM tbl_facturas WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("facturas")."'='1'",
			"SELECT * FROM tbl_facturas_c WHERE id_factura IN (SELECT id FROM tbl_facturas WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("facturas")."'='1')",
			"SELECT * FROM tbl_gastos WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("gastos")."'='1'",
			"SELECT * FROM tbl_agenda WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("agenda")."'='1'",
			"SELECT * FROM tbl_presupuestos WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("presupuestos")."'='1'",
			"SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_actas WHERE id_proyecto IN (SELECT id FROM tbl_proyectos $where) AND '".getParam("actas")."'='1'"
		);
		if($page=="posiblescli") $queryes=array(
			"SELECT * FROM tbl_posiblescli $where",
			"SELECT * FROM tbl_comentarios WHERE id_aplicacion='".page2id("posiblescli")."' AND id_registro IN (SELECT id FROM tbl_posiblescli $where)",
			"SELECT * FROM tbl_agenda WHERE id_posiblecli IN (SELECT id FROM tbl_posiblescli $where) AND '".getParam("agenda")."'='1'",
			"SELECT * FROM tbl_presupuestos WHERE id_posiblecli IN (SELECT id FROM tbl_posiblescli $where) AND '".getParam("presupuestos")."'='1'",
			"SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_posiblecli IN (SELECT id FROM tbl_posiblescli $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_posiblecli IN (SELECT id FROM tbl_posiblescli $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_actas WHERE id_posiblecli IN (SELECT id FROM tbl_posiblescli $where) AND '".getParam("actas")."'='1'"
		);
		if($page=="campanyas") $queryes=array(
			"SELECT * FROM tbl_campanyas $where",
			"SELECT * FROM tbl_comentarios WHERE id_aplicacion='".page2id("campanyas")."' AND id_registro IN (SELECT id FROM tbl_campanyas $where)",
			"SELECT * FROM tbl_posiblescli WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("posiblescli")."'='1'",
			"SELECT * FROM tbl_clientes WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("clientes")."'='1'",
			"SELECT * FROM tbl_agenda WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("agenda")."'='1'",
			"SELECT * FROM tbl_presupuestos WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("presupuestos")."'='1'",
			"SELECT * FROM tbl_presupuestos_t WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_presupuestos_p WHERE id_presupuesto IN (SELECT id FROM tbl_presupuestos WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("presupuestos")."'='1')",
			"SELECT * FROM tbl_actas WHERE id_campanya IN (SELECT id FROM tbl_campanyas $where) AND '".getParam("actas")."'='1'"
		);
		if($page=="correo") $queryes=array(
			"SELECT * FROM tbl_correo $where",
			"SELECT * FROM tbl_correo_a WHERE id_correo IN (SELECT id FROM tbl_correo $where)",
			"SELECT * FROM tbl_ficheros WHERE id_registro IN (SELECT id FROM tbl_correo $where) AND id_aplicacion='".page2id("correo")."'"
		);
		if($page=="feeds") $queryes=array(
			"SELECT * FROM tbl_feeds $where"
		);
		if(!isset($queryes)) action_denied();
		$result=array();
		foreach($queryes as $query) $result[]=execute_query($query);
		if(!count($result)) action_denied();
		$hash=md5(serialize($result));
		// GET THE TITLE
		$query="";
		if($page=="facturas") $query="SELECT
				CASE num WHEN '' THEN
					CONCAT('".LANG("albaran")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre)
				ELSE
					CONCAT('".LANG("factura")."',' ',num,' ',nombre)
				END subject
			FROM tbl_facturas $where";
		if($page=="actas") $query="SELECT
				CONCAT('".LANG("acta")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject
			FROM tbl_actas $where";
		if($page=="partes") $query="SELECT
				CONCAT('".LANG("parte")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',tarea) subject
			FROM tbl_partes $where";
		if($page=="presupuestos") $query="SELECT
				CONCAT('".LANG("presupuesto")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject
			FROM tbl_presupuestos $where";
		if($page=="clientes") $query="SELECT
				CONCAT('".LANG("cliente")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject
			FROM tbl_clientes $where";
		if($page=="proyectos") $query="SELECT
				CONCAT('".LANG("proyecto")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject
			FROM tbl_proyectos $where";
		if($page=="posiblescli") $query="SELECT
				CONCAT('".LANG("posiblecli")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject
			FROM tbl_posiblescli $where";
		if($page=="campanyas") $query="SELECT
				CONCAT('".LANG("campanya")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',nombre) subject
			FROM tbl_campanyas $where";
		if($page=="correo") $query="SELECT
				CONCAT('".LANG("correo","menu")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',subject) subject
			FROM tbl_correo $where";
		if($page=="feeds") $query="SELECT
				CONCAT('".LANG("feed","feeds")."',' ',LPAD(id,".intval(CONFIG("zero_padding_digits")).",0),' ',title) subject
			FROM tbl_feeds $where";
		if($query=="") action_denied();
		$result=db_query($query);
		$numrows=db_num_rows($result);
		$row=db_fetch_row($result);
		db_free($result);
		if(!$numrows) action_denied();
		$subject=($numrows==1)?$row["subject"]:LANG($page,"menu");
		// CREAR THUMBS SI ES NECESARIO
		$temp=get_directory("dirs/cachedir");
		$cache="$temp$hash.pdf";
		if(!file_exists($cache)) {
			// CREAR DEFAULT PDF
			$action="pdf";
			setParam("action",$action);
			ob_start();
			if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
			include("php/action/pdf.php");
			$pdf=ob_get_clean();
			file_put_contents($cache,$pdf);
		}
		// PREPARAR REPORT
		$data=base64_encode(file_get_contents($cache));
		set_array($_RESULT["rows"],"row",array("title"=>$subject,"hash"=>$hash,"data"=>$data));
	} elseif(getParam("page") && getParam("id") && getParam("cid")) {
		// CREATE REPORT FROM DOWNLOAD
		$action="download";
		setParam("action",$action);
		ob_start();
		if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
		include("php/action/download.php");
		$data=ob_get_clean();
		$hash=md5(serialize(array(md5($data),$name,$size,$type))); // MD5 INSIDE AS MEMORY TRICK
		// CREAR THUMBS SI ES NECESARIO
		$temp=get_directory("dirs/cachedir");
		$cache="$temp$hash.pdf";
		if(!file_exists($cache)) {
			include("php/unoconv.php");
			if(!file_exists($file)) {
				$file=get_cache_file($file);
				file_put_contents($file,$data);
			}
			file_put_contents($cache,unoconv2pdf($file));
		}
		// PREPARAR REPORT
		if(file_exists($cache)) {
			$data=base64_encode(file_get_contents($cache));
			set_array($_RESULT["rows"],"row",array("title"=>$name,"hash"=>$hash,"data"=>$data));
		}
	}
	// PREPARE THE OUTPUT
	$_RESULT["rows"]=array_values($_RESULT["rows"]);
	$buffer=json_encode($_RESULT);
	// CONTINUE
	output_handler(array(
		"data"=>$buffer,
		"type"=>"application/json",
		"cache"=>false
	));
}

?>