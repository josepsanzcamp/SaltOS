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
function __import_find_chars($data,$pos,$chars) {
	$result=array();
	$len=strlen($chars);
	for($i=0;$i<$len;$i++) {
		$temp=strpos($data,$chars[$i],$pos);
		if($temp!==false) $result[]=$temp;
	}
	return count($result)?min($result):false;
}

function __import_find_query($data,$pos) {
	$len=strlen($data);
	$parentesis=0;
	$parser=1;
	$exists=0;
	$pos2=__import_find_chars($data,$pos,"\\'();");
	while($pos2!==false) {
		if($data[$pos2]=="\\") $pos2++;
		elseif($data[$pos2]=="'") $parser=!$parser;
		elseif($data[$pos2]=="(" && $parser) $parentesis++;
		elseif($data[$pos2]==")" && $parser) $parentesis--;
		elseif($data[$pos2]==";" && $parser && !$parentesis) { $exists=1; break; }
		if($pos2+1>=$len) break;
		$pos2=__import_find_chars($data,$pos2+1,"\\'();");
	}
	if(!$parser || $parentesis || !$exists) return 0;
	return $pos2-$pos;
}

function __import_importfile($id_importacion,$nodes=null) {
	$query="SELECT * FROM tbl_ficheros WHERE id_aplicacion='".page2id("importaciones")."' AND id_registro='".abs($id_importacion)."'";
	$row=execute_query($query);
	if($row===null) show_php_error(array("phperror"=>"Unknown import file (id_importacion='${id_importacion}')"));
	$file=get_directory("dirs/filesdir").$row["fichero_file"];
	$type=$row["fichero_type"];
	switch($type) {
		case "application/xml":
		case "text/xml":
			$array=__import_xml2array($file);
			break;
		case "text/plain":
		case "text/csv":
			$array=__import_csv2array($file,";");
			$array=__import_array2tree($array,$nodes);
			break;
		case "application/vnd.ms-excel":
		case "application/excel":
			$array=__import_xls2array($file,0);
			$array=__import_array2tree($array,$nodes);
			break;
		default:
			show_php_error(array("phperror"=>"Unknown type '${type}' for file '${file}' (id_importacion='${id_importacion}')"));
	}
	return $array;
}

function __import_xml2array($file) {
	$xml=file_get_contents($file);
	$data=xml2struct($xml);
	$data=array_reverse($data);
	$array=__import_struct2array($data);
	return $array;
}

function __import_struct2array(&$data) {
	$array=array();
	while($linea=array_pop($data)) {
		$name=$linea["tag"];
		$type=$linea["type"];
		$value="";
		if(isset($linea["value"])) $value=$linea["value"];
		$attr=array();
		if(isset($linea["attributes"])) $attr=$linea["attributes"];
		if($type=="open") {
			// caso 1 <algo>
			$value=__import_struct2array($data);
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="close") {
			// caso 2 </algo>
			return $array;
		} elseif($type=="complete") {
			if($value=="") {
				// caso 3 <algo/>
				if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
				set_array($array,$name,$value);
			} else {
				// caso 4 <algo>algo</algo>
				if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
				set_array($array,$name,$value);
			}
		} elseif($type=="cdata") {
			// NOTHING TO DO
		} else {
			xml_error("Unknown tag type with name '&lt;/$name&gt;'",$linea);
		}
	}
	return $array;
}

function __import_csv2array($file,$sep) {
	$fd=fopen($file,"r");
	$array=array();
	while($row=fgetcsv($fd,0,$sep)) {
		foreach($row as $key=>$val) $row[$key]=getutf8($val);
		$array[]=$row;
	}
	fclose($fd);
	$array=__import_removevoid($array);
	return $array;
}

function __import_xls2array($file,$sheet) {
	set_include_path("lib/phpexcel:".get_include_path());
	include_once("PHPExcel.php");
	$cacheMethod=PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
	$cacheSettings=array("memoryCacheSize"=>"8MB");
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
	$objReader=PHPExcel_IOFactory::createReaderForFile($file);
	$objReader->setReadDataOnly(true);
	$objPHPExcel=$objReader->load($file);
	$objSheet=$objPHPExcel->getSheet($sheet);
	// DETECT COLS AND ROWS WITH DATA
	$cells=$objSheet->getCellCollection(true);
	$cols=array();
	$rows=array();
	foreach($cells as $cell) {
		list($col,$row)=__import_cell2colrow($cell);
		$cols[$col]=$col;
		$rows[$row]=$row;
	}
	// READ DATA
	$array=array();
	foreach($rows as $row) {
		$temp=array();
		foreach($cols as $col) {
			$temp[]=$objSheet->getCell($col.$row)->getValue();
		}
		$array[]=$temp;
	}
	// RELEASE MEMORY
	unset($objFilter);
	unset($objReader);
	unset($objPHPExcel);
	unset($objSheet);
	// CONTINUE
	$array=__import_removevoid($array);
	return $array;
}

function __import_removevoid($array) {
	$count_rows=count($array);
	$rows=array_fill(0,$count_rows,0);
	$count_cols=0;
	foreach($array as $val) $count_cols=max($count_cols,count($val));
	$cols=array_fill(0,$count_cols,0);
	foreach($array as $key=>$val) {
		foreach($val as $key2=>$val2) {
			if($val2!="") {
				$rows[$key]++;
				$cols[$key2]++;
			}
		}
	}
	$rows=array_keys(array_intersect($rows,array(0)));
	$cols=array_keys(array_intersect($cols,array(0)));
	foreach($rows as $val) unset($array[$val]);
	$array=array_values($array);
	foreach($array as $key=>$val) {
		foreach($cols as $val2) unset($val[$val2]);
		$array[$key]=array_values($val);
	}
	return $array;
}

function __import_array2tree($array,$nodes) {
	if(!count($array)) return $array;
	$head=array_shift($array);
	if(!$nodes) {
		$nodes=array(range(0,count($head)-1));
	} else {
		$col=0;
		foreach($nodes as $key=>$val) {
			if(!is_array($val)) $val=explode(",",$val);
			$nodes[$key]=array();
			foreach($val as $key2=>$val2) {
				if(in_array($val2,$head)) $nodes[$key][$key2]=array_search($val2,$head);
				elseif(__import_isname($val2)) $nodes[$key][$key2]=__import_name2col($val2);
				elseif(!is_numeric($val2)) $nodes[$key][$key2]=$col;
				$col++;
			}
		}
	}
	$result=array();
	foreach($array as $line) {
		$parts=array();
		foreach($nodes as $node) {
			$node2=array_flip($node);
			$head2=array_intersect_key($head,$node2);
			if(count($head2)) {
				$line2=array_intersect_key($line,$node2);
				$line3=array_combine($head2,$line2);
				$hash=md5(serialize($line3));
				$parts[$hash]=$line3;
			}
		}
		__import_array2tree_setter($result,$parts);
	}
	$result=__import_array2tree_clean($result);
	return $result;
}

function __import_array2tree_setter(&$result,$parts) {
	$part=each($parts);
	$key=$part["key"];
	$val=$part["value"];
	unset($parts[$key]);
	if(count($parts)) {
		if(!isset($result[$key])) $result[$key]=array("row"=>$val,"rows"=>array());
		__import_array2tree_setter($result[$key]["rows"],$parts);
	} else {
		$result[$key]=$val;
	}
}

function __import_array2tree_clean($array) {
	$result=array();
	foreach($array as $node) {
		if(isset($node["row"]) && isset($node["rows"])) {
			$result[]=array("row"=>$node["row"],"rows"=>__import_array2tree_clean($node["rows"]));
		} else {
			$result[]=$node;
		}
	}
	return $result;
}

function __import_tree2array($array) {
	$result=array();
	foreach($array as $node) {
		if(isset($node["row"]) && isset($node["rows"])) {
			$void_row=array_fill_keys(array_keys($node["row"]),"");
			foreach(__import_tree2array($node["rows"]) as $line=>$row) {
				if($line==0) $result[]=array_merge($node["row"],$row);
				if($line!=0) $result[]=array_merge($void_row,$row);
			}
		} else {
			$result[]=$node;
		}
	}
	return $result;
}

// COPIED FROM http://www.php.net/manual/en/function.base-convert.php#94874
function __import_col2name($n) {
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
        $n -= pow(26, $i);
    }
    return $r;
}

// COPIED FROM http://www.php.net/manual/en/function.base-convert.php#94874
function __import_name2col($a) {
    $r = 0;
    $l = strlen($a);
    for ($i = 0; $i < $l; $i++) {
        $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
    }
    return $r - 1;
}

function __import_isname($name) {
	$len=strlen($name);
	for($i=0;$i<$len;$i++) {
		if($name[$i]<'A' || $name[$i]>'Z') return false;
	}
	return true;
}

function __import_cell2colrow($cell) {
	$col="";
	$row="";
	$len=strlen($cell);
	for($i=0;$i<$len;$i++) {
		if($cell[$i]>='A' && $cell[$i]<='Z') $col.=$cell[$i];
		if($cell[$i]>='0' && $cell[$i]<='9') $row.=$cell[$i];
	}
	return array($col,$row);
}

function __import_make_table($array) {
	if(isset($array["head"]) && is_array($array["head"]) && count($array["head"])) $head1=$array["head"];
	elseif(isset($array["data"]) && is_array($array["data"]) && count($array["data"])) $head2=$array["data"][0];
	if(isset($array["limit"]) && is_numeric($array["limit"]) && $array["limit"]>0) $limit=$array["limit"];
	$result="";
	$result.="<table class='tabla width100'>\n";
	foreach($array as $key=>$val) {
		$key=limpiar_key($key);
		if($key=="auto" && !is_array($val) && eval_bool($val)) {
			if(isset($head1) || isset($head2)) {
				$result.="<tr>\n";
				if(isset($head1)) $head=$head1;
				elseif(isset($head2)) $head=$head2;
				$col=0;
				foreach($head as $field) {
					$result.="<td class='thead center'>\n";
					$result.=__import_col2name($col);
					$result.="</td>\n";
					$col++;
				}
				$result.="</tr>\n";
			}
			unset($head);
		}
		if($key=="select" && is_array($val) && count($val)) {
			if(isset($head1) || isset($head2)) {
				$result.="<tr>\n";
				if(isset($head1)) $head=$head1;
				elseif(isset($head2)) $head=$head2;
				$col=0;
				foreach($head as $field) {
					$name=__import_col2name($col);
					if(isset($head1)) $field2="field_".strtolower($field);
					elseif(isset($head2)) $field2="col_".strtolower($name);
					$result.="<td class='tbody center'>\n";
					$result.="<select class='ui-state-default ui-corner-all' name='${field2}'>\n";
					$result.="<option value=''></option>\n";
					foreach($val as $index=>$option) {
						$selected="";
						if(isset($head1) && $option==$field) $selected="selected";
						elseif(isset($head2) && $index==$col) $selected="selected";
						$result.="<option value='${option}' ${selected}>${option}</option>\n";
					}
					$result.="</select>";
					$result.="</td>\n";
					$col++;
				}
				$result.="</tr>\n";
			}
			unset($head);
		}
		if($key=="head" && is_array($val) && count($val)) {
			$result.="<tr>\n";
			foreach($val as $field) {
				$result.="<td class='thead center'>\n";
				$result.=$field;
				$result.="</td>\n";
			}
			$result.="</tr>\n";
		}
		if($key=="data" && is_array($val) && count($val)) {
			$count=0;
			foreach($val as $line=>$row) {
				$result.="<tr>\n";
				foreach($row as $field) {
					$result.="<td class='tbody'>\n";
					$result.=$field;
					$result.="</td>\n";
				}
				$result.="</tr>\n";
				$count++;
				if(isset($limit) && $count>=$limit) break;
			}
		}
	}
	$result.="</table>\n";
	return $result;
}
?>