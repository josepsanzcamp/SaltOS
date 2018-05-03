<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2018 by Josep Sanz Campderrós
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

/*
	Name:
		export_file
	Abstract:
		This function is intended to export data in the supported formats
	Input:
		Array
		- type: can be xml, csv or excel
		- data: the matrix to export
		- sep: separator char used only by csv format
		- eol: enf of line char used only by csv format
		- title: title used only by excel format
		- type2: type used only by excel format, can be Excel5 or Excel2007
		- file: local filename used to store the results
	Output:
		if file argument is specified, void string is returned
		if file argument is not specified, then they will returns all data
*/
function export_file($args) {
	//~ echo "<pre>".sprintr($args)."</pre>";die();
	// CHECK PARAMETERS
	if(!isset($args["type"])) show_php_error(array("phperror"=>"Unknown type"));
	if(!isset($args["data"])) show_php_error(array("phperror"=>"Unknown data"));
	if(!isset($args["sep"])) $args["sep"]=";";
	if(!isset($args["eol"])) $args["eol"]="\r\n";
	if(!isset($args["title"])) $args["title"]="";
	if(!isset($args["type2"])) $args["type2"]="Excel5";
	if(!in_array($args["type2"],array("Excel5","Excel2007"))) show_php_error(array("phperror"=>"Unknown type2 '${args["type2"]}'"));
	if(!isset($args["file"])) $args["file"]="";
	// CONTINUE
	switch($args["type"]) {
		case "application/xml":
		case "text/xml":
		case "xml":
			$buffer=__export_file_xml($args["data"]);
			break;
		case "text/plain":
		case "text/csv":
		case "csv":
			$buffer=__export_file_csv($args["data"],$args["sep"],$args["eol"]);
			break;
		case "application/vnd.ms-excel":
		case "application/excel":
		case "excel":
		case "xlsx":
		case "xls":
			$buffer=__export_file_excel($args["data"],$args["title"]);
			break;
		default:
			show_php_error(array("phperror"=>"Unknown type '${args["type"]}' for file '${args["file"]}'"));
	}
	if($args["file"]!="") {
		file_put_contents($file,$buffer);
		return "";
	}
	return $buffer;
}

/*
	Name:
		__export_file_xml
	Abstract:
		This function is intended to export data in xml format
	Input:
		Array
		- matrix: the matrix to export
	Output:
		They will returns all data
*/
function __export_file_xml($matrix) {
	require_once("php/array2xml.php");
	$buffer=__XML_HEADER__;
	$buffer.=__array2xml_write_nodes($matrix,0);
	return $buffer;
}

/*
	Name:
		__export_file_csv
	Abstract:
		This function is intended to export data in csv format
	Input:
		Array
		- matrix: the matrix to export
		- sep: separator char
		- eol: enf of line char
	Output:
		They will returns all data
*/
function __export_file_csv($matrix,$sep=";",$eol="\r\n") {
	$buffer=array();
	foreach($matrix as $key=>$val) {
		foreach($val as $key2=>$val2) {
			if(strpos($val2,$sep)!==false) $val[$key2]='"'.$val2.'"';
		}
		$buffer[]=implode($sep,$val);
	}
	$buffer=implode($eol,$buffer);
	return $buffer;
}

/*
	Name:
		__export_file_excel
	Abstract:
		This function is intended to export data in excel format
	Input:
		Array
		- matrix: the matrix to export
		- title: title used in the excel file
		- type: can be Excel5 or Excel2007
	Output:
		They will returns all data
*/
function __export_file_excel($matrix,$title="",$type="Excel5") {
	set_include_path("lib/phpexcel".PATH_SEPARATOR.get_include_path());
	require_once("PHPExcel.php");
	$cacheMethod=PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
	$cacheSettings=array("memoryCacheSize"=>"8MB");
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
	$objPHPExcel=new PHPExcel();
	$objPHPExcel->getProperties()->setCreator(get_name_version_revision());
	$objPHPExcel->getProperties()->setLastModifiedBy(current_datetime());
	if($title!="") {
		$objPHPExcel->getProperties()->setTitle($title);
		$objPHPExcel->getProperties()->setSubject($title);
		$objPHPExcel->getProperties()->setDescription($title);
		$objPHPExcel->getProperties()->setKeywords($title);
		$objPHPExcel->getProperties()->setCategory($title);
	}
	//~ $objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->fromArray($matrix,NULL,"A1");
	require_once("php/import.php");
	for($i=0;$i<count($matrix[0]);$i++) $objPHPExcel->getActiveSheet()->getColumnDimension(__import_col2name($i))->setAutoSize(true);
	if($title!="") {
		$objPHPExcel->getActiveSheet()->setTitle(substr($title,0,31));
	}
	$objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,$type);
	ob_start();
	$objWriter->save("php://output");
	$buffer=ob_get_clean();
	return $buffer;
}
?>