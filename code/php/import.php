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
/*
	Name:
		__import_find_chars
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
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

/*
	Name:
		__import_find_query
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
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

/*
	Name:
		import_file
	Abstract:
		This function is intended to import data in the supported formats
	Input:
		Array
		- file: local filename used to load the data
		- type: can be xml, csv, xls, bytes or edi
		- sep: separator char used only by csv format
		- sheet: sheet that must to be read
		- nodes: an array with the fields that define each nodes used in the tree construction
		- prefn: function executed between the load and the tree construction
		- postfn: function executed after the tree construction
		- notree: boolean to enable or disable the array2tree function
		- nohead: if the first row doesn't contains the header of the data, put this field to one
		- noletter: if you want to use numeric index instead of excel index, put this field to one
		- offset: the offset added to the start position in each map field
	Output:
		This function returns an array with the loaded data from file
		Can return a matrix or tree, depending the nodes parameter
*/
function import_file($args) {
	// CHECK PARAMETERS
	if(isset($args["data"])) {
		$args["file"]=get_cache_file($args["data"],"tmp");
		if(!file_exists($args["file"])) file_put_contents($args["file"],$args["data"]);
	}
	if(!isset($args["file"])) show_php_error(array("phperror"=>"Unknown file"));
	if(!isset($args["type"])) show_php_error(array("phperror"=>"Unknown type"));
	if(!isset($args["sep"])) $args["sep"]=";";
	if(!isset($args["sheet"])) $args["sheet"]=0;
	if(!isset($args["nodes"])) $args["nodes"]=array();
	if(!isset($args["prefn"])) $args["prefn"]="";
	if(!isset($args["postfn"])) $args["postfn"]="";
	if(!isset($args["notree"])) $args["notree"]=0;
	if(!isset($args["nohead"])) $args["nohead"]=0;
	if(!isset($args["noletter"])) $args["noletter"]=0;
	if(!isset($args["offset"])) $args["offset"]=0;
	if(!file_exists($args["file"])) return "Error: File '${args["file"]}' not found";
	// CONTINUE
	switch($args["type"]) {
		case "application/xml":
		case "text/xml":
		case "xml":
			$array=__import_xml2array($args["file"]);
			break;
		case "text/plain":
		case "text/csv":
		case "csv":
			$array=__import_csv2array($args["file"],$args["sep"]);
			break;
		case "application/wps-office.xls":
		case "application/vnd.ms-excel":
		case "application/excel":
		case "excel":
		case "xlsx":
		case "xls":
			$array=__import_xls2array($args["file"],$args["sheet"]);
			break;
		case "bytes":
			$array=__import_bytes2array($args["file"],$args["map"],$args["offset"]);
			break;
		case "edi":
			$array=__import_edi2array($args["file"]);
			break;
		default:
			show_php_error(array("phperror"=>"Unknown type '${args["type"]}' for file '${args["file"]}'"));
	}
	if(!is_array($array)) return $array;
	if($args["prefn"]) $array=$args["prefn"]($array,$args);
	if(!is_array($array)) return $array;
	if(!$args["notree"]) $array=__import_array2tree($array,$args["nodes"],$args["nohead"],$args["noletter"]);
	if($args["postfn"]) $array=$args["postfn"]($array,$args);
	return $array;
}

/*
	Name:
		__import_utf8bom
	Abstract:
		This function remove the bom header of the string
	Input:
	    The data that must to be checked
	Output:
		The data without the bom characters
*/
function __import_utf8bom($data) {
	if(substr($data,0,3)=="\xef\xbb\xbf") $data=substr($data,3);
	return $data;
}

/*
	Name:
		__import_xml2array
	Abstract:
		This function convert an xml into an array
	Input:
	    The file that contains the xml
	Output:
		An array with the contents of the xml
*/
function __import_xml2array($file) {
	$xml=file_get_contents($file);
	$xml=__import_utf8bom($xml);
	capture_next_error();
	$data=xml2struct($xml);
	$error=get_clear_error();
	//~ if($error!="") return $error;
	if($error!="") return "Error: XML not well-formed";
	$data=array_reverse($data);
	$array=__import_struct2array($data);
	return $array;
}

/*
	Name:
		__import_struct2array
	Abstract:
		This function is a helper of the __import_xml2array
	Input:
	    An array with all nodes of the xml file
	Output:
		An array with the correct structure that matches the xml structure
*/
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
		} elseif($type=="complete" && $value=="") {
			// caso 3 <algo/>
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="complete" && $value!="") {
			// caso 4 <algo>algo</algo>
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="cdata") {
			// NOTHING TO DO
		} else {
			xml_error("Unknown tag type with name '&lt;/$name&gt;'",$linea);
		}
	}
	return $array;
}

/*
	Name:
		__import_getnode
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_getnode($path,$array) {
	if(!is_array($path)) $path=explode("/",$path);
	$elem=array_shift($path);
	if(!is_array($array) || !isset($array[$elem])) return null;
	if(count($path)==0) return $array[$elem];
	return __import_getnode($path,__import_getvalue($array[$elem]));
}

/*
	Name:
		__import_getvalue
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_getvalue($array) {
	return (is_array($array) && isset($array["value"]) && isset($array["#attr"]))?$array["value"]:$array;
}

/*
	Name:
		__import_specialchars
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_specialchars($arg) {
	$orig=array("\\t","\\r","\\n");
	$dest=array("\t","\r","\n");
	return str_replace($orig,$dest,$arg);
}

/*
	Name:
		__import_csv2array
	Abstract:
		This function is a helper of the __import_xml2array
	Input:
	    An array with all nodes of the xml file
	Output:
		An array with the correct structure that matches the xml structure
*/
function __import_csv2array($file,$sep) {
	$sep=__import_specialchars($sep);
	$fd=fopen($file,"r");
	$array=array();
	while($row=fgetcsv($fd,0,$sep)) {
		foreach($row as $key=>$val) $row[$key]=getutf8($val);
		$array[]=$row;
	}
	fclose($fd);
	if(isset($array[0][0])) $array[0][0]=__import_utf8bom($array[0][0]);
	$array=__import_removevoid($array);
	return $array;
}

/*
	Name:
		__import_xls2array
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_xls2array($file,$sheet) {
	require_once("lib/phpspreadsheet/vendor/autoload.php");
	$objReader=PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
	// CHECK THE SHEET PARAM
	if(!method_exists($objReader,"listWorksheetNames")) return "Error: Sheets not found in the file";
	$sheets=$objReader->listWorksheetNames($file);
	if(is_numeric($sheet)) {
		if(!isset($sheets[$sheet])) return "Error: Sheet number '${sheet}' not found";
	} else {
		foreach($sheets as $sheet2=>$name2) {
			if($sheet==$name2) {
				$sheet=$sheet2;
				break;
			}
		}
		if(!is_numeric($sheet)) return "Error: Sheet named '${sheet}' not found";
	}
	// TRICK FOR A BIG FILES
	if(count($sheets)==1 && filesize($file)>1048576) { // filesize>1Mb
		require_once("php/unoconv.php");
		$temp=get_cache_file(file_get_contents($file),"csv");
		if(!file_exists($temp)) {
			__unoconv_convert($file,$temp,"csv");
		}
		if(file_exists($temp)) {
			unset($objReader);
			$array=__import_csv2array($temp,",");
			return $array;
		}
	}
	// CONTINUE
	$objPHPExcel=$objReader->load($file);
	$objSheet=$objPHPExcel->getSheet($sheet);
	// DETECT COLS AND ROWS WITH DATA
	$cells=$objSheet->getCoordinates(true);
	$cols=array();
	$rows=array();
	foreach($cells as $cell) {
		list($col,$row)=__import_cell2colrow($cell);
		$cols[$col]=__import_name2col($col);
		$rows[$row]=$row;
	}
	// IMPORTANT TRICK: TO ORDER THE COLS, WE NEEDED TO CONVERT IT INTO NUMBERS BEFORE TO DO THE REAL ORDER,
	// AND WHEN THE LIST HAS THE CORRECT ORDER, THEN WE CAN CONVERT IT TO THE ORIGINAL LETTERS
	sort($cols,SORT_NUMERIC);
	sort($rows,SORT_NUMERIC);
	foreach($cols as $key=>$val) $cols[$key]=__import_col2name($val);
	// READ DATA
	$array=array();
	foreach($rows as $row) {
		$temp=array();
		foreach($cols as $col) {
			$cell=$objSheet->getCell($col.$row);
			if($cell->isFormula()) {
				$temp2=$cell->getOldCalculatedValue();
			} elseif(PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
				//~ $temp2=$cell->getValue();
				//~ $temp2=PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($temp2);
				//~ $temp2=date("Y-m-d",$temp2);
				$cell->getStyle()->getNumberFormat()->setFormatCode("YYYY-MM-DD");
				$temp2=$cell->getFormattedValue();
			} else {
				$temp2=$cell->getFormattedValue();
			}
			$temp[]=$temp2;
		}
		$array[]=$temp;
	}
	// RELEASE MEMORY
	unset($objReader);
	unset($objPHPExcel);
	unset($objSheet);
	// CONTINUE
	$array=__import_removevoid($array);
	return $array;
}

/*
	Name:
		__import_bytes2array
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_bytes2array($file,$map,$offset) {
	if(!is_array($map)) {
		$map=trim($map);
		$map=explode("\n",$map);
		foreach($map as $key=>$val) {
			$val=trim($val);
			$val=explode(";",$val);
			$map[$key]=$val;
		}
	}
	$lines=file($file,FILE_IGNORE_NEW_LINES);
	if(isset($lines[0])) $lines[0]=__import_utf8bom($lines[0]);
	$array=array();
	$row=array();
	foreach($map as $map0) $row[]=$map0[0];
	$array[]=$row;
	foreach($lines as $line) {
		$line=getutf8($line);
		$row=array();
		foreach($map as $map0) $row[]=trim(mb_substr($line,$map0[1]+$offset,$map0[2]));
		$array[]=$row;
	}
	$array=__import_removevoid($array);
	return $array;
}

/*
	Name:
		__import_edi2array
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_edi2array($file) {
	require_once("lib/edifact/vendor/autoload.php");
	$parser=new EDI\Parser();
	$parser->load($file);
	$array=$parser->get();
	return $array;
}

/*
	Name:
		__import_removevoid
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
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

/*
	Name:
		__import_array2tree
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_array2tree($array,$nodes,$nohead,$noletter) {
	// INITIAL CHECKS
	if(!is_array($array)) return $array;
	if(!count($array)) return $array;
	// CHECK FOR DETECT REAL MATRIX OR TREES
	$valid=1;
	foreach($array as $key=>$val) {
		if(!is_numeric($key)) {
			$valid=0;
		} elseif(!is_array($val)) {
			$valid=0;
		} else {
			foreach($val as $key2=>$val2) {
				if(!is_numeric($key2)) {
					$valid=0;
				} elseif(is_array($val2)) {
					$valid=0;
				}
				if(!$valid) break;
			}
		}
		if(!$valid) break;
	}
	if(!$valid) return $array;
	// CONTINUE
	if($nohead) {
		$head=array();
		$num=1;
		foreach($array as $temp) $num=max($num,count($temp));
		for($i=0;$i<$num;$i++) $head[]=$noletter?$i:__import_col2name($i);
	} else {
		$head=array_shift($array);
	}
	// FIX FOR DUPLICATES AND SPACES
	$temp=array();
	foreach($head as $temp2) {
		$temp2=trim($temp2);
		set_array($temp,$temp2,"");
	}
	$head=array_keys($temp);
	// CONTINUE
	if(!is_array($nodes) || !count($nodes)) {
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
			$head2=__import_array_intersect($head,$node);
			if(count($head2)) {
				$line2=__import_array_intersect($line,$node);
				if(count($head2)>count($line2)) {
					$temp=array();
					foreach($head2 as $key=>$val) {
						$temp[$key]=isset($line2[$key])?$line2[$key]:"";
					}
					$line2=$temp;
				}
				$line3=array_combine($head2,$line2);
				$hash=md5(serialize($line3));
				$parts[$hash]=$line3;
			}
		}
		__import_array2tree_set($result,$parts);
	}
	$result=__import_array2tree_clean($result);
	return $result;
}

/*
	Name:
		__import_array_intersect
	Abstract:
		This function returns the same result that array_intersect_key($data,array_flip($filter))
		maintaining the order of the filter array.
	Input:
	    TODO
	Output:
		TODO
*/
function __import_array_intersect($data,$filter) {
	$result=array();
	foreach($filter as $field) if(isset($data[$field])) $result[$field]=$data[$field];
	return $result;
}

/*
	Name:
		__import_array2tree_set
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_array2tree_set(&$result,$parts) {
	$key=key($parts);
	$val=current($parts);
	unset($parts[$key]);
	if(count($parts)) {
		if(!isset($result[$key])) $result[$key]=array("row"=>$val,"rows"=>array());
		__import_array2tree_set($result[$key]["rows"],$parts);
	} else {
		set_array($result,$key,$val);
	}
}

/*
	Name:
		__import_array2tree_clean
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
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

/*
	Name:
		__import_tree2array
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_tree2array($array) {
	$result=array();
	foreach($array as $node) {
		if(isset($node["row"]) && isset($node["rows"])) {
			foreach(__import_tree2array($node["rows"]) as $row) {
				// FIX FOR DUPLICATES
				$temp=$node["row"];
				foreach($row as $key=>$val) set_array($temp,$key,$val);
				// CONTINUE
				$result[]=$temp;
			}
		} else {
			$result[]=$node;
		}
	}
	return $result;
}

/*
	Name:
		__import_col2name
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
// COPIED FROM http://www.php.net/manual/en/function.base-convert.php#94874
function __import_col2name($n) {
	if(is_array($n)) {
		foreach($n as $key=>$val) $n[$key]=__import_col2name($val);
		return $n;
	}
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
        $n -= pow(26, $i);
    }
    return $r;
}

/*
	Name:
		__import_name2col
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
// COPIED FROM http://www.php.net/manual/en/function.base-convert.php#94874
function __import_name2col($a) {
	if(is_array($a)) {
		foreach($a as $key=>$val) $a[$key]=__import_name2col($val);
		return $a;
	}
    $r = 0;
    $l = strlen($a);
    for ($i = 0; $i < $l; $i++) {
        $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
    }
    return $r - 1;
}

/*
	Name:
		__import_isname
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_isname($name) {
	$len=strlen($name);
	for($i=0;$i<$len;$i++) {
		if($name[$i]<'A' || $name[$i]>'Z') return false;
	}
	return true;
}

/*
	Name:
		__import_cell2colrow
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
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

/*
	Name:
		__import_getkeys
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_getkeys($array) {
	$result=array();
	if(isset($array[0])) {
		$node=$array[0];
		if(isset($node["row"]) && isset($node["rows"])) {
			$result=array_merge(array_keys($node["row"]),__import_getkeys($node["rows"]));
		} else {
			$result=array_keys($node);
		}
	}
	return $result;
}

/*
	Name:
		__import_make_table
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_make_table($array) {
	$result="";
	$result.="<table class='tabla width100'>\n";
	if(!is_array($array["data"])) {
		$result.="<tr>\n";
		$result.="<td class='thead ui-widget-header center ui-corner-top'></td>";
		$result.="</tr>\n";
		$result.="<tr>\n";
		$result.="<td class='tbody ui-widget-content center ui-corner-bottom nodata'>".$array["data"]."</td>";
		$result.="</tr>\n";
	} elseif(!count($array["data"])) {
		$result.="<tr>\n";
		$result.="<td class='thead ui-widget-header center ui-corner-top'></td>";
		$result.="</tr>\n";
		$result.="<tr>\n";
		$result.="<td class='tbody ui-widget-content center ui-corner-bottom nodata'>".LANG("nodata")."</td>";
		$result.="</tr>\n";
	} else {
		$head=(isset($array["data"]) && is_array($array["data"]) && count($array["data"]))?__import_getkeys($array["data"]):"";
		$limit=(isset($array["limit"]) && is_numeric($array["limit"]) && $array["limit"]>0)?$array["limit"]:0;
		$offset=(isset($array["offset"]) && is_numeric($array["offset"]) && $array["offset"]>0)?$array["offset"]:0;
		$width="";
		if(isset($array["width"])) {
			if(is_numeric($array["width"]) && $array["width"]>0) {
				$width=$array["width"];
			}
			if(is_array($array["width"])) {
				$width=array();
				foreach($array["width"] as $key=>$val) {
					$width[__import_name2col($key)]=$val;
				}
			}
		}
		$edit=array();
		if(isset($array["edit"]) && is_array($array["edit"]) && count($array["edit"])) {
			foreach($array["edit"] as $key=>$val) {
				if(!is_array($val)) $edit[__import_name2col($val)]="";
				if(is_array($val)) $edit[__import_name2col($key)]=$val;
			}
		}
		$first=1;
		foreach($array as $key=>$val) {
			$key=limpiar_key($key);
			if($key=="auto" && !is_array($val) && eval_bool($val)) {
				if(is_array($head)) {
					$last=count($head)-1;
					$result.="<tr>\n";
					$col=0;
					foreach($head as $col=>$field) {
						$noright=($col<$last)?"noright":"";
						$notop=(!$first)?"notop":"";
						$cornertl=($first && $col==0)?"ui-corner-tl":"";
						$cornertr=($first && $col==$last)?"ui-corner-tr":"";
						$result.="<td class='thead ui-widget-header center ${noright} ${notop} ${cornertl} ${cornertr}'>";
						$result.=__import_col2name($col);
						$result.="</td>\n";
						$col++;
					}
					$result.="</tr>\n";
					$first=0;
				}
			}
			if($key=="select" && is_array($val) && count($val)) {
				if(is_array($head)) {
					$last=count($head)-1;
					$result.="<tr>\n";
					$col=0;
					foreach($head as $col=>$field) {
						$name="col_".__import_col2name($col);
						$noright=($col<$last)?"noright":"";
						$notop=(!$first)?"notop":"";
						$cornertl=($first && $col==0)?"ui-corner-tl":"";
						$cornertr=($first && $col==$last)?"ui-corner-tr":"";
						$result.="<td class='tbody ui-widget-content center ${noright} ${notop} ${cornertl} ${cornertr}'>";
						$result.="<select class='ui-state-default ui-corner-all' name='${name}' ".__import_make_table_width($col,$width,12).">\n";
						$result.="<option value=''></option>\n";
						foreach($val as $index=>$option) {
							$selected=(isset($head[$index]) && $head[$index]==$option)?"selected":"";
							$result.="<option value='${option}' ${selected}>${option}</option>\n";
						}
						$result.="</select>";
						$result.="</td>\n";
						$col++;
					}
					$result.="</tr>\n";
					$first=0;
				}
			}
			if($key=="head" && !is_array($val) && eval_bool($val)) {
				if(is_array($head)) {
					$last=count($head)-1;
					$result.="<tr>\n";
					foreach($head as $col=>$field) {
						$noright=($col<$last)?"noright":"";
						$notop=(!$first)?"notop":"";
						$cornertl=($first && $col==0)?"ui-corner-tl":"";
						$cornertr=($first && $col==$last)?"ui-corner-tr":"";
						$result.="<td class='thead ui-widget-header center ${noright} ${notop} ${cornertl} ${cornertr}'>";
						$result.=limpiar_key($field);
						$result.="</td>\n";
					}
					$result.="</tr>\n";
					$first=0;
				}
			}
			if($key=="data" && is_array($val) && count($val)) {
				$result.=__import_make_table_rec($val,$limit,$offset,$edit,$width);
			}
		}
	}
	$result.="</table>\n";
	return $result;
}

/*
	Name:
		__import_make_table_width
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_make_table_width($col,$width,$extra=0) {
	if(is_array($width)) {
		if(!isset($width[$col])) return "";
		return __import_make_table_width($col,$width[$col],$extra);
	}
	return ($width!=""?"style='width:".($width+$extra)."px'":"");
}

/*
	Name:
		__import_make_table_rowspan
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_make_table_rowspan($array) {
	$result=0;
	foreach($array as $node) {
		if(isset($node["row"]) && isset($node["rows"])) {
			$result+=__import_make_table_rowspan($node["rows"]);
		} else {
			$result++;
		}
	}
	return $result;
}

/*
	Name:
		__import_make_table_trs
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_make_table_trs($action) {
	static $open=0;
	$result="";
	if($action=="open" && !$open) {
		$result="<tr>\n";
		$open=1;
	}
	if($action=="close" && $open) {
		$result="</tr>\n";
		$open=0;
	}
	return $result;
}

/*
	Name:
		__import_make_table_rec
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_make_table_rec($array,$limit,$offset,$edit,$width,$class="",$depth=0,$path="") {
	static $classes=array("ui-widget-content","ui-state-default");
	$result="";
	$lines=0;
	foreach($array as $key=>$node) {
		if(!$depth) $class=$classes[$lines%2];
		$result.=__import_make_table_trs("open");
		if(isset($node["row"]) && isset($node["rows"])) {
			$rowspan=__import_make_table_rowspan($node["rows"]);
			$result.=__import_make_table_row($node["row"],$class,$rowspan,$depth,$depth+count($node["row"]),$edit,$width,$path."/row/".$key);
			$result.=__import_make_table_rec($node["rows"],$limit,$offset,$edit,$width,$class,$depth+count($node["row"]),$path."/row/".$key);
		} else {
			$result.=__import_make_table_row($node,$class,1,$depth,$depth+count($node)-1,$edit,$width,$path."/row/".$key);
		}
		$result.=__import_make_table_trs("close");
		$lines++;
		if(!$depth && $offset && $lines<=$offset) $result="";
		if(!$depth && $limit && $lines>=$offset+$limit) break;
	}
	if(!$depth) {
		$corners=array("ui-corner-bl-disabled"=>"ui-corner-bl","ui-corner-br-disabled"=>"ui-corner-br");
		foreach($corners as $key=>$val) {
			$pos=strrpos($result,$key);
			$result=substr_replace($result,$val,$pos,strlen($key));
			$result=str_replace($key,"",$result);
		}
	}
	return $result;
}

/*
	Name:
		__import_make_table_row
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_make_table_row($row,$class,$rowspan,$depth,$last,$edit,$width,$path) {
	$result="";
	$col=0;
	foreach($row as $key=>$field) {
		$noright=($depth+$col<$last)?"noright":"";
		$cornerbl=($depth+$col==0)?"ui-corner-bl-disabled":"";
		$cornerbr=($depth+$col==$last)?"ui-corner-br-disabled":"";
		$result.="<td class='tbody ${class} ${noright} notop nowrap ${cornerbl} ${cornerbr}' rowspan='${rowspan}' ".__import_make_table_width($depth+$col,$width).">";
		if(isset($edit[$depth+$col])) {
			$name=$path."/col/".$col;
			$options=$edit[$depth+$col];
			if(is_array($options)) {
				$result.="<select class='ui-state-default ui-corner-all importsave' name='${name}' ".__import_make_table_width($depth+$col,$width,12).">";
				foreach($options as $value=>$label) $result.="<option value='${value}' ".($value==$field?"selected='true'":"").">${label}</option>";
				$result.="</select>";
			} else {
				$result.="<input type='text' class='ui-state-default ui-corner-all importsave' name='${name}' value='${field}' ".__import_make_table_width($depth+$col,$width)."/>";
			}
		} else {
			if(substr($field,0,4)=="tel:") {
				$field=explode(":",$field,2);
				$result.="<a href='javascript:void(0)' onclick='qrcode2(\"${field[1]}\")'>${field[1]}</a>";
			} elseif(substr($field,0,7)=="mailto:") {
				$field=explode(":",$field,2);
				$result.="<a href='javascript:void(0)' onclick='mailto(\"${field[1]}\")'>${field[1]}</a>";
			} elseif(substr($field,0,5)=="href:") {
				$field=explode(":",$field,2);
				$result.="<a href='javascript:void(0)' onclick='openwin(\"${field[1]}\")'>${field[1]}</a>";
			} elseif(substr($field,0,5)=="link:") {
				$field=explode(":",$field,3);
				$result.="<a href='javascript:void(0)' onclick='${field[1]}'>${field[2]}</a>";
			} else {
				$result.=$field;
			}
		}
		$result.="</td>\n";
		$col++;
	}
	return $result;
}

/*
	Name:
		__import_filter
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_filter($array,$filter,$eval=0) {
	$result=array();
	foreach($array as $node) {
		if(__import_filter_rec($node,$filter,$eval)) $result[]=$node;
	}
	return $result;
}

/*
	Name:
		__import_filter_rec
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_filter_rec($node,$filter,$eval,$parent=array()) {
	if(isset($node["row"]) && isset($node["rows"])) {
		// NORMAL FILTER
		foreach($node["row"] as $val) {
			if(stripos($val,$filter)!==false) return true;
		}
		// EVAL FILTER
		if($eval) {
			$vars=array_merge($parent,array_values($node["row"]));
			$keys=array_keys($vars);
			foreach($keys as $key=>$val) $keys[$key]=__import_col2name($val);
			$vars=array_combine($keys,$vars);
			capture_next_error();
			$result=eval_protected($filter,$vars);
			$error=get_clear_error();
			if($result && !$error) return true;
		}
		// RECURSIVE CALL
		foreach($node["rows"] as $node2) {
			if(__import_filter_rec($node2,$filter,$eval,array_merge($parent,array_values($node["row"])))) return true;
		}
	} else {
		// NORMAL FILTER
		foreach($node as $val) {
			if(stripos($val,$filter)!==false) return true;
		}
		// EVAL FILTER
		if($eval) {
			$vars=array_merge($parent,array_values($node));
			$keys=array_keys($vars);
			foreach($keys as $key=>$val) $keys[$key]=__import_col2name($val);
			$vars=array_combine($keys,$vars);
			capture_next_error();
			$result=eval_protected($filter,$vars);
			$error=get_clear_error();
			if($result && !$error) return true;
		}
	}
}

/*
	Name:
		__import_apply_patch
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_apply_patch(&$array,$key,$val) {
	$key=explode("/",$key);
	$key=array_reverse($key);
	array_pop($key);
	__import_apply_patch_rec($array,$key,$val);
}

/*
	Name:
		__import_apply_patch_rec
	Abstract:
		TODO
	Input:
	    TODO
	Output:
		TODO
*/
function __import_apply_patch_rec(&$array,$key,$val) {
	$key0=array_pop($key);
	$key1=array_pop($key);
	if($key0=="row") {
		if(isset($array["rows"][$key1])) {
			__import_apply_patch_rec($array["rows"][$key1],$key,$val);
		} elseif(isset($array[$key1])) {
			__import_apply_patch_rec($array[$key1],$key,$val);
		} else {
			show_php_error(array("phperror"=>"Path '${key0}' for '${key1}' not found"));
		}
	} elseif($key0=="col") {
		if(isset($array["row"]) && isset($array["rows"])) {
			$col=0;
			foreach($array["row"] as $key2=>$val2) {
				if($col==$key1) $array["row"][$key2]=$val;
				$col++;
			}
		} else {
			$col=0;
			foreach($array as $key2=>$val2) {
				if($col==$key1) $array[$key2]=$val;
				$col++;
			}
		}
	} else {
		show_php_error(array("phperror"=>"Unknown '${key0}' for '${key1}'"));
	}
}

function __import_make_table_ascii($array) {
	if(!is_array($array["rows"])) {
		$array["rows"]=array(array($array["rows"]));
		$array["head"]=0;
	}
	if(!count($array["rows"])) {
		$array["rows"]=array(array(LANG("nodata")));
		$array["head"]=0;
	}
	$rows=isset($array["rows"])?$array["rows"]:array();
	$head=isset($array["head"])?$array["head"]:1;
	$compact=isset($array["compact"])?$array["compact"]:0;
	$widths=array();
	$aligns=array();
	if($head) array_unshift($rows,array_combine(array_keys($rows[0]),array_keys($rows[0])));
	foreach($rows as $row) {
		foreach($row as $key=>$val) {
			if(!isset($widths[$key])) $widths[$key]=0;
			$widths[$key]=max(mb_strlen($val),$widths[$key]);
			if(!isset($aligns[$key])) $aligns[$key]="L";
			if(is_numeric($val)) $aligns[$key]="R";
			if(substr($val,-1,1)=="%") $aligns[$key]="R";
			if(substr($val,-1,1)=="€") $aligns[$key]="R";
		}
	}
	foreach($widths as $width) echo "+".str_repeat("-",$width+($compact?0:2));
	echo "+\n";
	foreach($rows as $index=>$row) {
		if($index==1 && $head) {
			foreach($widths as $width) echo "+".str_repeat("-",$width+($compact?0:2));
			echo "+\n";
		}
		foreach($row as $key=>$val) {
			echo "|";
			if($aligns[$key]=="R") echo str_repeat(" ",$widths[$key]-mb_strlen($val));
			echo ($compact?"":" ").$val.($compact?"":" ");
			if($aligns[$key]=="L") echo str_repeat(" ",$widths[$key]-mb_strlen($val));
		}
		echo "|\n";
	}
	foreach($widths as $width) echo "+".str_repeat("-",$width+($compact?0:2));
	echo "+";
	$buffer=ob_get_clean();
	return $buffer;
}
?>
