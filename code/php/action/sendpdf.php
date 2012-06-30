<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2011 by Josep Sanz Campderrós
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
if(in_array($page,array("facturas","actas","partes","presupuestos"))) {
	require_once("php/getmail.php");
	require_once("php/defines.php");
	// DATOS FACTURA/ACTA/PARTE/PRESUPUESTO
	$where="WHERE id IN (".check_ids(getParam("id")).")";
	if($page=="facturas") $query="
		SELECT
			CASE num WHEN '' THEN
				/*MYSQL CONCAT('".LANG("albaran")."',' ',LPAD(id,5,0),' ',nombre) */
				/*SQLITE '".LANG("albaran")."' || ' ' || SUBSTR('00000' || id,-5,5) || ' ' || nombre */
			ELSE
				/*MYSQL CONCAT('".LANG("factura")."',' ',num,' ',nombre) */
				/*SQLITE '".LANG("factura")."' || ' ' || num || ' ' || nombre */
			END subject,id
		FROM tbl_facturas $where";
	if($page=="actas") $query="
		SELECT
			/*MYSQL CONCAT('".LANG("acta")."',' ',LPAD(id,5,0),' ',nombre) */
			/*SQLITE '".LANG("acta")."' || ' ' || SUBSTR('00000' || id,-5,5) || ' ' || nombre */ subject,id
		FROM tbl_actas $where";
	if($page=="partes") $query="
		SELECT
			/*MYSQL CONCAT('".LANG("parte")."',' ',LPAD(id,5,0),' ',tarea) */
			/*SQLITE '".LANG("parte")."' || ' ' || SUBSTR('00000' || id,-5,5) || ' ' || tarea */ subject,id
		FROM tbl_partes $where";
	if($page=="presupuestos") $query="
		SELECT
			/*MYSQL CONCAT('".LANG("presupuesto")."',' ',LPAD(id,5,0),' ',nombre) */
			/*SQLITE '".LANG("presupuesto")."' || ' ' || SUBSTR('00000' || id,-5,5) || ' ' || nombre */ subject,id
		FROM tbl_presupuestos $where";
	$result=db_query($query);
	$numrows=db_num_rows($result);
	if(!$numrows) action_denied();
	$ids=array();
	$body=array();
	if($numrows==1) {
		$row=db_fetch_row($result);
		$subject=$row["subject"];
		$ids[]=$row["id"];
		$body[]=$row["subject"];
	} else {
		$subject=LANG($page,"menu");
		while($row=db_fetch_row($result)) {
			$ids[]=$row["id"];
			$body[]=$row["subject"];
		}
	}
	db_free($result);
	$files=array();
	foreach($ids as $key=>$val) {
		// PDF FACTURA/ACTA/PARTE/PRESUPUESTO
		$action="pdf";
		setParam("action",$action);
		$_LANG["default"]="$page,menu,common";
		$_CONFIG[$page]=xml2array("xml/$page.xml");
		$_GET["id"]=$val;
		ob_start();
		if(!defined("__CANCEL_DIE__")) define("__CANCEL_DIE__",1);
		if(!defined("__CANCEL_HEADER__")) define("__CANCEL_HEADER__",1);
		include("php/default.php");
		$pdf=ob_get_clean();
		$file=get_temp_file(getDefault("exts/pdfext",".pdf"));
		file_put_contents($file,$pdf);
		$name=$body[$key].".pdf";
		//$name=str_replace(".","",$name);
		//$name=$name.".pdf";
		$name=encode_bad_chars_file($name);
		$mime="application/pdf";
		$size=__getmail_gethumansize(strlen($pdf));
		$files["pdf_${key}"]=array("file"=>$file,"name"=>$name,"mime"=>$mime,"size"=>$size);
	}
	$body=implode(__HTML_NEW_LINE__,$body);
	//$body=__TEXT_HTML_OPEN__.$body.__TEXT_HTML_CLOSE__;
	sess_init();
	$_SESSION["correo"]=array("subject"=>$subject,"body"=>$body,"files"=>$files);
	sess_close();
	// REBOTAR AL FORMULARIO DE REDACCION
	javascript_location_page("correo&action=form&id=0_session_0");
	die();
}
?>