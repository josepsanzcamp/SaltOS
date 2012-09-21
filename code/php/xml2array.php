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
function begin_eval_control() {
	capture_next_error();
	ob_start();
}

function end_eval_control($value,$source="") {
	$error1=ob_get_clean();
	$error2=get_clear_error();
	$error=$error1?$error1:$error2;
	if($error) {
		//~ $value=is_array($value)?sprintr($value):$value;
		xml_error($error,$source);
	}
}

function leer_nodos(&$data,$file="") {
	$array=array();
	while($linea=array_pop($data)) {
		$name=$linea["tag"];
		$type=$linea["type"];
		$value="";
		if(isset($linea["value"])) $value=$linea["value"];
		$attr=array();
		if(isset($linea["attributes"])) $attr=$linea["attributes"];
		if($type=="open") {
			// CASE 1 <some>
			$value=leer_nodos($data,$file);
			$path="";
			$action="";
			foreach($attr as $key=>$val) {
				$key=limpiar_key($key);
				if($key=="path") {
					$path=$val;
				} elseif(in_array($key,array("before","after","replace","append","add","remove","delete"))) {
					if($action!="") xml_error("Detected '$action' and '$key' attr in the same node",$linea,"",$file);
					$action=$key;
				}
			}
			if($path && !$action) xml_error("Detected 'path' attr without 'before', 'after' or 'replace' attr",$linea,"",$file);
			if($action && !$path) xml_error("Detected '$action' attr without 'path' attr",$linea,"",$file);
			if($path) unset_array($attr,"path");
			if($action) unset_array($attr,$action);
			if($path) $array=__set_array_recursive($array,$path,$value,$action);
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			if(!$path) set_array($array,$name,$value);
		} elseif($type=="close") {
			// CASE 2 </some>
			return $array;
		} elseif($type=="complete" && $value=="") {
			// CASE 3 <some/>
			$include=0;
			$replace=0;
			foreach($attr as $key=>$val) {
				$key=limpiar_key($key);
				if($key=="include") {
					$value=xml2array($val,false);
					$include=1;
				} elseif($key=="replace") {
					$replace=eval_bool($val);
				}
			}
			if($replace && !$include) xml_error("Attr 'replace' not allowed without attr 'include'",$linea,"",$file);
			if($include) unset_array($attr,"include");
			if($replace) unset_array($attr,"replace");
			if(count($attr)) {
				if($replace) {
					if(is_array($value)) {
						foreach($value as $key=>$val) {
							if(is_array($val) && isset($val["value"]) && isset($val["#attr"])) {
								$value[$key]["#attr"]=$attr;
								foreach($val["#attr"] as $key2=>$val2) set_array($value[$key]["#attr"],$key2,$val2);
							} else {
								$value[$key]=array("value"=>$val,"#attr"=>$attr);
							}
						}
					}
				} else {
					$value=array("value"=>$value,"#attr"=>$attr);
				}
			}
			if($replace && is_array($value)) {
				foreach($value as $key=>$val) {
					set_array($array,$key,$val);
				}
			} else {
				set_array($array,$name,$value);
			}
		} elseif($type=="complete" && $value!="") {
			// CASE 4 <some>some</some>
			if(count($attr)) $value=array("value"=>$value,"#attr"=>$attr);
			set_array($array,$name,$value);
		} elseif($type=="cdata") {
			// NOTHING TO DO
		} else {
			xml_error("Unknown tag type with name '&lt;/$name&gt;'",$linea,"",$file);
		}
	}
	return $array;
}

function __set_array_recursive($array,$keys,$value,$type) {
	if(!is_array($keys)) $keys=explode("/",$keys);
	// RESOLVE NODE USING XPATH SYNTAX
	$path=explode("[",str_replace("]","",$keys[0]));
	$count=count($path);
	if($count>1) {
		for($i=1;$i<$count;$i++) {
			$path[$i]=explode("=",$path[$i],2);
			if(!isset($path[$i][1])) $path[$i][1]="";
		}
		$key=array();
		foreach($array as $key2=>$val2) {
			$valid=1;
			if(!in_array($path[0],array("","*",limpiar_key($key2)))) $valid=0;
			for($i=1;$i<$count && $valid;$i++) {
				if(!isset($val2[$path[$i][0]])) $valid=0;
				elseif($val2[$path[$i][0]]!=$path[$i][1]) $valid=0;
			}
			if($valid) $key[]=$key2;
		}
	} else {
		$key=array($keys[0]);
	}
	// CONTINUE
	$count=count($keys);
	if($count>1) {
		$temp=array();
		for($i=1;$i<$count;$i++) $temp[]=$keys[$i];
		foreach($key as $key2) {
			if(!isset($array[$key2])) xml_error("Undefined node: $key2");
			$array[$key2]=__set_array_recursive($array[$key2],$temp,$value,$type);
		}
	} elseif($count==1) {
		$temp=array();
		$hasattr=(count($array)==2 && isset($array["value"]) && isset($array["#attr"]));
		$array_value=$hasattr?$array["value"]:$array;
		$array_attr=$hasattr?$array["#attr"]:array();
		foreach($array_value as $key2=>$val2) {
			if(in_array($key2,$key)) {
				switch($type) {
					case "before":
						foreach($value as $key3=>$val3) set_array($temp,limpiar_key($key3),$val3);
						set_array($temp,limpiar_key($key2),$val2);
						break;
					case "after":
						set_array($temp,limpiar_key($key2),$val2);
						foreach($value as $key3=>$val3) set_array($temp,limpiar_key($key3),$val3);
						break;
					case "replace":
						foreach($value as $key3=>$val3) set_array($temp,limpiar_key($key3),$val3);
						break;
					case "append":
					case "add":
						$hasattr=(count($val2)==2 && isset($val2["value"]) && isset($val2["#attr"]));
						foreach($value as $key3=>$val3) {
							if($hasattr) {
								if(!is_array($val2["value"])) xml_error("Can not '$type' the node '$key3' to the node '$key2'");
								set_array($val2["value"],limpiar_key($key3),$val3);
							} else {
								if(!is_array($val2)) xml_error("Can not '$type' the node '$key3' to the node '$key2'");
								set_array($val2,limpiar_key($key3),$val3);
							}
						}
						set_array($temp,limpiar_key($key2),$val2);
						break;
					case "remove":
					case "delete":
						// NOTHING TO DO
						break;
					default:
						xml_error("Unknown type '$type' in __set_array_recursive");
						break;
				}
			} else {
				set_array($temp,limpiar_key($key2),$val2);
			}
		}
		$array=count($array_attr)?array("value"=>$temp,"#attr"=>$array_attr):$temp;
	} else {
		xml_error("Error in __set_array_recursive using count 0");
	}
	return $array;
}

function set_array(&$array,$name,$value) {
	$count="";
	$prefix="";
	if(isset($array[$name])) {
		$count=1;
		$prefix="#";
		while(isset($array[$name.$prefix.$count])) $count++;
	}
	$array[$name.$prefix.$count]=$value;
}

function unset_array(&$array,$name) {
	$len=strlen($name);
	foreach($array as $key=>$val) {
		if(substr($key,0,$len)==$name) unset($array[$key]);
	}
}

function limpiar_key($key) {
	$pos=strpos($key,"#");
	if($pos!==false) $key=substr($key,0,$pos);
	return $key;
}

function eval_files() {
	foreach($_FILES as $key=>$val) {
		$temp_name=$val["name"];
		$temp_file="";
		$temp_size=$val["size"];
		$temp_type=$val["type"];
		$temp_temp=$val["tmp_name"];
		if(file_exists($temp_temp)) {
			$temp_file=time()."_".get_unique_id_md5()."_".encode_bad_chars_file($temp_name);
			$ext=pathinfo($temp_file,PATHINFO_EXTENSION);
			if($ext=="php") $temp_file=substr($temp_file,0,-strlen($ext)-1).getDefault("exts/defaultext",".dat");
			setParam($key,$temp_name);
			setParam($key."_file",$temp_file);
			setParam($key."_size",$temp_size);
			setParam($key."_type",$temp_type);
			setParam($key."_temp",$temp_temp);
		} elseif($temp_name!="") {
			session_error(LANG("fileuploaderror").$temp_name);
		}
	}
}

function move_files($key) {
	move_uploaded_file(__getParam_helper($key."_temp"),get_directory("dirs/filesdir").__getParam_helper($key."_file"));
}

function xml2array($file,$usecache=true) {
	static $depend=array();
	$usecache=$usecache && eval_bool(getDefault("cache/usexml2arraycache",true));
	if(!file_exists($file)) xml_error("File not found: $file");
	if($usecache) {
		$cache=get_cache_file($file,getDefault("exts/arrayext",".arr"));
		$depend=array();
		if(cache_exists($cache,$file)) {
			$array=unserialize(file_get_contents($cache));
			if(isset($array["depend"])) {
				if(cache_exists($cache,$array["depend"])) {
					return $array["root"];
				}
			}
		}
	} else {
		$depend[]=$file;
	}
	$xml=file_get_contents($file);
	$data=xml2struct($xml,$file);
	$data=array_reverse($data);
	$array=leer_nodos($data,$file);
	if($usecache) {
		$array["source"]=$file;
		$array["depend"]=$depend;
		if(file_exists($cache)) unlink_protected($cache);
		file_put_contents($cache,serialize($array));
		chmod_protected($cache,0666);
	}
	return $array["root"];
}

function xml2struct($xml,$file="") {
	$parser=xml_parser_create();
	xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
	xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING,"UTF-8");
	xml_parse_into_struct($parser,$xml,$array,$index);
	$code=xml_get_error_code($parser);
	if($code) {
		$error=xml_error_string($code);
		$linea=xml_get_current_line_number($parser);
		$fila=xml_get_current_column_number($parser);
		xml_error("Error ".$code.": ".$error,"",$linea.",".$fila,$file);
	}
	xml_parser_free($parser);
	return $array;
}

function eval_attr($array) {
	if(!is_array($array)) {
		return eval_attr(array("inline"=>$array));
	} elseif(count($array)==2 && isset($array["value"]) && isset($array["#attr"])) {
		return eval_attr(array("inline"=>$array));
	}
	foreach($array as $key=>$val) {
		unset($array[$key]);
		if(is_array($val)) {
			if(isset($val["#attr"])) {
				unset($match);
				unset($nomatch);
				unset($prefix);
				unset($cancel);
				unset($remove);
				unset($for_var);
				unset($for_from);
				unset($for_step);
				unset($for_to);
				unset($global);
				unset($foreach_rows);
				unset($foreach_as);
				$value=$val["value"];
				$attr=$val["#attr"];
				$count=0;
				foreach($attr as $key2=>$val2) {
					$key2=limpiar_key($key2);
					switch($key2) {
						case "global":
							$global=$val2;
							$val2=explode(",",$val2);
							foreach($val2 as $var) global $$var;
							break;
						case "eval":
							if(eval_bool($val2)) {
								if(!$value) xml_error("Evaluation error: void expression");
								if(!isset($prefix)) {
									$old_value=$value;
									begin_eval_control();
									$value=eval("return $value;");
									end_eval_control($value,$old_value);
								} else {
									$old_value=$value;
									$value=array();
									foreach($prefix as $p) {
										$temp_value=$old_value;
										$temp_value=str_replace("getParam(\"","getParam(\"$p",$temp_value);
										$temp_value=str_replace("setParam(\"","setParam(\"$p",$temp_value);
										$temp_value=str_replace("getParamAsArray(\"","getParamAsArray(\"$p",$temp_value);
										begin_eval_control();
										$value[]=eval("return $temp_value;");
										end_eval_control($value,$old_value);
									}
								}
							}
							break;
						case "preeval":
							if(eval_bool($val2)) {
								$preevals=getDefault("parser/preevals");
								while(1) {
									foreach($preevals as $preeval) {
										$pos=strpos($value,$preeval);
										if($pos!==false) break;
									}
									if($pos===false) break;
									$len=strlen($value);
									$parent=0;
									$exist=0;
									for($i=$pos;$i<$len;$i++) {
										$letter=$value[$i];
										if($letter=="(") $parent++;
										if($parent>0) $exist=1;
										if($letter==")") $parent--;
										if($exist && !$parent) break;
									}
									$temp_value=substr($value,$pos,$i-$pos+1);
									$old_value=$temp_value;
									begin_eval_control();
									$temp_value=eval("return $temp_value;");
									end_eval_control($value,$old_value);
									$value=substr_replace($value,$temp_value,$pos,$i+1-$pos);
								}
							}
							break;
						case "match":
							$match=$val2;
							break;
						case "nomatch":
							$nomatch=$val2;
							break;
						case "prefix":
							if(eval_bool($val2)) {
								$prefix=array();
								foreach(array_merge($_POST,$_GET) as $key3=>$val3) {
									if(substr($key3,0,7)=="prefix_") {
										$ok=1;
										if(isset($match) && strpos($val3,$match)===false) $ok=0;
										if(isset($nomatch) && strpos($val3,$nomatch)!==false) $ok=0;
										if($ok) $prefix[]=$val3;
									}
								}
							}
							break;
						case "lang":
							if(eval_bool($val2)) {
								if(LANG_LOADED()) {
									$value=LANG($value);
								} else {
									$cancel=1;
								}
							}
							break;
						case "config":
							if(eval_bool($val2)) {
								if(CONFIG_LOADED()) {
									$newvalue=CONFIG($value);
									if($newvalue===null) xml_error("Configuration '$value' not found");
									$value=$newvalue;
								} else {
									$cancel=1;
								}
							}
							break;
						case "ifeval":
							begin_eval_control();
							$ifeval=eval("return $val2;");
							end_eval_control($ifeval,$val2);
							if(!$ifeval) $remove=1;
							break;
						case "ifpreeval":
							begin_eval_control();
							$ifpreeval=eval("return $val2;");
							end_eval_control($ifpreeval,$val2);
							if(!$ifpreeval) $cancel=1;
							break;
						case "require":
							$val2=explode(",",$val2);
							foreach($val2 as $file) {
								if(!file_exists($file)) xml_error("Require '$file' not found");
								require_once($file);
							}
							break;
						case "for":
							if(!$val2 || is_numeric($val2)) xml_error("The 'for' attr requires a variable name");
							$for_var=$val2;
							break;
						case "from":
							if(!is_numeric($val2)) {
								$old_value=$val2;
								begin_eval_control();
								$val2=eval("return $val2;");
								end_eval_control($val2,$old_value);
							}
							if(!is_numeric($val2)) xml_error("The 'from' attr requires an integer");
							$for_from=$val2;
							break;
						case "step":
							if(!is_numeric($val2)) {
								$old_value=$val2;
								begin_eval_control();
								$val2=eval("return $val2;");
								end_eval_control($val2,$old_value);
							}
							if(!is_numeric($val2)) xml_error("The 'step' attr requires an integer");
							$for_step=$val2;
							break;
						case "to":
							if(!is_numeric($val2)) {
								$old_value=$val2;
								begin_eval_control();
								$val2=eval("return $val2;");
								end_eval_control($val2,$old_value);
							}
							if(!is_numeric($val2)) xml_error("The 'to' attr requires an integer");
							$for_to=$val2;
							break;
						case "foreach":
							if(!is_array($$val2)) xml_error("The 'foreach' attr requires a rows array");
							$foreach_rows=$$val2;
							break;
						case "as":
							if(!$val2) xml_error("The 'as' attr requires a row array");
							$foreach_as=$val2;
							break;
						case "translated":
							if(!eval_bool($val2)) $value=$value." (not translated)";
							break;
						case "revised":
							if(!eval_bool($val2)) $value=$value." (not revised)";
							break;
						default:
							xml_error("Unknown attr '$key2' with value '$val2'");
					}
					$count++;
					if(isset($for_var) && isset($for_from) && isset($for_to)) {
						// CHECK SOME DOMAIN ERRORS
						if(!isset($for_step)) $for_step=1;
						if(!$for_step) xml_error("Error sequence FOR - FROM(${for_from}) - STEP(${for_step}) - TO(${for_to})");
						if(sign($for_to-$for_from)!=sign($for_step)) xml_error("Error sequence FOR - FROM(${for_from}) - STEP(${for_step}) - TO(${for_to})");
						// CONTINUE
						for($i=0;$i<$count;$i++) array_shift($attr);
						if(isset($global)) $attr=array_merge(array("global"=>$global),$attr);
						$old_value=$value;
						$value=array();
						for($$for_var=$for_from;$$for_var<=$for_to;$$for_var=$$for_var+$for_step) {
							$temp_value=eval_attr(array("inline"=>array("value"=>$old_value,"#attr"=>$attr)));
							if(isset($temp_value["inline"])) $value[]=$temp_value["inline"];
						}
						unset($for_var);
						unset($for_from);
						unset($for_step);
						unset($for_to);
						$val["value"]="__TRICK__";
						break;
					} elseif(isset($foreach_rows) && isset($foreach_as)) {
						for($i=0;$i<$count;$i++) array_shift($attr);
						if(isset($global)) $attr=array_merge(array("global"=>$global),$attr);
						$old_value=$value;
						$value=array();
						foreach($foreach_rows as $$foreach_as) {
							$temp_value=eval_attr(array("inline"=>array("value"=>$old_value,"#attr"=>$attr)));
							if(isset($temp_value["inline"])) $value[]=$temp_value["inline"];
						}
						unset($foreach_rows);
						unset($foreach_as);
						$val["value"]="__TRICK__";
						break;
					}
					if(isset($cancel) || isset($remove)) break;
				}
				if(isset($for_var) || isset($for_from) || isset($for_step) || isset($for_to)) {
					xml_error("Incomplete sequence FOR - FROM - STEP - TO");
				} elseif(isset($foreach_rows) || isset($foreach_as)) {
					xml_error("Incomplete sequence FOREACH - AS");
				} elseif(isset($cancel)) {
					$array[$key]=$val;
				} elseif(isset($remove)) {
					// NOTHING TO DO
				} elseif(!is_array($value)) {
					$array[$key]=$value;
				} elseif(!is_array($val["value"])) {
					foreach($value as $v) set_array($array,$key,$v);
				} else {
					$array[$key]=eval_attr($value);
				}
			} else {
				$array[$key]=eval_attr($val);
			}
		} else {
			$array[$key]=$val;
		}
	}
	return $array;
}

function eval_bool($arg) {
	if($arg===true) return 1;
	if($arg===false) return 0;
	$bool=strtoupper($arg);
	if($bool=="TRUE") return 1;
	if($bool=="FALSE") return 0;
	if($bool=="ON") return 1;
	if($bool=="OFF") return 0;
	if($bool=="YES") return 1;
	if($bool=="NO") return 0;
	if($bool=="1") return 1;
	if($bool=="0") return 0;
	xml_error("Unknown boolean value '$arg'");
}

function xml_error($error,$source="",$count="",$file="") {
	$array=array();
	$array["xmlerror"]=$error;
	if($count!="" && $file=="") $array["xmlerror"].=" (at line $count)";
	if($count=="" && $file!="") $array["xmlerror"].=" (on file $file)";
	if($count!="" && $file!="") $array["xmlerror"].=" (on file $file at line $count)";
	if(is_array($source)) $array["source"]=htmlentities(sprintr($source),ENT_COMPAT,"UTF-8");
	elseif($source!="") $array["source"]=htmlentities($source,ENT_COMPAT,"UTF-8");
	show_php_error($array);
}
?>