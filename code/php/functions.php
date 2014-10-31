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
function pre_datauser() {
	global $_USER;
	$user=useSession("user");
	$pass=useSession("pass");
	if($user!="" && $pass!="") {
		reset_datauser();
		$query="SELECT * FROM tbl_usuarios WHERE activo='1' AND login='${user}' AND password='${pass}'";
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
		$query="SELECT * FROM tbl_usuarios WHERE id='${_USER["id"]}'";
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

function check_sql($aplicacion="",$permiso="",$prefix="") {
	// INITIAL CHECK
	global $_ERROR;
	if(isset($_ERROR)) return 0;
	// BEGIN NORMAL CODE
	global $_USER;
	static $stack=array();
	$hash=md5(serialize(array($aplicacion,$permiso)));
	if(!isset($stack[$hash])) {
		if($permiso!="") {
			$subquery=array();
			// CHECK FOR USER/GROUP/ALL PERMISSIONS
			$queries=array();
			$queries["all"]="(1=1)";
			$queries["group"]="(id_grupo='${_USER["id_grupo"]}' OR id_grupo IN (".check_ids(execute_query("SELECT id_grupo FROM tbl_usuarios_g WHERE id_usuario='${_USER["id"]}'"))."))";
			$queries["user"]="(id_usuario='${_USER["id"]}')";
			foreach($queries as $key=>$val) {
				if(check_user($aplicacion,"${key}_${permiso}")) $subquery[]=$val;
			}
		}
		// CHECK FOR APLICATION/REGISTER FILTERS
		$query="SELECT a.id_aplicacion id_aplicacion,a.id_registro id_registro,b.filter1 filter FROM (
			SELECT id_aplicacion,id_registro
			FROM tbl_usuarios
			WHERE id='${_USER["id"]}'
			UNION
			SELECT id_aplicacion,id_registro
			FROM tbl_usuarios_r
			WHERE id_usuario='${_USER["id"]}'
		) a
		LEFT JOIN tbl_aplicaciones b ON a.id_aplicacion=b.id
		WHERE a.id_aplicacion IN (
			SELECT id
			FROM tbl_aplicaciones
			WHERE (SELECT CONCAT(',',filter2,',')
				FROM tbl_aplicaciones
				WHERE codigo='$aplicacion' AND filter2!=''
			) LIKE (
				CONCAT('%,',filter1,',%')
			) AND filter1!=''
		)";
		$result=db_query($query);
		$id_aplicacion=page2id($aplicacion);
		if($permiso!="") {
			$filters=array();
			while($row=db_fetch_row($result)) {
				if(check_user($aplicacion,"user_${permiso}")) {
					$filter=($id_aplicacion==$row["id_aplicacion"])?"${prefix}id":$row["filter"];
					if(!isset($filters[$filter])) $filters[$filter]=array();
					$filters[$filter][]=$row["id_registro"];
				}
			}
			db_free($result);
			foreach($filters as $key=>$val) {
				$subquery[]="(".$key." IN (".implode(",",$val)."))";
			}
			$filters=implode(" OR ",$subquery);
			if(!$filters) $filters="1=0";
			$filters="($filters)";
		}
		if($permiso=="") {
			$filters=array();
			while($row=db_fetch_row($result)) {
				if(!in_array($row["filter"],$filters) && $id_aplicacion!=$row["id_aplicacion"]) $filters[]=$row["filter"];
			}
			db_free($result);
			$filters=implode(",",$filters);
			if(!$filters) $filters="'0' __check_sql_${aplicacion}__";
		}
		// STORE RESULT
		$stack[$hash]=$filters;
	}
	// RETURN RESULT
	return $stack[$hash];
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
	$servername=getDefault("server/hostname");
	if(!$servername) $servername=getServer("SERVER_NAME");
	$added="";
	$scriptname=getServer("SCRIPT_NAME");
	$querystring=str_replace("+","%20",getServer("QUERY_STRING"));
	// SOME CHECKS
	if($querystring) $querystring="?".$querystring;
	if($porthttps!=443) $added=":${porthttps}";
	// CONTINUE
	$url=$protocol.$servername.$added.$scriptname.$querystring;
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
	return str_replace("'","\\'",LANG($key,$arg));
}

function CONFIG_LOADED() {
	global $_CONFIG;
	return isset($_CONFIG);
}

function CONFIG($key) {
	$row=array();
	$query="SELECT valor FROM tbl_configuracion WHERE clave='$key'";
	$result=db_query($query);
	if(db_num_rows($result)==1) {
		$row=db_fetch_row($result);
		$row=array($key=>$row["valor"]);
	} else {
		$row=getDefault("configs");
	}
	db_free($result);
	if(!isset($row[$key])) return null;
	return $row[$key];
}

function setConfig($key,$val) {
	$query="SELECT valor FROM tbl_configuracion WHERE clave='$key'";
	$config=execute_query($query);
	if($config===null) {
		$query="INSERT INTO tbl_configuracion(`id`,`clave`,`valor`) VALUES(NULL,'$key','$val')";
		db_query($query);
	} else {
		$query="UPDATE tbl_configuracion SET valor='$val' WHERE clave='$key'";
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
		$query="SELECT `id`,`codigo`,`table` FROM tbl_aplicaciones";
		$result=db_query($query);
		$diccionario["page2id"]=array();
		$diccionario["id2page"]=array();
		$diccionario["page2table"]=array();
		$diccionario["id2table"]=array();
		while($row=db_fetch_row($result)) {
			$diccionario["page2id"][$row["codigo"]]=$row["id"];
			$diccionario["id2page"][$row["id"]]=$row["codigo"];
			$diccionario["page2table"][$row["codigo"]]=$row["table"];
			$diccionario["table2page"][$row["table"]]=$row["codigo"];
			$diccionario["id2table"][$row["id"]]=$row["table"];
			$diccionario["table2id"][$row["table"]]=$row["id"];
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

function table2page($page) {
	return __aplicaciones(__FUNCTION__,$page);
}

function id2table($id) {
	return __aplicaciones(__FUNCTION__,$id);
}

function table2id($id) {
	return __aplicaciones(__FUNCTION__,$id);
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

function get_use_cache($query="") {
	static $max=0;
	static $maxs=array();
	$usecache=getDefault("db/usecache");
	if(!$query) return $usecache;
	if(!eval_bool($usecache)) return $usecache;
	$nocaches=getDefault("db/nocaches");
	if(is_array($nocaches)) {
		if(!$max) {
			foreach($nocaches as $key=>$nocache) $maxs[$key]=str_word_count($nocache);
			$max=max($maxs);
		}
		$words=array(strtoupper(strtok($query," ")));
		for($i=1;$i<$max;$i++) $words[]=strtoupper(strtok(" "));
		foreach($nocaches as $key=>$nocache) {
			$word=strtoupper(strtok($nocache," "));
			$pos=0;
			while($pos<$maxs[$key] && $word==$words[$pos]) {
				$word=strtoupper(strtok(" "));
				$pos++;
			}
			if($pos==$maxs[$key]) {
				$usecache="false";
				break;
			}
		}
	}
	return $usecache;
}

function set_use_cache($bool) {
	global $_CONFIG;
	$result=getDefault("db/usecache");
	$_CONFIG["db"]["usecache"]=$bool;
	return $result;
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
	$hash2=md5(serialize(xml2array($file)));
	if($hash1!=$hash2) {
		if(!semaphore_acquire(array("db_schema","db_static"),getDefault("semaphoretimeout",100000))) return;
		$oldcache=set_use_cache("false");
		$dbschema=eval_attr(xml2array($file));
		if(is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
			$tables1=get_tables();
			$tables2=get_tables_from_dbschema();
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
					$hash3=md5(serialize($fields1));
					$hash4=md5(serialize($fields2));
					if($hash3!=$hash4) {
						db_query(sql_alter_table($table,$backup));
						db_query(sql_create_table($tablespec));
						db_query(sql_insert_from_select($table,$backup));
						db_query(sql_drop_table($backup));
					}
				} elseif(in_array($backup,$tables1)) {
					$fields1=get_fields($backup);
					$fields2=get_fields_from_dbschema($table);
					$hash3=md5(serialize($fields1));
					$hash4=md5(serialize($fields2));
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
								$hash3=md5(serialize($fields1));
								$hash4=md5(serialize($fields2));
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
		set_use_cache($oldcache);
		semaphore_release(array("db_schema","db_static"));
	}
}

function db_static() {
	if(!eval_bool(getDefault("db/dbstatic"))) return;
	$file="xml/dbstatic.xml";
	$hash1=CONFIG($file);
	$hash2=md5(serialize(xml2array($file)));
	if($hash1!=$hash2) {
		if(!semaphore_acquire(array("db_schema","db_static"),getDefault("semaphoretimeout",100000))) return;
		$oldcache=set_use_cache("false");
		$dbstatic=eval_attr(xml2array($file));
		if(is_array($dbstatic)) {
			foreach($dbstatic as $table=>$rows) {
				$query="DELETE FROM `$table`";
				db_query($query);
				foreach($rows as $row) {
					$keys=array();
					$vals=array();
					foreach($row as $key=>$val) {
						$keys[]="`${key}`";
						$vals[]=($val=="NULL")?$val:"'${val}'";
					}
					$keys=implode(",",$keys);
					$vals=implode(",",$vals);
					$query="INSERT INTO `$table`($keys) VALUES($vals)";
					db_query($query);
				}
			}
		}
		setConfig($file,$hash2);
		set_use_cache($oldcache);
		semaphore_release(array("db_schema","db_static"));
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

function check_ids($value) {
	$value=is_array($value)?$value:explode(",",$value);
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

function load_iconset($iconset) {
	global $_CONFIG;
	if(!isset($_CONFIG["iconset"])) $_CONFIG["iconset"]=xml2array("xml/iconset.xml");
	return is_array(getDefault("iconset/$iconset"));
}

function load_style($style) {
	global $_CONFIG;
	if(!isset($_CONFIG["themeroller"])) $_CONFIG["themeroller"]=xml2array("xml/themeroller.xml");
	return getDefault("themeroller/themes/$style")!==null;
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
				$query="UPDATE tbl_usuarios SET password='${pass}' WHERE activo='1' AND login='${user}'";
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
	$query="SELECT id FROM tbl_security WHERE id_session='${id_session}'";
	$oldcache=set_use_cache("false");
	$id_security=execute_query($query);
	set_use_cache($oldcache);
	if(is_array($id_security)) {
		$query="DELETE FROM tbl_security WHERE id_session='${id_session}'";
		db_query($query);
		$id_security=0;
	}
	if(!$id_security) {
		$query="INSERT INTO tbl_security(id,id_session,id_usuario,logout) VALUES(NULL,'${id_session}','0','0')";
		db_query($query);
		$query="SELECT MAX(id) maximo FROM tbl_security";
		$id_security=execute_query($query);
	}
	// BUSCAR ID_SECURITY_IP
	$query="SELECT id FROM tbl_security_ip WHERE id_session='${id_session}' AND remote_addr='${remote_addr}'";
	$oldcache=set_use_cache("false");
	$id_security_ip=execute_query($query);
	set_use_cache($oldcache);
	if(is_array($id_security_ip)) {
		$query="DELETE FROM tbl_security_ip WHERE id_session='${id_session}' AND remote_addr='${remote_addr}'";
		db_query($query);
		$id_security_ip=0;
	}
	if(!$id_security_ip) {
		$query="INSERT INTO tbl_security_ip(id,id_session,remote_addr,retries) VALUES(NULL,'${id_session}','${remote_addr}','0')";
		db_query($query);
		$query="SELECT MAX(id) maximo FROM tbl_security_ip";
		$id_security_ip=execute_query($query);
	}
	// BORRAR REGISTROS CADUCADOS
	$query="DELETE FROM tbl_security WHERE NOT id_session IN (SELECT id FROM tbl_sessions)";
	db_query($query);
	$query="DELETE FROM tbl_security_ip WHERE NOT id_session IN (SELECT id FROM tbl_sessions)";
	db_query($query);
	// NORMAL CODE
	if($action=="login") {
		if($id_usuario) {
			// MARCAR REGISTROS DEL ID_USUARIO PARA LOGOUT
			$query="UPDATE tbl_security SET logout='1' WHERE id_usuario='${id_usuario}'";
			db_query($query);
			// LIMPIAR RETRIES DEL MISMO REMOTE_ADDR
			$query="UPDATE tbl_security_ip SET retries='0' WHERE id_session='${id_session}' OR remote_addr='${remote_addr}'";
			db_query($query);
			// PONER ID_USUARIO Y RESETEAR LOGOUT EN EL REGISTRO ACTUAL
			$query="UPDATE tbl_security SET id_usuario='${id_usuario}',logout='0' WHERE id='${id_security}'";
			db_query($query);
		} else {
			// INCREMENTAR RETRIES EN EL REGISTRO ACTUAL
			$query="UPDATE tbl_security_ip SET retries=retries+1 WHERE id='${id_security_ip}'";
			db_query($query);
		}
	} elseif($action=="logout") {
		// RESETEAR ID_USUARIO Y LOGOUT EN EL REGISTRO ACTUAL
		$query="UPDATE tbl_security SET id_usuario='0',logout='0' WHERE id='${id_security}'";
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
		$query="UPDATE tbl_security_ip SET retries=0 WHERE id='${id_security_ip}'";
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
				$query="DELETE FROM tbl_cookies WHERE id_usuario='$uid' AND clave='$name'";
				db_query($query);
				$count=0;
			}
			if($count==0) {
				$query="INSERT INTO tbl_cookies(id,id_usuario,clave,valor) VALUES(NULL,'$uid','$name','$value')";
				db_query($query);
			} else {
				$query="UPDATE tbl_cookies SET valor='$value' WHERE id_usuario='$uid' AND clave='$name'";
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
	// TRICK TO PREVENT THAT SHUTDOWN_HANDLER CAPTURES THE ERROR
	$error=error_get_last();
	if(is_array($error) && isset($error["type"]) && $error["type"]!=E_USER_NOTICE) {
		set_error_handler("var_dump",0);
		ob_start();
		$olderror=error_reporting(0);
		trigger_error("");
		error_reporting($olderror);
		ob_end_clean();
		restore_error_handler();
	}
	// CONTINUE
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
	if(isset($array["dberror"])) $msg[]=array("DB Error",$array["dberror"]);
	if(isset($array["emailerror"])) $msg[]=array("EMAIL Error",$array["emailerror"]);
	if(isset($array["fileerror"])) $msg[]=array("FILE Error",$array["fileerror"]);
	if(isset($array["source"])) $msg[]=array("XML Source",$array["source"]);
	if(isset($array["exception"])) $msg[]=array("Exception",$array["exception"]);
	if(isset($array["details"])) $msg[]=array("Details",$array["details"]);
	if(isset($array["query"])) $msg[]=array("Query",$array["query"]);
	if(isset($array["backtrace"])) {
		foreach($array["backtrace"] as $key=>$item) {
			$temp="${key} => ${item["function"]}";
			if(isset($item["class"])) $temp.=" (in class ${item["class"]})";
			if(isset($item["file"]) && isset($item["line"])) $temp.=" (in file ${item["file"]} at line ${item["line"]})";
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
	if($array===null) $array=$backup;
	if($array===null) return;
	// ADD BACKTRACE IF NOT FOUND
	if(!isset($array["backtrace"])) $array["backtrace"]=debug_backtrace();
	// ADD DEBUG IF NOT FOUND
	if(!isset($array["debug"])) {
		$array["debug"]=array();
		if(useSession("user")) $array["debug"]["user"]=useSession("user");
		foreach(array("page","action","id") as $item) if(getParam($item)) $array["debug"][$item]=getParam($item);
		if(!count($array["debug"])) unset($array["debug"]);
	}
	// PROTECTION OF SENSITIVE DATA
	foreach($array as $key=>$val) $array[$key]=str_replace(array(getDefault("db/host"),getDefault("db/port"),getDefault("db/user"),getDefault("db/pass"),getDefault("db/name")),"...",$val);
	// CREATE THE MESSAGE ERROR USING HTML ENTITIES AND PLAIN TEXT
	$msg_html=do_message_error($array,"html");
	$msg_text=do_message_error($array,"text");
	$msg=getServer("SHELL")?$msg_text:$msg_html;
	// REFUSE THE DEPRECATED WARNINGS
	if(isset($array["phperror"]) && stripos($array["phperror"],"deprecated")!==false) {
		$hash=md5($msg_text);
		$file=isset($array["file"])?$array["file"]:getDefault("debug/warningfile","warning.log");
		if(checklog($hash,$file)) $msg_text="";
		addlog("${msg_text}***** ${hash} *****",$file);
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
	$file=isset($array["file"])?$array["file"]:getDefault("debug/errorfile","error.log");
	if(checklog($hash,$file)) $msg_text="";
	addlog("${msg_text}***** ${hash} *****",$file);
	// CHECK FOR CANCEL_DIE
	if(isset($array["cancel"]) && eval_bool($array["cancel"])) return;
	if(isset($array["die"]) && !eval_bool($array["die"])) return;
	// PREPARE THE FINAL REPORT (ONLY IN NOT SHELL MODE)
	if(!getServer("SHELL")) {
		$msg=pretty_html_error($msg);
		if(!headers_sent()) {
			header_powered();
			header_expires(false);
			header("Content-Type: text/html");
		}
	}
	// DUMP TO STDOUT
	while(ob_get_level()) ob_end_clean(); // TRICK TO CLEAR SCREEN
	echo $msg;
	die();
}

function pretty_html_error($msg) {
	$html="<html>";
	$html.="<head>";
	$html.="<title>".get_name_version_revision()."</title>";
	$html.="<style>";
	$html.=".phperror { background:#eee; color:#000; padding:20px; font-family:Helvetica,Arial,sans-serif; }";
	$html.=".phperror div { width:80%; margin:0 auto; background:#fff; padding:20px 40px; border:1px solid #aaa; border-radius:5px; text-align:left; }";
	$favicon=getDefault("info/favicon","img/favicon.png");
	if(file_exists($favicon) && memory_get_free(true)>filesize($favicon)*4/3) $favicon="data:".saltos_content_type($favicon).";base64,".base64_encode(file_get_contents($favicon));
	$html.=".phperror h3 { background:url(${favicon}) top left no-repeat; padding-left: 48px; height:32px; font-size:24px; margin:0; }";
	$html.=".phperror pre { white-space:pre-wrap; font-size:10px; }";
	$html.=".phperror form { display:inline; float:right; }";
	$html.=".phperror a { color:#00c; }";
	$html.=".phperror form a { margin-left:12px; font-size:12px; }";
	$html.="</style>";
	$html.="</head>";
	$html.="<body class='phperror'>";
	$html.="<div>";
	$bug=base64_encode(serialize(array("app"=>get_name_version_revision(),"msg"=>$msg)));
	$html.=__pretty_html_error_helper("http://bugs.saltos.net",array("bug"=>$bug),LANG_LOADED()?LANG("notifybug"):"Notify bug");
	$html.=__pretty_html_error_helper("",array("page"=>"home"),LANG_LOADED()?LANG("gotohome"):"Go to home");
	$html.=$msg;
	$html.="</div>";
	$html.="</body>";
	$html.="</html>";
	return $html;
}

function __pretty_html_error_helper($action,$hiddens,$submit) {
	$html="";
	$html.="<form action='${action}' method='post'>";
	foreach($hiddens as $key=>$val) $html.="<input type='hidden' name='${key}' value='${val}'/>";
	$html.="<input type='submit' value='${submit}'/>";
	$html.="</form>";
	return $html;
}

function __error_handler($type,$message,$file,$line) {
	$backtrace=debug_backtrace();
	show_php_error(array("phperror"=>"${message} (code ${type})","details"=>"Error on file '${file}' at line ${line}","backtrace"=>$backtrace));
}

function __exception_handler($e) {
	$backtrace=$e->getTrace();
	show_php_error(array("exception"=>$e->getMessage()." (code ".$e->getCode().")","details"=>"Error on file '".$e->getFile()."' at line ".$e->getLine(),"backtrace"=>$backtrace));
}

function __shutdown_handler() {
	semaphore_shutdown();
	$error=error_get_last();
	$types=array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR);
	if(is_array($error) && isset($error["type"]) && in_array($error["type"],$types)) {
		global $_ERROR_HANDLER;
		$_ERROR_HANDLER=array("level"=>0,"msg"=>array());
		$backtrace=debug_backtrace();
		show_php_error(array("phperror"=>"${error["message"]}","details"=>"Error on file '${error["file"]}' at line ${error["line"]}","backtrace"=>$backtrace));
	}
}

function __tick_handler() {
	if(time_get_free()<getDefault("server/percenterror")) {
		$cur=ini_get("max_execution_time");
		$max=getDefault("server/max_execution_time");
		if($cur>0 && $max>0) {
			$cur=min($cur*(1+getDefault("server/percentincr")/100),$max);
			//~ addlog("max_execution_time, max=$max, cur=$cur");
			capture_next_error();
			ini_set("max_execution_time",$cur);
			get_clear_error();
		}
		if(time_get_free()<getDefault("server/percenterror")) {
			$backtrace=debug_backtrace();
			show_php_error(array("phperror"=>"max_execution_time reached","backtrace"=>$backtrace));
		}
	}
	if(memory_get_free()<getDefault("server/percenterror")) {
		$cur=normalize_value(ini_get("memory_limit"));
		$max=normalize_value(getDefault("server/memory_limit"));
		if($cur>0 && $max>0) {
			$cur=min($cur*(1+getDefault("server/percentincr")/100),$max);
			//~ addlog("memory_limit, max=$max, cur=$cur");
			capture_next_error();
			ini_set("memory_limit",$cur);
			get_clear_error();
		}
		if(memory_get_free()<getDefault("server/percenterror")) {
			$backtrace=debug_backtrace();
			show_php_error(array("phperror"=>"memory_limit reached","backtrace"=>$backtrace));
		}
	}
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
	if(isphp(5.3) && !ishhvm()) register_tick_function("__tick_handler");
}

function init_random() {
	static $init=false;
	if($init) return;
	srand((float)microtime(true)*1000000);
	$init=true;
}

function check_postlimit() {
	$content_length=getServer("CONTENT_LENGTH");
	if($content_length) {
		$post_max_size=ini_get("post_max_size");
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
	$memory_usage=memory_get_usage(true);
	$diff=$memory_limit-$memory_usage;
	if(!$bytes) $diff=($diff*100)/$memory_limit;
	return $diff;
}

function debug($name,$end=0) {
	static $stack=array();
	if(!isset($stack[$name])) $stack[$name]=array();
	$home=microtime(true);
	if(!$end) {
		$stack[$name][]=$home;
		file_put_contents("files/debug.txt",sprintf("%f: timer %s start\n",$home,$name),FILE_APPEND);
	} else {
		$diff=$home-array_pop($stack[$name]);
		file_put_contents("files/debug.txt",sprintf("%f: timer %s stop: %f\n",$home,$name,$diff),FILE_APPEND);
	}
}

function debugEnd($name) {
	debug($name,1);
}

function usleep_protected($usec) {
	if(function_exists("socket_create")) {
		$socket=socket_create(AF_UNIX,SOCK_STREAM,0);
		$read=null;
		$write=null;
		$except=array($socket);
		capture_next_error();
		$time1=microtime(true);
		socket_select($read,$write,$except,intval($usec/1000000),intval($usec%1000000));
		$time2=microtime(true);
		get_clear_error();
		return ($time2-$time1)*1000000;
	}
	$time1=microtime(true);
	usleep($usec);
	$time2=microtime(true);
	return ($time2-$time1)*1000000;
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
	if(!$max) $max=getDefault("server/max_execution_time");
	if(!$max) $max=600;
	if(stripos($fn,"usage")!==false) $diff=$cur-$ini;
	elseif(stripos($fn,"free")!==false) $diff=$max-($cur-$ini);
	if(!$secs) $diff=($diff*100)/$max;
	return $diff;
}
?>