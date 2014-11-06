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
if(!check_user()) action_denied();
if(getParam("action")=="excel") {
	// FUNCTIONS
	function __xls_dump($query,$page) {
		$result=db_query($query);
		$matrix=array(array());
		for($i=0;$i<db_num_fields($result);$i++) $matrix[0][]=db_field_name($result,$i);
		while($row=db_fetch_row($result)) $matrix[]=array_values($row);
		db_free($result);
		set_include_path("lib/phpexcel:".get_include_path());
		require_once("PHPExcel.php");
		$cacheMethod=PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings=array("memoryCacheSize"=>"8MB");
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
		$objPHPExcel=new PHPExcel();
		$objPHPExcel->getProperties()->setCreator(get_name_version_revision());
		$objPHPExcel->getProperties()->setLastModifiedBy(current_datetime());
		$title=ucfirst($page);
		$objPHPExcel->getProperties()->setTitle($title);
		$objPHPExcel->getProperties()->setSubject($title);
		$objPHPExcel->getProperties()->setDescription($title);
		$objPHPExcel->getProperties()->setKeywords($title);
		$objPHPExcel->getProperties()->setCategory($title);
		//~ $objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->fromArray($matrix,NULL,"A1");
		require_once("php/import.php");
		for($i=0;$i<count($matrix[0]);$i++) $objPHPExcel->getActiveSheet()->getColumnDimension(__import_col2name($i))->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->setTitle(substr($title,0,31));
		if(!defined("__CANCEL_HEADER__")) {
			header_powered();
			header_expires(false);
			header("Content-Type: application/x-excel");
			$name=$page.getDefault("exts/excelext",".xls");
			header("Content-Disposition: attachment; filename=\"$name\"");
		}
		$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,"Excel5");
		$objWriter->save("php://output");
		if(!defined("__CANCEL_DIE__")) die();
	}
	// CONTINUE
	$_LANG["default"]="$page,menu,common";
	if(!file_exists("xml/${page}.xml")) action_denied();
	$config=xml2array("xml/${page}.xml");
	if(!isset($config[$action])) action_denied();
	$config=$config[$action];
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump(false);
	$config=eval_attr($config);
	if(eval_bool(getDefault("debug/actiondebug"))) debug_dump();
	$oldcache=set_use_cache("false");
	$query=$config["query"];
	__xls_dump($query,$page);
	set_use_cache($oldcache);
	if(!defined("__CANCEL_DIE__")) die();
}
?>