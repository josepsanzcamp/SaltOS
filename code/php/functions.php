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
function pre_datauser() {
	global $_USER;
	$user=useSession("user");
	$pass=useSession("pass");
	if($user!="" && $pass!="") {
		reset_datauser();
		$query=make_select_query("tbl_usuarios","*",make_where_query(array(
			"activo"=>1,
			"login"=>$user,
			"password"=>$pass
		)));
		$result=db_query($query);
		if(db_num_rows($result)==1) {
			$row=db_fetch_row($result);
			if($user==$row["login"] && $pass==$row["password"]) {
				$_USER["id"]=$row["id"];
			}
		}
		db_free($result);
	}
}

function reset_datauser() {
	global $_USER;
	$_USER=array("id"=>0);
}

function post_datauser() {
	global $_USER;
	global $_ERROR;
	if(isset($_USER["id"])) {
		$query=make_select_query("tbl_usuarios","*",make_where_query(array("id"=>$_USER["id"])));
		$result=db_query($query);
		if(db_num_rows($result)==1) {
			$row=db_fetch_row($result);
			$_USER=array();
			$_USER["id"]=$row["id"];
			$_USER["id_grupo"]=$row["id_grupo"];
			$_USER["hora_ini"]=$row["hora_ini"];
			$_USER["hora_fin"]=$row["hora_fin"];
			$_USER["dias_sem"]=$row["dias_sem"];
			$query2="SELECT a.codigo aplicacion,p.codigo permiso,x.allow allow,x.deny deny
				FROM (
					SELECT ai.id_aplicacion id_aplicacion,ai.id_permiso id_permiso,ai.allow allow,ai.deny deny
					FROM tbl_aplicaciones_i ai
					UNION
					SELECT gp.id_aplicacion id_aplicacion,gp.id_permiso id_permiso,gp.allow allow,gp.deny deny
					FROM tbl_grupos_p gp,tbl_usuarios u,tbl_aplicaciones_p ap
					WHERE u.id='${_USER["id"]}' AND u.id_grupo=gp.id_grupo AND gp.id_aplicacion=ap.id_aplicacion AND gp.id_permiso=ap.id_permiso
					UNION
					SELECT gp.id_aplicacion id_aplicacion,gp.id_permiso id_permiso,gp.allow allow,gp.deny deny
					FROM tbl_grupos_p gp,tbl_usuarios_g ug,tbl_aplicaciones_p ap
					WHERE ug.id_usuario='${_USER["id"]}' AND ug.id_grupo=gp.id_grupo AND gp.id_aplicacion=ap.id_aplicacion AND gp.id_permiso=ap.id_permiso
					UNION
					SELECT up.id_aplicacion id_aplicacion,up.id_permiso id_permiso,up.allow allow,up.deny deny
					FROM tbl_usuarios_p up,tbl_aplicaciones_p ap
					WHERE up.id_usuario='${_USER["id"]}' AND up.id_aplicacion=ap.id_aplicacion AND up.id_permiso=ap.id_permiso
				) x
				LEFT JOIN tbl_aplicaciones a ON a.id=x.id_aplicacion
				LEFT JOIN tbl_permisos p ON p.id=x.id_permiso";
			$result2=db_query($query2);
			while($row2=db_fetch_row($result2)) {
				$aplicacion=$row2["aplicacion"];
				$permiso=$row2["permiso"];
				if(!isset($_USER[$aplicacion])) $_USER[$aplicacion]=array();
				$_USER[$aplicacion][$permiso]=((isset($_USER[$aplicacion][$permiso])?$_USER[$aplicacion][$permiso]:1) && $row2["allow"] && !$row2["deny"])?1:0;
			}
			db_free($result2);
		} else {
			set_array($_ERROR,"error",LANG("nocheckuser"));
		}
		db_free($result);
	}
}

function check_time() {
	global $_USER;
	global $_ERROR;
	if(isset($_ERROR)) return false;
	if(!isset($_USER["id"])) return false;
	if(!$_USER["id"]) return false;
	$hora=current_time();
	$dia=(date("w",time())+6)%7;
	$check_hora1=($_USER["hora_ini"]<=$_USER["hora_fin"] && ($hora<$_USER["hora_ini"] || $hora>$_USER["hora_fin"]));
	$check_hora2=($_USER["hora_ini"]>$_USER["hora_fin"] && $hora<$_USER["hora_ini"] && $hora>$_USER["hora_fin"]);
	$check_dia=($_USER["dias_sem"][$dia]=="0");
	if($check_hora1 || $check_hora2) {
		set_array($_ERROR,"error",LANG("nochecktime"));
		reset_datauser();
	} elseif($check_dia) {
		set_array($_ERROR,"error",LANG("nocheckday"));
		reset_datauser();
	}
}

function check_user($aplicacion="",$permiso="") {
	global $_USER;
	global $_ERROR;
	if(isset($_ERROR)) return 0;
	if(!isset($_USER["id"])) return 0;
	if(!$_USER["id"]) return 0;
	if($aplicacion=="") return 1;
	if(!isset($_USER[$aplicacion])) return 0;
	if(!isset($_USER[$aplicacion][$permiso])) return 0;
	return $_USER[$aplicacion][$permiso];
}

function check_sql($aplicacion,$permiso) {
	// INITIAL CHECK
	global $_ERROR;
	if(isset($_ERROR)) return "(1=0)";
	// BEGIN NORMAL CODE
	global $_USER;
	// CHECK FOR USER/GROUP/ALL PERMISSIONS
	$sql=array();
	$sql["all"]="1=1";
	$sql["group"]="id_grupo IN (".check_ids($_USER["id_grupo"],execute_query_array(make_select_query("tbl_usuarios_g","id_grupo",make_where_query(array("id_usuario"=>$_USER["id"]))))).")";
	$sql["user"]=make_where_query(array("id_usuario"=>$_USER["id"]));
	foreach($sql as $key=>$val) if(check_user($aplicacion,"${key}_${permiso}")) return $val;
	return "1=0";
}

function check_sql2($permiso,$prefix="") {
	$query="SELECT * FROM tbl_aplicaciones WHERE tabla!=''";
	$result=db_query($query);
	$cases=array();
	while($row=db_fetch_row($result)) {
		$cases[]="(${prefix}id_aplicacion='${row["id"]}' AND (".check_sql($row["codigo"],$permiso)."))";
	}
	db_free($result);
	$cases="(".implode(" OR ",$cases).")";
	return $cases;
}

function eval_iniset($array) {
	if(is_array($array)) {
		foreach($array as $key=>$val) {
			$current=ini_get($key);
			$diff=0;
			if(strtolower($val)=="on" || strtolower($val)=="off") {
				$current=$current?"On":"Off";
				if(strtolower($val)!=strtolower($current)) $diff=1;
			} else {
				if($val!=$current) $diff=1;
			}
			if($diff) {
				if($key=="mbstring.internal_encoding") {
					if(mb_internal_encoding($val)===false) {
						show_php_error(array("phperror"=>"mb_internal_encoding fails to set '$val'"));
					}
				} elseif($key=="mbstring.detect_order") {
					if(mb_detect_order($val)===false) {
						show_php_error(array("phperror"=>"mb_detect_order fails to set '$val'"));
					}
				} elseif(ini_set($key,$val)===false) {
					show_php_error(array("phperror"=>"ini_set fails to set '$key' from '$current' to '$val'"));
				}
			}
		}
	}
}

function eval_putenv($array) {
	if(is_disabled_function("putenv")) return;
	if(is_array($array)) {
		foreach($array as $key=>$val) {
			$current=getenv($key);
			$diff=0;
			if($val!=$current) $diff=1;
			if($diff) {
				if(putenv($key."=".$val)===false) {
					show_php_error(array("phperror"=>"putenv fails to set '$key' from '$current' to '$val'"));
				}
			}
		}
	}
}

function force_ssl() {
	// SOME CHECKS
	if(!eval_bool(getDefault("server/forcessl"))) return;
	$serverport=getServer("SERVER_PORT");
	$porthttps=getDefault("server/porthttps",443);
	if($serverport==$porthttps) return;
	// MAIN VARIABLES
	$protocol="https://";
	$servername=getDefault("server/hostname",getServer("SERVER_NAME"));
	$addedport="";
	$scriptname=getDefault("server/pathname",getServer("SCRIPT_NAME"));
	$querystring=getServer("QUERY_STRING");
	// SOME CHECKS
	if(substr($scriptname,0,1)!="/") $scriptname="/".$scriptname;
	if(basename($scriptname)==getDefault("server/dirindex","index.php")) {
		$scriptname=dirname($scriptname);
		if(substr($scriptname,-1,1)!="/") $scriptname.="/";
	}
	// SOME CHECKS
	if($querystring) $querystring="?".str_replace("+","%20",$querystring);
	if($porthttps!=443) $addedport=":${porthttps}";
	// CONTINUE
	$url=$protocol.$servername.$addedport.$scriptname.$querystring;
	javascript_location($url);
	die();
}

function LANG_LOADED() {
	global $_LANG;
	return isset($_LANG);
}

function LANG($key,$arg="") {
	global $_LANG;
	if(!LANG_LOADED()) return "$key not load";
	if($arg) $arg="$arg,";
	$default=explode(",",$arg.$_LANG["default"]);
	foreach($default as $d) {
		if(isset($_LANG[$d][$key])) {
			return eval_bool(getDefault("debug/langdebug"))?"LANG(".$_LANG[$d][$key].")":$_LANG[$d][$key];
		}
	}
	return "$key (not found)";
}

function LANG_ESCAPE($key,$arg="") {
	return addslashes(LANG($key,$arg));
}

function LANG_ENCODE($key,$arg="") {
	return encode_bad_chars(LANG($key,$arg));
}

function CONFIG_LOADED() {
	global $_CONFIG;
	return isset($_CONFIG);
}

function CONFIG($key) {
	$row=array();
	$query=make_select_query("tbl_configuracion","valor",make_where_query(array("clave"=>$key)));
	capture_next_error();
	$config=execute_query($query);
	$error=get_clear_error();
	if($error=="" && $config!==null) {
		$row=array($key=>$config);
	} else {
		$row=getDefault("configs");
	}
	if(!isset($row[$key])) return null;
	return $row[$key];
}

function setConfig($key,$val) {
	$query=make_select_query("tbl_configuracion","valor",make_where_query(array("clave"=>$key)));
	$config=execute_query($query);
	if($config===null) {
		$query=make_insert_query("tbl_configuracion",array(
			"clave"=>$key,
			"valor"=>$val
		));
		db_query($query);
	} else {
		$query=make_update_query("tbl_configuracion",array(
			"valor"=>$val
		),make_where_query(array(
			"clave"=>$key
		)));
		db_query($query);
	}
}

function current_user() {
	global $_USER;
	if(!isset($_USER["id"])) return 0;
	return $_USER["id"];
}

function current_group() {
	global $_USER;
	if(!isset($_USER["id_grupo"])) return 0;
	return $_USER["id_grupo"];
}

function __aplicaciones($tipo,$dato) {
	static $diccionario=array();
	if(!count($diccionario)) {
		$query=make_select_query("tbl_aplicaciones",array("id","codigo","tabla","subtablas"));
		$result=db_query($query);
		$diccionario["page2id"]=array();
		$diccionario["id2page"]=array();
		$diccionario["page2table"]=array();
		$diccionario["table2page"]=array();
		$diccionario["id2table"]=array();
		$diccionario["table2id"]=array();
		$diccionario["id2subtables"]=array();
		$diccionario["page2subtables"]=array();
		$diccionario["table2subtables"]=array();
		while($row=db_fetch_row($result)) {
			$diccionario["page2id"][$row["codigo"]]=$row["id"];
			$diccionario["id2page"][$row["id"]]=$row["codigo"];
			$diccionario["page2table"][$row["codigo"]]=$row["tabla"];
			$diccionario["table2page"][$row["tabla"]]=$row["codigo"];
			$diccionario["id2table"][$row["id"]]=$row["tabla"];
			$diccionario["table2id"][$row["tabla"]]=$row["id"];
			$diccionario["id2subtables"][$row["id"]]=$row["subtablas"];
			$diccionario["page2subtables"][$row["codigo"]]=$row["subtablas"];
			$diccionario["table2subtables"][$row["tabla"]]=$row["subtablas"];
		}
		db_free($result);
	}
	if(!isset($diccionario[$tipo])) return "";
	if(!isset($diccionario[$tipo][$dato])) return "";
	return $diccionario[$tipo][$dato];
}

function page2id($page) {
	return __aplicaciones(__FUNCTION__,$page);
}

function id2page($id) {
	return __aplicaciones(__FUNCTION__,$id);
}

function page2table($page) {
	return __aplicaciones(__FUNCTION__,$page);
}

function table2page($table) {
	return __aplicaciones(__FUNCTION__,$table);
}

function id2table($id) {
	return __aplicaciones(__FUNCTION__,$id);
}

function table2id($table) {
	return __aplicaciones(__FUNCTION__,$table);
}

function id2subtables($id) {
	return __aplicaciones(__FUNCTION__,$id);
}

function page2subtables($page) {
	return __aplicaciones(__FUNCTION__,$page);
}

function table2subtables($table) {
	return __aplicaciones(__FUNCTION__,$table);
}

function __usuarios($tipo,$dato) {
	static $diccionario=array();
	if(!count($diccionario)) {
		$query=make_select_query("tbl_usuarios",array("id","login"));
		$result=db_query($query);
		$diccionario["user2id"]=array();
		$diccionario["id2user"]=array();
		while($row=db_fetch_row($result)) {
			$diccionario["user2id"][$row["login"]]=$row["id"];
			$diccionario["id2user"][$row["id"]]=$row["login"];
		}
		db_free($result);
	}
	if(!isset($diccionario[$tipo])) return "";
	if(!isset($diccionario[$tipo][$dato])) return "";
	return $diccionario[$tipo][$dato];
}

function user2id($user) {
	return __usuarios(__FUNCTION__,$user);
}

function id2user($id) {
	return __usuarios(__FUNCTION__,$id);
}

function get_filtered_field($field) {
	if(substr($field,0,4)=="tel:") {
		$temp=strtok($field,":");
		$temp=strtok("");
		$field=$temp;
	}
	if(substr($field,0,7)=="mailto:") {
		$temp=strtok($field,":");
		$temp=strtok("");
		$field=$temp;
	}
	if(substr($field,0,7)=="href:") {
		$temp=strtok($field,":");
		$temp=strtok("");
		$field=$temp;
	}
	if(substr($field,0,5)=="link:") {
		$temp=strtok($field,":");
		$temp=strtok(":");
		$temp=strtok("");
		$field=$temp;
	}
	return $field;
}

function debug_dump($die=true) {
	global $config;
	echo "<pre>"; echo "GET ".sprintr($_GET)."</pre>";
	echo "<pre>"; echo "POST ".sprintr($_POST)."</pre>";
	echo "<pre>"; echo "FILES ".sprintr($_FILES)."</pre>";
	echo "<pre>"; echo "CONFIG ".sprintr($config)."</pre>";
	if($die) die();
}

function check_filter($array) {
	foreach($array as $key=>$val) {
		if(getParam($key)!=$val) {
			return true;
		}
	}
	return false;
}

function add_css_page(&$result,$page) {
	$file="css/${page}.css";
	if(!file_exists($file)) $file=$page;
	if(file_exists($file)) {
		$exists=0;
		if(isset($result["styles"])) {
			if(in_array($file,$result["styles"])) $exists=1;
			foreach($result["styles"] as $array) if(is_array($array)) if(in_array($file,$array)) $exists=1;
		}
		if(!$exists) {
			if(!eval_bool(getDefault("cache/usecsscache"))) set_array($result["styles"],"include",$file);
			else set_array($result["styles"],"cache",array("include"=>$file));
		}
	}
}

function add_js_page(&$result,$page) {
	$file="js/${page}.js";
	if(!file_exists($file)) $file=$page;
	if(file_exists($file)) {
		$exists=0;
		if(isset($result["javascript"])) {
			if(in_array($file,$result["javascript"])) $exists=1;
			foreach($result["javascript"] as $array) if(is_array($array)) if(in_array($file,$array)) $exists=1;
		}
		if(!$exists) {
			if(!eval_bool(getDefault("cache/usejscache"))) set_array($result["javascript"],"include",$file);
			else set_array($result["javascript"],"cache",array("include"=>$file));
		}
	}
}

function add_css_js_page(&$result,$page) {
	add_css_page($result,$page);
	add_js_page($result,$page);
}

function cache_gc() {
	if(!eval_bool(getDefault("cache/cachegcenabled"))) return;
	init_random();
	if(rand(0,intval(getDefault("cache/cachegcdivisor")))>intval(getDefault("cache/cachegcprobability"))) return;
	if(!semaphore_acquire(__FUNCTION__,getDefault("semaphoretimeout",100000))) return;
	$cachedir=get_directory("dirs/cachedir");
	$files=array();
	$files=array_merge($files,glob_protected($cachedir."*"));
	$files=array_merge($files,glob_protected($cachedir.".*"));
	$files=array_diff($files,array($cachedir.".",$cachedir."..",$cachedir.".htaccess"));
	$delta=time()-intval(getDefault("cache/cachegctimeout"));
	foreach($files as $file) {
		list($mtime,$error)=filemtime_protected($file);
		if(!$error && $delta>$mtime) unlink_protected($file);
	}
	semaphore_release(__FUNCTION__);
}

function db_schema() {
	if(!eval_bool(getDefault("db/dbschema"))) return;
	$file="xml/dbschema.xml";
	capture_next_error();
	$hash1=CONFIG($file);
	get_clear_error();
	$hash2=md5(json_encode(xml2array($file)));
	if($hash1!=$hash2) {
		if(!semaphore_acquire(array("db_schema","db_static"),getDefault("semaphoretimeout",100000))) return;
		$dbschema=eval_attr(xml2array($file));
		if(is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
			$tables1=get_tables();
			$tables2=get_tables_from_dbschema();
			if(isset($dbschema["indexes"]) && is_array($dbschema["indexes"])) {
				foreach($dbschema["indexes"] as $key=>$val) {
					foreach($val["fields"] as $key2=>$val2) $val["fields"][$key2]=$val2["name"];
					$dbschema["indexes"][$key]["name"]=$val["table"]."_".implode("_",$val["fields"]);
				}
				foreach($dbschema["tables"] as $tablespec) {
					foreach($tablespec["fields"] as $field) {
						if(isset($field["fkey"]) && $field["fkey"]!="") {
							set_array($dbschema["indexes"],"index",array(
								"name"=>$tablespec["name"]."_".$field["name"],
								"table"=>$tablespec["name"],
								"fields"=>array(
									"field"=>array(
										"name"=>$field["name"]
									)
								)
							));
						}
					}
				}
			}
			if(isset($dbschema["excludes"]) && is_array($dbschema["excludes"])) {
				foreach($dbschema["excludes"] as $exclude) {
					foreach($tables1 as $key=>$val) if($exclude["name"]==$val) unset($tables1[$key]);
					foreach($tables2 as $key=>$val) if($exclude["name"]==$val) unset($tables2[$key]);
				}
			}
			foreach($tables1 as $table) {
				$isbackup=(substr($table,0,2)=="__" && substr($table,-2,2)=="__");
				if(!$isbackup && !in_array($table,$tables2)) {
					$backup="__${table}__";
					db_query(sql_alter_table($table,$backup));
				}
			}
			foreach($dbschema["tables"] as $tablespec) {
				$table=$tablespec["name"];
				$backup="__${table}__";
				if(in_array($table,$tables1)) {
					$fields1=get_fields($table);
					$fields2=get_fields_from_dbschema($table);
					$hash3=md5(json_encode($fields1));
					$hash4=md5(json_encode($fields2));
					if($hash3!=$hash4) {
						db_query(sql_alter_table($table,$backup));
						db_query(sql_create_table($tablespec));
						db_query(sql_insert_from_select($table,$backup));
						db_query(sql_drop_table($backup));
					}
				} elseif(in_array($backup,$tables1)) {
					$fields1=get_fields($backup);
					$fields2=get_fields_from_dbschema($table);
					$hash3=md5(json_encode($fields1));
					$hash4=md5(json_encode($fields2));
					if($hash3!=$hash4) {
						db_query(sql_create_table($tablespec));
						db_query(sql_insert_from_select($table,$backup));
						db_query(sql_drop_table($backup));
					} else {
						db_query(sql_alter_table($backup,$table));
					}
				} else {
					db_query(sql_create_table($tablespec));
				}
				if(isset($dbschema["indexes"]) && is_array($dbschema["indexes"])) {
					$indexes1=get_indexes($table);
					$indexes2=array();
					foreach($dbschema["indexes"] as $indexspec) {
						if($indexspec["table"]==$table) {
							$indexes2[$indexspec["name"]]=array();
							foreach($indexspec["fields"] as $fieldspec) $indexes2[$indexspec["name"]][]=$fieldspec["name"];
						}
					}
					foreach($indexes1 as $index=>$fields) {
						if(!array_key_exists($index,$indexes2)) {
							db_query(sql_drop_index($index,$table));
						}
					}
					foreach($dbschema["indexes"] as $indexspec) {
						if($indexspec["table"]==$table) {
							$index=$indexspec["name"];
							if(array_key_exists($index,$indexes1)) {
								$fields1=$indexes1[$index];
								$fields2=$indexes2[$index];
								$hash3=md5(json_encode($fields1));
								$hash4=md5(json_encode($fields2));
								if($hash3!=$hash4) {
									db_query(sql_drop_index($index,$table));
									db_query(sql_create_index($indexspec));
								}
							} else {
								db_query(sql_create_index($indexspec));
							}
						}
					}
				}
			}
		}
		setConfig($file,$hash2);
		semaphore_release(array("db_schema","db_static"));
	}
}

function db_static() {
	if(!eval_bool(getDefault("db/dbstatic"))) return;
	$file="xml/dbstatic.xml";
	$hash1=CONFIG($file);
	$hash2=md5(json_encode(xml2array($file)));
	if($hash1!=$hash2) {
		if(!semaphore_acquire(array("db_schema","db_static"),getDefault("semaphoretimeout",100000))) return;
		$dbstatic=eval_attr(xml2array($file));
		if(is_array($dbstatic)) {
			foreach($dbstatic as $table=>$rows) {
				$query=make_delete_query($table);
				db_query($query);
				foreach($rows as $row) __db_static_helper($table,$row);
			}
		}
		setConfig($file,$hash2);
		semaphore_release(array("db_schema","db_static"));
	}
}

function __db_static_helper($table,$row) {
	$fields=getDefault("db/dbfields");
	$found="";
	if(is_array($fields)) {
		foreach($fields as $field) {
			if(isset($row[$field]) && strpos($row[$field],",")!==false) {
				$found=$field;
				break;
			}
		}
	}
	if($found!="") {
		$a=explode(",",$row[$field]);
		foreach($a as $b) {
			$row[$field]=$b;
			__db_static_helper($table,$row);
		}
	} else {
		$query=make_insert_query($table,$row);
		db_query($query);
	}
}

function session_error($error) {
	sess_init();
	$hashs=array();
	if(isset($_SESSION["errors"])) foreach($_SESSION["errors"] as $val) $hashs[]=md5($val);
	$hash=md5($error);
	if(!in_array($hash,$hashs)) set_array($_SESSION["errors"],"error",$error);
	sess_close();
}

function session_alert($alert) {
	sess_init();
	$hashs=array();
	if(isset($_SESSION["alerts"])) foreach($_SESSION["alerts"] as $val) $hashs[]=md5($val);
	$hash=md5($alert);
	if(!in_array($hash,$hashs)) set_array($_SESSION["alerts"],"alert",$alert);
	sess_close();
}

function check_ids() {
	$value=array();
	foreach(func_get_args() as $arg) {
		$arg=is_array($arg)?$arg:explode(",",$arg);
		$value=array_merge($value,$arg);
	}
	foreach($value as $key=>$val) {
		if(substr_count($val,"_")==2) {
			if($val[0]=="'" && substr($val,-1,1)=="'") $val=substr($val,1,-1);
			$val=explode("_",$val);
			$val[1]=id2page(page2id($val[1]));
			$val[2]=abs($val[2]);
			$value[$key]="'".implode("_",$val)."'";
		} else {
			$value[$key]=abs($val);
		}
	}
	$value=count($value)?implode(",",$value):"0";
	return $value;
}

function load_style($style) {
	global $_CONFIG;
	if(!isset($_CONFIG["styles2"])) $_CONFIG["styles2"]=eval_attr(xml2array("xml/styles.xml"));
	foreach($_CONFIG["styles2"] as $style2) if($style2["value"]==$style) return true;
	return false;
}

function color_style($style) {
	global $_CONFIG;
	foreach($_CONFIG["styles2"] as $style2) if($style2["value"]==$style) return $style2["color"];
	return "";
}

function load_lang($lang) {
	return file_exists("xml/lang/${lang}.xml");
}

function check_remember() {
	if(!eval_bool(getDefault("security/allowremember"))) return;
	if(useSession("user")) return;
	if(useSession("pass")) return;
	if(!useCookie("remember")) return;
	if(!useCookie("user")) return;
	if(!useCookie("pass")) return;
	if(!check_security("retries") && !check_security("logouts")) {
		sess_close();
		setParam("action","logout");
		include("php/action/logout.php");
	}
	useSession("user",useCookie("user"));
	useSession("pass",useCookie("pass"));
	pre_datauser();
	check_security("login");
	return;
}

function remake_password($user,$pass) {
	$query="SELECT * FROM tbl_usuarios WHERE activo='1' AND login='${user}'";
	$result=db_query($query);
	if(db_num_rows($result)==1) {
		$row=db_fetch_row($result);
		if($user==$row["login"]) {
			if(check_password($pass,$row["password"])) {
				$pass=$row["password"];
			} elseif(in_array($row["password"],array(md5($pass),sha1($pass)))) {
				// CONVERT FROM MD5/SHA1 TO CRYPT FORMAT
				$pass=hash_password($pass);
				$query=make_update_query("tbl_usuarios",array(
					"password"=>$pass
				),"activo='1' AND login='${user}'");
				db_query($query);
			}
		}
	}
	db_free($result);
	return $pass;
}

function check_basicauth() {
	if(!eval_bool(getDefault("security/allowbasicauth"))) return;
	if(useSession("user")) return;
	if(useSession("pass")) return;
	if(!getServer("PHP_AUTH_USER")) return;
	if(!getServer("PHP_AUTH_PW")) return;
	if(!check_security("retries") && !check_security("logouts")) {
		sess_close();
		setParam("action","logout");
		include("php/action/logout.php");
	}
	useSession("user",getServer("PHP_AUTH_USER"));
	useSession("pass",remake_password(getServer("PHP_AUTH_USER"),getServer("PHP_AUTH_PW")));
	pre_datauser();
	check_security("login");
	return;
}

function check_security($action="") {
	if(!eval_bool(getDefault("security/filterenabled"))) return true;
	$id_session=current_session();
	$id_usuario=current_user();
	$remote_addr=getServer("REMOTE_ADDR");
	// BUSCAR ID_SECURITY
	$query=make_select_query("tbl_security","id",make_where_query(array("id_session"=>$id_session)));
	$id_security=execute_query($query);
	if(is_array($id_security)) {
		$query=make_delete_query("tbl_security",make_where_query(array("id_session"=>$id_session)));
		db_query($query);
		$id_security=0;
	}
	if(!$id_security) {
		$query=make_insert_query("tbl_security",array(
			"id_session"=>$id_session,
			"id_usuario"=>0,
			"logout"=>0
		));
		db_query($query);
		$query=make_select_query("tbl_security","MAX(id)");
		$id_security=execute_query($query);
	}
	// BUSCAR ID_SECURITY_IP
	$query="SELECT id FROM tbl_security_ip WHERE id_session='${id_session}' AND remote_addr='${remote_addr}'";
	$id_security_ip=execute_query($query);
	if(is_array($id_security_ip)) {
		$query=make_delete_query("tbl_security_ip","id_session='${id_session}' AND remote_addr='${remote_addr}'");
		db_query($query);
		$id_security_ip=0;
	}
	if(!$id_security_ip) {
		$query=make_insert_query("tbl_security_ip",array(
			"id_session"=>$id_session,
			"remote_addr"=>$remote_addr
		));
		db_query($query);
		$query=make_select_query("tbl_security_ip","MAX(id)");
		$id_security_ip=execute_query($query);
	}
	// BORRAR REGISTROS CADUCADOS
	$query=make_delete_query("tbl_security","NOT id_session IN (SELECT id FROM tbl_sessions)");
	db_query($query);
	$query=make_delete_query("tbl_security_ip","NOT id_session IN (SELECT id FROM tbl_sessions)");
	db_query($query);
	// NORMAL CODE
	if($action=="login") {
		if($id_usuario) {
			// MARCAR REGISTROS DEL ID_USUARIO PARA LOGOUT
			$query=make_update_query("tbl_security",array(
				"logout"=>"1"
			),"id_usuario='${id_usuario}'");
			db_query($query);
			// LIMPIAR RETRIES DEL MISMO REMOTE_ADDR
			$query=make_update_query("tbl_security_ip",array(
				"retries"=>"0"
			),"id_session='${id_session}' OR remote_addr='${remote_addr}'");
			db_query($query);
			// PONER ID_USUARIO Y RESETEAR LOGOUT EN EL REGISTRO ACTUAL
			$query=make_update_query("tbl_security",array(
				"id_usuario"=>$id_usuario,
				"logout"=>0
			),"id='${id_security}'");
			db_query($query);
		} else {
			// INCREMENTAR RETRIES EN EL REGISTRO ACTUAL
			$query=make_update_query("tbl_security_ip",array(),"id='${id_security_ip}'",array(
				"retries"=>"retries+1"
			));
			db_query($query);
		}
	} elseif($action=="logout") {
		// RESETEAR ID_USUARIO Y LOGOUT EN EL REGISTRO ACTUAL
		$query=make_update_query("tbl_security",array(
			"id_usuario"=>0,
			"logout"=>0
		),"id='${id_security}'");
		db_query($query);
	} elseif($action=="main") {
		if($id_usuario) {
			// BUSCAR LOGOUT EN EL REGISTRO ACTUAL
			$query="SELECT logout,id_usuario FROM tbl_security WHERE id='${id_security}'";
			$result=execute_query($query);
			// BUSCAR COUNT DEL ID_USUARIO EN TODOS LOS REGISTROS DONDE LOGOUT=0
			$query="SELECT COUNT(*) FROM tbl_security WHERE id_usuario='${id_usuario}' AND logout='0'";
			$count=execute_query($query);
			// HACER LOGOUT SI ES NECESARIO
			if($result["logout"] || !$result["id_usuario"] || $count!=1) {
				setParam("action","logout");
				include("php/action/logout.php");
			}
		}
	} elseif($action=="retries") {
		// BUSCAR SUM(RETRIES) PARA TODOS LOS REGISTROS_IP DEL ID_SESSION O DEL REMOTE_ADDR
		$query="SELECT SUM(retries) FROM tbl_security_ip WHERE id_session='${id_session}' OR remote_addr='${remote_addr}'";
		$retries=execute_query($query);
		return $retries<getDefault("security/maxretries");
	} elseif($action=="logouts") {
		// BUSCAR LOGOUT EN EL REGISTRO ACTUAL
		$query="SELECT logout FROM tbl_security WHERE id='${id_security}'";
		$logout=execute_query($query);
		return !$logout;
	} elseif($action=="captcha") {
		$query=make_update_query("tbl_security_ip",array(
			"retries"=>0
		),"id='${id_security_ip}'");
		db_query($query);
	} else {
		show_php_error(array("phperror"=>"Unknown action='$action' in check_security"));
	}
}

function check_captcha($captcha="") {
	$id=getDefault("captcha/id","captcha");
	$valid=1;
	// CHECK VALUE
	$captcha1=$captcha;
	$captcha2=useSession($id);
	if($captcha!="") useSession($id,"null");
	if(strlen($captcha1)==0) $valid=0;
	if(strlen($captcha2)==0) $valid=0;
	if($captcha1=="null") $valid=0;
	if($captcha2=="null") $valid=0;
	if($captcha1!=$captcha2) $valid=0;
	// CONTINUE
	return $valid;
}

function use_table_cookies($name,$value="",$default="") {
	$uid=current_user();
	if($uid) {
		if($value!="") {
			if($value=="null") $value="";
			$query="SELECT COUNT(*) FROM tbl_cookies WHERE id_usuario='$uid' AND clave='$name'";
			$count=execute_query($query);
			if($count>1) {
				$query=make_delete_query("tbl_cookies","id_usuario='${uid}' AND clave='${name}'");
				db_query($query);
				$count=0;
			}
			if($count==0) {
				$query=make_insert_query("tbl_cookies",array(
					"id_usuario"=>$uid,
					"clave"=>$name,
					"valor"=>$value
				));
				db_query($query);
			} else {
				$query=make_update_query("tbl_cookies",array(
					"valor"=>$value
				),"id_usuario='${uid}' AND clave='${name}'");
				db_query($query);
			}
		} else {
			$query="SELECT valor FROM tbl_cookies WHERE id_usuario='$uid' AND clave='$name'";
			$value=execute_query($query);
			if($value=="") $value=$default;
		}
	} else {
		if($value=="") $value=$default;
	}
	return $value;
}

function getDefault($key,$default="") {
	global $_CONFIG;
	$key=explode("/",$key);
	$count=count($key);
	$config=$_CONFIG;
	if($count==1 && isset($config["default"][$key[0]])) {
		$config=$config["default"][$key[0]];
		$count=0;
	}
	while($count) {
		$key2=array_shift($key);
		if(!isset($config[$key2])) return $default;
		$config=$config[$key2];
		$count--;
	}
	if($config==="") return $default;
	return $config;
}

function capture_next_error() {
	global $_ERROR_HANDLER;
	if(!isset($_ERROR_HANDLER["level"])) show_php_error(array("phperror"=>"error_handler without levels availables"));
	$_ERROR_HANDLER["level"]++;
	array_push($_ERROR_HANDLER["msg"],"");
}

function get_clear_error() {
	global $_ERROR_HANDLER;
	if($_ERROR_HANDLER["level"]<=0) show_php_error(array("phperror"=>"error_handler without levels availables"));
	$_ERROR_HANDLER["level"]--;
	return array_pop($_ERROR_HANDLER["msg"]);
}

function do_message_error($array,$format) {
	static $dict=array(
		"html"=>array(array("<h3>","</h3>"),array("<pre>","</pre>"),"<br/>"),
		"text"=>array(array("***** "," *****\n"),array("","\n"),"\n")
	);
	if(!isset($dict[$format])) die("Unknown format $format");
	$msg=array();
	if(isset($array["phperror"])) $msg[]=array("PHP Error",$array["phperror"]);
	if(isset($array["xmlerror"])) $msg[]=array("XML Error",$array["xmlerror"]);
	if(isset($array["dberror"])) {
		$privated=array(getDefault("db/host"),getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name"));
		$msg[]=array("DB Error",str_replace($privated,"...",$array["dberror"]));
	}
	if(isset($array["jserror"])) $msg[]=array("JS Error",$array["jserror"]);
	if(isset($array["source"])) $msg[]=array("XML Source",$array["source"]);
	if(isset($array["exception"])) $msg[]=array("Exception",$array["exception"]);
	if(isset($array["details"])) $msg[]=array("Details",$array["details"]);
	if(isset($array["query"])) $msg[]=array("Query",$array["query"]);
	if(isset($array["backtrace"])) {
		foreach($array["backtrace"] as $key=>$item) {
			$temp="${key} => ${item["function"]}";
			if(isset($item["class"])) $temp.=" (in class ${item["class"]})";
			if(isset($item["file"]) && isset($item["line"])) $temp.=" (in file ".basename($item["file"])." at line ${item["line"]})";
			$array["backtrace"][$key]=$temp;
		}
		$msg[]=array("Backtrace",implode($dict[$format][2],$array["backtrace"]));
	}
	if(isset($array["debug"])) {
		foreach($array["debug"] as $key=>$item) $array["debug"][$key]="${key} => ${item}";
		$msg[]=array("Debug",implode($dict[$format][2],$array["debug"]));
	}
	foreach($msg as $key=>$item) $msg[$key]=$dict[$format][0][0].$item[0].$dict[$format][0][1].$dict[$format][1][0].$item[1].$dict[$format][1][1];
	$msg=implode($msg);
	return $msg;
}

function show_php_error($array=null) {
	global $_ERROR_HANDLER;
	static $backup=null;
	if($array===null && $backup!==null) {
		while($_ERROR_HANDLER["level"]>0) get_clear_error();
		show_php_error($backup);
	}
	// ADD BACKTRACE IF NOT FOUND
	if(!isset($array["backtrace"])) $array["backtrace"]=debug_backtrace();
	// ADD DEBUG IF NOT FOUND
	if(!isset($array["debug"])) {
		$array["debug"]=array();
		if(useSession("user")) $array["debug"]["user"]=useSession("user");
		foreach(array("page","action","id") as $item) if(getParam($item)) $array["debug"][$item]=getParam($item);
		if(!count($array["debug"])) unset($array["debug"]);
	}
	// CREATE THE MESSAGE ERROR USING HTML ENTITIES AND PLAIN TEXT
	$msg_html=do_message_error($array,"html");
	$msg_text=do_message_error($array,"text");
	$msg=getServer("SHELL")?$msg_text:$msg_html;
	// REFUSE THE DEPRECATED WARNINGS
	if(isset($array["phperror"]) && stripos($array["phperror"],"deprecated")!==false) {
		$hash=md5($msg_text);
		$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
		if(is_writable($dir)) {
			$file=isset($array["file"])?$array["file"]:getDefault("debug/deprecatedfile","deprecated.log");
			if(checklog($hash,$file)) $msg_text="";
			addlog("${msg_text}***** ${hash} *****",$file);
		}
		return;
	}
	// CHECK IF CAPTURE ERROR WAS ACTIVE
	if($_ERROR_HANDLER["level"]>0) {
		$old=array_pop($_ERROR_HANDLER["msg"]);
		array_push($_ERROR_HANDLER["msg"],$old.$msg);
		$backup=$array;
		return;
	}
	// ADD THE MSG_ALT TO THE ERROR LOG FILE
	$hash=md5($msg_text);
	$dir=get_directory("dirs/filesdir",getcwd_protected()."/files");
	if(is_writable($dir)) {
		$file=isset($array["file"])?$array["file"]:getDefault("debug/errorfile","error.log");
		if(isset($array["phperror"])) $file=getDefault("debug/phperrorfile","phperror.log");
		if(isset($array["xmlerror"])) $file=getDefault("debug/xmlerrorfile","xmlerror.log");
		if(isset($array["dberror"])) $file=getDefault("debug/dberrorfile","dberror.log");
		if(isset($array["jserror"])) $file=getDefault("debug/jserrorfile","jserror.log");
		if(checklog($hash,$file)) $msg_text="";
		addlog("${msg_text}***** ${hash} *****",$file);
	}
	// CHECK FOR CANCEL_DIE
	if(isset($array["cancel"]) && eval_bool($array["cancel"])) return;
	if(isset($array["die"]) && !eval_bool($array["die"])) return;
	while(ob_get_level()) ob_end_clean(); // TRICK TO CLEAR SCREEN
	// PREPARE THE FINAL REPORT (ONLY IN NOT SHELL MODE)
	if(!getServer("SHELL")) {
		$msg=pretty_html_error($msg);
		if(!headers_sent()) {
			output_handler(array(
				"data"=>$msg,
				"type"=>"text/html",
				"cache"=>false
			));
		}
	}
	// DUMP TO STDOUT
	echo $msg;
	die();
}

function pretty_html_error($msg) {
	$html="<!DOCTYPE html>";
	$html.="<html>";
	$html.="<head>";
	$html.="<title>".get_name_version_revision()."</title>";
	$html.="<style>";
	$html.=".phperror { color:#fff; background:#00f; margin:0; padding:10px; font-family:monospace; }";
	$html.=".phperror form { display:inline; float:right; }";
	$html.=".phperror input { background:#fff; color:#00f; font-weight:bold; border:0; padding:10px 20px; font-family:monospace; margin-left:10px; }";
	$html.=".phperror input:hover { background:#000; color:#fff; cursor:pointer; }";
	$html.=".phperror h1 { display:inline; }";
	$html.=".phperror pre { white-space:normal; }";
	$html.="</style>";
	$html.="</head>";
	$html.="<body class='phperror'>";
	$html.=__pretty_html_error_helper("",array("page"=>"home"),LANG_LOADED()?LANG("gotohome"):"Go to home");
	$html.=__pretty_html_error_helper("",array("page"=>"support","subject"=>(LANG_LOADED()?LANG("notifybug"):"Notify bug").": ".get_name_version_revision(),"comentarios"=>$msg),LANG_LOADED()?LANG("notifybug"):"Notify bug");
	$html.="<h1>".get_name_version_revision()."</h1>";
	$html.=$msg;
	$html.="</body>";
	$html.="</html>";
	return $html;
}

function __pretty_html_error_helper($action,$hiddens,$submit) {
	$html="";
	$html.="<form action='${action}' method='post'>";
	foreach($hiddens as $key=>$val) {
		$val=htmlentities($val,ENT_COMPAT,"UTF-8");
		$html.="<input type=\"hidden\" name=\"${key}\" value=\"${val}\"/>";
	}
	$html.="<input type='submit' value='${submit}'/>";
	$html.="</form>";
	return $html;
}

function __error_handler($type,$message,$file,$line) {
	show_php_error(array("phperror"=>"${message} (code ${type})","details"=>"Error on file '".basename($file)."' at line ${line}","backtrace"=>debug_backtrace()));
}

function __exception_handler($e) {
	show_php_error(array("exception"=>$e->getMessage()." (code ".$e->getCode().")","details"=>"Error on file '".basename($e->getFile())."' at line ".$e->getLine(),"backtrace"=>$e->getTrace()));
}

function __shutdown_handler() {
	global $_ERROR_HANDLER;
	if($_ERROR_HANDLER["level"]>0) show_php_error();
	$error=error_get_last();
	$types=array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR);
	if(is_array($error) && isset($error["type"]) && in_array($error["type"],$types)) {
		show_php_error(array("phperror"=>"${error["message"]}","details"=>"Error on file '".basename($error["file"])."' at line ${error["line"]}","backtrace"=>debug_backtrace()));
	}
	semaphore_shutdown();
}

function program_handlers() {
	global $_ERROR_HANDLER;
	$_ERROR_HANDLER=array("level"=>0,"msg"=>array());
	// IMPORTANT CHECK
	if(!ini_get("date.timezone")) ini_set("date.timezone","Europe/Madrid");
	// CONTINUE
	error_reporting(E_ALL);
	set_error_handler("__error_handler");
	set_exception_handler("__exception_handler");
	register_shutdown_function("__shutdown_handler");
	time_get_usage(true);
}

function init_random() {
	static $init=false;
	if($init) return;
	srand(intval(microtime(true)*1000000));
	$init=true;
}

function check_postlimit() {
	$content_length=getServer("CONTENT_LENGTH");
	if($content_length) {
		$post_max_size=ini_get("post_max_size");
		if(!$post_max_size && ishhvm()) $post_max_size=ini_get("hhvm.server.max_post_size");
		$post_max_size=normalize_value($post_max_size);
		if($content_length>$post_max_size) session_error(LANG("postlimiterror"));
	}
}

function check_system() {
	// GENERAL CHECK
	if(headers_sent()) show_php_error(array("phperror"=>"Has been detected previous headers sent"));
	// PACKAGE CHECKS
	$array=array(
		array("class_exists","DomElement","Class","php-xml"),
		array("function_exists","imagecreatetruecolor","Function","php-gd"),
		array("function_exists","imagecreatefrompng","Function","php-gd"),
		array("function_exists","mb_check_encoding","Function","php-mbstring"),
		array("function_exists","mb_convert_encoding","Function","php-mbstring"),
		array("function_exists","mb_strlen","Function","php-mbstring"),
		array("function_exists","mb_substr","Function","php-mbstring"),
		array("function_exists","mb_strpos","Function","php-mbstring"));
	foreach($array as $a) if(!$a[0]($a[1])) show_php_error(array("phperror"=>"$a[2] $a[1] not found","details"=>"Try to install $a[3] package"));
	// INSTALL CHECK
	if(!file_exists("files/config.xml")) { include("install/install.php"); die(); }
}

function action_denied() {
	session_error(LANG("permdenied")." (".getParam("action").")");
	javascript_location_page("");
	die();
}

function fix_input_vars() {
	if(intval(ini_get("max_input_vars"))>0) {
		$temp=getParam("fix_input_vars");
		if($temp!="") {
			$temp=querystring2array(base64_decode($temp));
			if(isset($_GET["fix_input_vars"])) {
				unset($_GET["fix_input_vars"]);
				$_GET=array_merge($_GET,$temp);
			}
			if(isset($_POST["fix_input_vars"])) {
				unset($_POST["fix_input_vars"]);
				$_POST=array_merge($_POST,$temp);
			}
		}
	}
}

function memory_get_free($bytes=false) {
	$memory_limit=normalize_value(ini_get("memory_limit"));
	$memory_usage=memory_get_usage();
	$diff=$memory_limit-$memory_usage;
	if(!$bytes) $diff=($diff*100)/$memory_limit;
	return $diff;
}

function time_get_usage($secs=false) {
	return __time_get_helper(__FUNCTION__,$secs);
}

function time_get_free($secs=false) {
	return __time_get_helper(__FUNCTION__,$secs);
}

function __time_get_helper($fn,$secs) {
	static $ini=null;
	if($ini===null) $ini=microtime(true);
	$cur=microtime(true);
	$max=ini_get("max_execution_time");
	if(!$max) $max=getDefault("ini_set/max_execution_time");
	if(stripos($fn,"usage")!==false) $diff=$cur-$ini;
	elseif(stripos($fn,"free")!==false) $diff=$max-($cur-$ini);
	if(!$secs) $diff=($diff*100)/$max;
	return $diff;
}

function make_indexing($id_aplicacion=null,$id_registro=null) {
	// CHECK PARAMETERS
	if($id_aplicacion===null) $id_aplicacion=page2id(getParam("page"));
	$tabla=id2table($id_aplicacion);
	if($tabla=="") return -1;
	$subtablas=id2subtables($id_aplicacion);
	if($id_registro===null) $id_registro=execute_query("SELECT MAX(id) FROM ${tabla}");
	if(is_string($id_registro) && strpos($id_registro,",")!==false) $id_registro=explode(",",$id_registro);
	if(is_array($id_registro)) {
		$last_result=0;
		foreach($id_registro as $id) $last_result=make_indexing($id_aplicacion,$id);
		return $last_result;
	}
	// BUSCAR SI EXISTE INDEXACION
	$query=make_select_query("tbl_indexing","id",make_where_query(array(
		"id_aplicacion"=>$id_aplicacion,
		"id_registro"=>$id_registro
	)));
	$id_indexing=execute_query($query);
	// SOME CHECKS
	if(is_array($id_indexing)) {
		$temp=$id_indexing;
		$id_indexing=array_pop($temp);
		foreach($temp as $temp2) {
			$query=make_delete_query("tbl_indexing",make_where_query(array(
				"id"=>$temp2
			)));
			db_query($query);
		}
	}
	// BUSCAR SI EXISTEN DATOS DE LA TABLA PRINCIPAL
	$query=make_select_query($tabla,"id",make_where_query(array(
		"id"=>$id_registro
	)));
	$id_data=execute_query($query);
	if(!$id_data) {
		if($id_indexing) {
			$query=make_delete_query("tbl_indexing",make_where_query(array(
				"id"=>$id_indexing
			)));
			db_query($query);
			return 3;
		} else {
			return -2;
		}
	}
	// CONTINUE
	$queries=array();
	// OBTENER DATOS DE LA TABLA PRINCIPAL
	$campos=__make_indexing_helper($tabla);
	foreach($campos as $key=>$val) $campos[$key]="IFNULL((${val}),'')";
	$campos="CONCAT(".implode(",' ',",$campos).")";
	$query=make_select_query($tabla,$campos,make_where_query(array(
		"id"=>$id_registro
	)));
	$queries[]=$query;
	// OBTENER DATOS DE LAS SUBTABLAS
	if($subtablas!="") {
		foreach(explode(",",$subtablas) as $subtabla) {
			$tabla=strtok($subtabla,"(");
			$campo=strtok(")");
			$campos=__make_indexing_helper($tabla);
			foreach($campos as $key=>$val) $campos[$key]="IFNULL((${val}),'')";
			$campos="GROUP_CONCAT(CONCAT(".implode(",' ',",$campos)."))";
			$query=make_select_query($tabla,$campos,make_where_query(array(
				$campo=>$id_registro
			)));
			$queries[]=$query;
		}
	}
	// OBTENER DATOS DE LAS TABLAS GENERICAS
	$tablas=array("tbl_ficheros","tbl_comentarios");
	foreach($tablas as $tabla) {
		$campos=__make_indexing_helper($tabla);
		foreach($campos as $key=>$val) $campos[$key]="IFNULL((${val}),'')";
		$campos="GROUP_CONCAT(CONCAT(".implode(",' ',",$campos)."))";
		$query=make_select_query($tabla,$campos,make_where_query(array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_registro
		)));
		$queries[]=$query;
	}
	// PREPARAR QUERY PRINCIPAL
	foreach($queries as $key=>$val) $queries[$key]="IFNULL((${val}),'')";
	$search="CONCAT(".implode(",' ',",$queries).")";
	// TO FIX THE ERROR CAUSED BY "DATA TOO LONG FOR COLUMN SEARCH"
	$search="/*MYSQL IF(LENGTH(${search})>=POW(2,24),SUBSTR(${search},1,POW(2,24)-1-LENGTH(${search})+CHAR_LENGTH(${search})),${search}) *//*SQLITE ${search} */";
	// AÑADIR A LA TABLA INDEXING
	if($id_indexing) {
		$query=make_update_query("tbl_indexing",array(),make_where_query(array(
			"id"=>$id_indexing
		)),array(
			"search"=>$search
		));
		db_query($query);
		return 2;
	} else {
		$query=make_insert_query("tbl_indexing",array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_registro
		),array(
			"search"=>$search
		));
		db_query($query);
		return 1;
	}
}

function __make_indexing_helper($tabla) {
	static $tables=null;
	static $types=null;
	static $campos=null;
	static $fields=null;
	if($tables===null) {
		$file="xml/dbschema.xml";
		$dbschema=eval_attr(xml2array($file));
		$tables=array();
		if(is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
			foreach($dbschema["tables"] as $tablespec) {
				$tables[$tablespec["name"]]=array();
				$types[$tablespec["name"]]=array();
				foreach($tablespec["fields"] as $fieldspec) if(isset($fieldspec["fkey"])) {
					$tables[$tablespec["name"]][$fieldspec["name"]]=$fieldspec["fkey"];
					$types[$tablespec["name"]][$fieldspec["name"]]=get_field_type($fieldspec["type"]);
				}
			}
		}
	}
	if($campos===null) {
		$file="xml/dbstatic.xml";
		$dbstatic=eval_attr(xml2array($file));
		$campos=array();
		if(is_array($dbstatic) && isset($dbstatic["tbl_aplicaciones"]) && is_array($dbstatic["tbl_aplicaciones"])) {
			foreach($dbstatic["tbl_aplicaciones"] as $row) {
				if(isset($row["tabla"]) && isset($row["campo"])) {
					if(substr($row["campo"],0,1)=='"' && substr($row["campo"],-1,1)=='"') $row["campo"]=eval_protected($row["campo"]);
					$campos[$row["tabla"]]=$row["campo"];
				}
			}
		}
	}
	if($fields===null) {
		$file="xml/dbschema.xml";
		$dbschema=eval_attr(xml2array($file));
		$fields=array();
		if(is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
			foreach($dbschema["tables"] as $tablespec) {
				$fields[$tablespec["name"]]=array();
				foreach($tablespec["fields"] as $fieldspec) $fields[$tablespec["name"]][]=$fieldspec["name"];
			}
		}
	}
	if(!isset($fields[$tabla])) {
		$fields[$tabla]=array();
		foreach(get_fields($tabla) as $field) $fields[$tabla][]=$field["name"];
	}
	$result=$fields[$tabla];
	$result[]="LPAD(id,".intval(CONFIG("zero_padding_digits")).",0)";
	if(isset($campos[$tabla])) $result[]=$campos[$tabla];
	if(isset($tables[$tabla])) {
		foreach($tables[$tabla] as $key=>$val) {
			if(isset($campos[$val])) {
				$campo=$campos[$val];
			} elseif(isset($fields[$val])) {
				$campo="CONCAT(".implode(",' ',",$fields[$val]).")";
			} else {
				$campo="";
			}
			$type=$types[$tabla][$key];
			if($type=="int") {
				$where="${val}.id=${key}";
			} elseif($type=="string") {
				$where="FIND_IN_SET(${val}.id,${key})";
				$campo="GROUP_CONCAT(${campo})";
			} else {
				$where="";
			}
			if($campo!="" && $where!="") {
				$result[]="(".make_select_query($val,$campo,$where).")";
			}
		}
	}
	return $result;
}

function make_control($id_aplicacion=null,$id_registro=null,$id_usuario=null,$datetime=null) {
	// CHECK PARAMETERS
	if($id_aplicacion===null) $id_aplicacion=page2id(getParam("page"));
	$tabla=id2table($id_aplicacion);
	if($tabla=="") return -1;
	if($id_registro===null) $id_registro=execute_query("SELECT MAX(id) FROM ${tabla}");
	if($id_usuario===null) $id_usuario=current_user();
	if($datetime===null) $datetime=current_datetime();
	if(is_string($id_registro) && strpos($id_registro,",")!==false) $id_registro=explode(",",$id_registro);
	if(is_array($id_registro)) {
		$last_result=0;
		foreach($id_registro as $id) $last_result=make_control($id_aplicacion,$id,$id_usuario,$datetime);
		return $last_result;
	}
	// BUSCAR SI EXISTE REGISTRO DE CONTROL
	$query=make_select_query("tbl_registros","id",make_where_query(array(
		"id_aplicacion"=>$id_aplicacion,
		"id_registro"=>$id_registro,
		"first"=>1
	)));
	$id_control=execute_query($query);
	// SOME CHECKS
	if(is_array($id_control)) {
		$temp=$id_control;
		$id_control=array_pop($temp);
		foreach($temp as $temp2) {
			$query=make_delete_query("tbl_registros",make_where_query(array(
				"id"=>$temp2
			)));
			db_query($query);
		}
	}
	// BUSCAR SI EXISTEN DATOS DE LA TABLA PRINCIPAL
	$query=make_select_query($tabla,"id",make_where_query(array(
		"id"=>$id_registro
	)));
	$id_data=execute_query($query);
	if(!$id_data) {
		if($id_control) {
			$query=make_delete_query("tbl_registros",make_where_query(array(
				"id_aplicacion"=>$id_aplicacion,
				"id_registro"=>$id_registro
			)));
			db_query($query);
			return 3;
		} else {
			return -2;
		}
	}
	if($id_control) {
		$query=make_insert_query("tbl_registros",array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_registro,
			"id_usuario"=>$id_usuario,
			"datetime"=>$datetime,
			"first"=>0
		));
		db_query($query);
		return 2;
	} else {
		$query=make_insert_query("tbl_registros",array(
			"id_aplicacion"=>$id_aplicacion,
			"id_registro"=>$id_registro,
			"id_usuario"=>$id_usuario,
			"datetime"=>$datetime,
			"first"=>1
		));
		db_query($query);
		return 1;
	}
}

function ICON($icon) {
	global $_CONFIG;
	if(!isset($_CONFIG["icons"])) $_CONFIG["icons"]=xml2array("xml/icons.xml");
	if(isset($_CONFIG["icons"][$icon])) return $_CONFIG["icons"][$icon];
	return "fa fa-tag";
}

function is_disabled_function($fn="") {
	static $disableds_string=null;
	static $disableds_array=array();
	if($disableds_string===null) {
		$disableds_string=ini_get("disable_functions").",".ini_get("suhosin.executor.func.blacklist");
		$disableds_array=$disableds_string?explode(",",$disableds_string):array();
		foreach($disableds_array as $key=>$val) {
			$val=strtolower(trim($val));
			if($val=="") unset($disableds_array[$key]);
			if($val!="") $disableds_array[$key]=$val;
		}
	}
	return in_array($fn,$disableds_array);
}
?>