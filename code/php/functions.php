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
function pre_datauser() {
	global $_USER;
	$user=useSession("user");
	$pass=useSession("pass");
	if($user!="" && $pass!="") {
		$_USER=array("id"=>0);
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
				$_USER[$aplicacion][$permiso]=(isset($_USER[$aplicacion][$permiso])?$_USER[$aplicacion][$permiso]:1) && $row2["allow"] && !$row2["deny"];
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
	if($hora<$_USER["hora_ini"] || $hora>$_USER["hora_fin"]) {
		set_array($_ERROR,"error",LANG("nochecktime"));
		unset($_USER);
	} else {
		$dia=(date("w",time())+6)%7;
		if($_USER["dias_sem"][$dia]=="0") {
			set_array($_ERROR,"error",LANG("nocheckday"));
			unset($_USER);
		}
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

function check_sql($aplicacion="",$permiso="") {
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
		$query="SELECT * FROM (
			SELECT a.id_aplicacion id_aplicacion,a.id_registro id_registro,b.filter1 filter
			FROM tbl_usuarios a
			LEFT JOIN tbl_aplicaciones b ON a.id_aplicacion=b.id
			WHERE a.id='${_USER["id"]}'
			UNION
			SELECT a.id_aplicacion id_aplicacion,a.id_registro id_registro,b.filter1 filter
			FROM tbl_usuarios_r a
			LEFT JOIN tbl_aplicaciones b ON a.id_aplicacion=b.id
			WHERE a.id_usuario='${_USER["id"]}'
		) a
		WHERE id_aplicacion IN (
			SELECT id
			FROM tbl_aplicaciones
			WHERE (SELECT /*MYSQL CONCAT(',',filter2,',') *//*SQLITE (',' || filter2 || ',') */
				FROM tbl_aplicaciones
				WHERE codigo='$aplicacion' AND filter2!=''
			) LIKE (
				/*MYSQL CONCAT('%,',filter1,',%') *//*SQLITE '%,' || filter1 || ',%' */
			) AND filter1!=''
		)";
		$result=db_query($query);
		$id_aplicacion=page2id($aplicacion);
		if($permiso!="") {
			$filters=array();
			while($row=db_fetch_row($result)) {
				if(check_user($aplicacion,"user_${permiso}")) {
					$filter=($id_aplicacion==$row["id_aplicacion"])?"id":$row["filter"];
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
				$result=ini_set($key,$val);
				if($result===false) {
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
				$result=putenv($key."=".$val);
				if($result===false) {
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
	$servername=getServer("SERVER_NAME");
	$added="";
	$scriptname=getServer("SCRIPT_NAME");
	$querystring=getServer("QUERY_STRING");
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

function getConfig($key) {
	return CONFIG($key);
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
			$diccionario["id2table"][$row["id"]]=$row["table"];
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

function id2table($id) {
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
	$semaphore=get_cache_file("cache_gc",getDefault("exts/semext",".sem"));
	if(!semaphore_acquire($semaphore,getDefault("semaphoretimeout",100000))) return;
	$files=glob(get_directory("dirs/cachedir")."*");
	if(is_array($files) && count($files)>0) {
		$delta=time()-intval(getDefault("cache/cachegctimeout"));
		foreach($files as $file) {
			list($mtime,$error)=filemtime_protected($file);
			if(!$error && $delta>$mtime) unlink_protected($file);
		}
	}
	semaphore_release($semaphore);
}

function db_schema() {
	if(!eval_bool(getDefault("db/dbschema"))) return;
	$file="xml/dbschema.xml";
	$dbschema=eval_attr(xml2array($file));
	capture_next_error();
	$hash1=getConfig($file);
	get_clear_error();
	$hash2=md5(serialize($dbschema));
	if($hash1!=$hash2) {
		$semaphore=get_cache_file(array("db_schema","db_static"),getDefault("exts/semext",".sem"));
		if(!semaphore_acquire($semaphore,getDefault("semaphoretimeout",100000))) return;
		$oldcache=set_use_cache("false");
		if(isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
			$tables1=get_tables();
			$tables2=array();
			foreach($dbschema["tables"] as $tablespec) $tables2[]=$tablespec["name"];
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
					$fields2=array();
					foreach($tablespec["fields"] as $fieldspec) $fields2[]=array("name"=>$fieldspec["name"],"type"=>get_field_type($fieldspec["type"]));
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
					$fields2=array();
					foreach($tablespec["fields"] as $fieldspec) $fields2[]=array("name"=>$fieldspec["name"],"type"=>get_field_type($fieldspec["type"]));
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
		semaphore_release($semaphore);
	}
}

function db_static() {
	if(!eval_bool(getDefault("db/dbstatic"))) return;
	$file="xml/dbstatic.xml";
	$dbstatic=eval_attr(xml2array($file));
	$hash1=getConfig($file);
	$hash2=md5(serialize($dbstatic));
	if($hash1!=$hash2) {
		$semaphore=get_cache_file(array("db_schema","db_static"),getDefault("exts/semext",".sem"));
		if(!semaphore_acquire($semaphore,getDefault("semaphoretimeout",100000))) return;
		$oldcache=set_use_cache("false");
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
		semaphore_release($semaphore);
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
	foreach($value as $key=>$val) $value[$key]=abs($val);
	$value=count($value)?implode(",",$value):"0";
	return $value;
}

function load_iconset($iconset) {
	global $_CONFIG;
	if(!isset($_CONFIG["iconset"])) $_CONFIG["iconset"]=xml2array("xml/iconset.xml");
	return is_array(getDefault("iconset/$iconset"));
}

function check_remember() {
	if(!eval_bool(getDefault("security/allowremember"))) return;
	if(!useCookie("remember")) return;
	if(useSession("user")) return;
	if(useSession("pass")) return;
	useSession("user",useCookie("user"));
	useSession("pass",useCookie("pass"));
	pre_datauser();
	check_security("remember");
	return;
}

function check_security($action="") {
	if(!eval_bool(getDefault("security/filterenabled"))) return true;
	$id_session=current_session();
	$id_usuario=current_user();
	$remote_addr=getServer("REMOTE_ADDR");
	// BUSCAR ID_SECURITY
	$oldcache=set_use_cache("false");
	$query="SELECT id FROM tbl_security WHERE id_session='${id_session}' AND REMOTE_ADDR='${remote_addr}'";
	$id_security=execute_query($query);
	set_use_cache($oldcache);
	if(!$id_security) {
		$query="INSERT INTO tbl_security(id,id_session,id_usuario,remote_addr,retryes,logout) VALUES(NULL,'${id_session}','0','${remote_addr}','0','0')";
		db_query($query);
		$query="SELECT MAX(id) maximo FROM tbl_security";
		$id_security=execute_query($query);
	}
	// BORRAR REGISTROS CADUCADOS
	$query="SELECT a.id FROM tbl_security a LEFT JOIN tbl_sessions b ON a.id_session=b.id WHERE b.id IS NULL";
	$result=execute_query($query);
	if($result) {
		if(is_array($result)) $result=implode(",",$result);
		$query="DELETE FROM tbl_security WHERE id IN ($result)";
		db_query($query);
	}
	// NORMAL CODE
	if($action=="login") {
		if($id_usuario) {
			// MARCAR REGISTROS DEL ID_USUARIO PARA LOGOUT
			$query="UPDATE tbl_security SET logout='1' WHERE id_usuario='${id_usuario}'";
			db_query($query);
			// LIMPIAR REGISTROS DEL MISMO REMOTE_ADDR
			$query="UPDATE tbl_security SET retryes='0' WHERE remote_addr='${remote_addr}'";
			db_query($query);
			// PONER ID_USUARIO Y RETRYES EN EL REGISTRO ACTUAL
			$query="UPDATE tbl_security SET id_usuario='${id_usuario}',retryes='0',logout='0' WHERE id='${id_security}'";
			db_query($query);
		} else {
			// INCREMENTAR RETRYES EN EL REGISTRO ACTUAL
			$query="UPDATE tbl_security SET retryes=retryes+1 WHERE id='${id_security}'";
			db_query($query);
		}
	} elseif($action=="logout") {
		// RESETEAR ID_USUARIO Y RETRYES EN EL REGISTRO ACTUAL
		$query="UPDATE tbl_security SET id_usuario='0',retryes='0',logout='0' WHERE id='${id_security}'";
		db_query($query);
	} elseif($action=="remember") {
		if($id_usuario) {
			// MARCAR REGISTROS DEL ID_USUARIO PARA LOGOUT
			$query="UPDATE tbl_security SET logout='1' WHERE id_usuario='${id_usuario}'";
			db_query($query);
			// LIMPIAR REGISTROS DEL MISMO REMOTE_ADDR
			$query="UPDATE tbl_security SET retryes='0' WHERE remote_addr='${remote_addr}'";
			db_query($query);
			// PONER ID_USUARIO Y RETRYES EN EL REGISTRO ACTUAL
			$query="UPDATE tbl_security SET id_usuario='${id_usuario}',retryes='0',logout='0' WHERE id='${id_security}'";
			db_query($query);
		}
	} elseif($action=="main") {
		// BUSCAR LOGOUT EN EL REGISTRO ACTUAL
		$query="SELECT logout FROM tbl_security WHERE id='${id_security}'";
		$logout=execute_query($query);
		if($logout) {
			$action="logout";
			setParam("action",$action);
			include("php/action/${action}.php");
		}
	} else {
		// RETORNAR SI SUM(RETRYES)<3 PARA TODOS LOS REMOTE_ADDR
		$query="SELECT SUM(retryes) suma FROM tbl_security WHERE remote_addr='${remote_addr}'";
		$retryes=execute_query($query);
		return $retryes<getDefault("security/maxretryes");
	}
}

function check_captcha($captcha="") {
	$valid=1;
	// CHECK VALUE
	$captcha1=$captcha;
	$captcha2=useSession("captcha_value");
	if($captcha!="") useSession("captcha_value","null");
	if(strlen($captcha1)==0) $valid=0;
	if(strlen($captcha2)==0) $valid=0;
	if($captcha1=="null") $valid=0;
	if($captcha2=="null") $valid=0;
	if($captcha1!=$captcha2) $valid=0;
	// CHECK IPADDR
	$captcha1=getServer("REMOTE_ADDR");
	$captcha2=useSession("captcha_ipaddr");
	if($captcha!="") useSession("captcha_ipaddr","null");
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
		$backtrace=$array["backtrace"];
		array_walk($backtrace,"__debug_backtrace_helper");
		$msg[]=array("Backtrace",implode($dict[$format][2],$backtrace));
	}
	array_walk($msg,"__do_message_error_helper",$dict[$format]);
	$msg=implode($msg);
	return $msg;
}

function __debug_backtrace_helper(&$item,$key) {
	$item="${key} => ".$item["function"].(isset($item["class"])?" (in class ".$item["class"].")":"").((isset($item["file"]) && isset($item["line"]))?" (in file ".$item["file"]." at line ".$item["line"].")":"");
}

function __do_message_error_helper(&$item,$key,$dict) {
	$item=$dict[0][0].$item[0].$dict[0][1].$dict[1][0].$item[1].$dict[1][1];
}

function show_php_error($array=null) {
	global $_ERROR_HANDLER;
	static $backup=null;
	if(is_null($array)) $array=$backup;
	if(is_null($array)) return;
	// REFUSE THE DEPRECATED WARNINGS
	if(isset($array["phperror"])) {
		$pos1=stripos($array["phperror"],"function");
		$pos2=stripos($array["phperror"],"deprecated");
		if($pos1!==false && $pos2!==false) return;
	}
	// CREATE THE MESSAGE ERROR USING HTML ENTITIES AND PLAIN TEXT
	$msg_html=do_message_error($array,"html");
	$msg_text=do_message_error($array,"text");
	$msg=getServer("SHELL")?$msg_text:$msg_html;
	// CHECK IF CAPTURE ERROR WAS ACTIVE
	if($_ERROR_HANDLER["level"]>0) {
		array_pop($_ERROR_HANDLER["msg"]);
		array_push($_ERROR_HANDLER["msg"],$msg);
		$backup=$array;
		return;
	}
	// ADD THE MSG_ALT TO THE ERROR LOG FILE
	addlog($msg_text,getDefault("debug/errorfile","error.log"));
	// PREPARE THE FINAL REPORT (ONLY IN NOT SHELL MODE)
	if(!getServer("SHELL")) {
		$msg=pretty_html_error($msg);
		if(!headers_sent()) {
			header_powered();
			header_expires(false);
			header("Content-Type: text/html");
		}
	}
	echo $msg;
	die();
}

function pretty_html_error($msg) {
	// ORIGINAL IDEA FROM plugins.jquery.com
	$html="<html>";
	$html.="<head>";
	$html.="<title>".get_name_version_revision()."</title>";
	$html.="<style>";
	$html.=".phperror { background:#444; color:#FFF; font-family:Helvetica,Arial,sans-serif; padding:20px 0; }";
	$html.=".phperror h3 { background:url(".getDefault("info/favicon","img/favicon").") top left no-repeat; padding-left: 50px; min-height:32px; font-size:1.5em; margin:0; }";
	$html.=".phperror a { color: cyan; }";
	$html.=".phperror pre { white-space:pre-wrap; }";
	$html.=".phperror p { margin:0; }";
	$html.=".phperror form { display:inline; }";
	$html.=".phperror input { background:#FFF; padding:5px; border-radius:5px; border:none; margin-right:5px; }";
	$html.=".phperror div { background:#000; padding:20px; border-radius:20px; box-shadow:0 0 20px #222; width:800px; margin:100px auto; }";
	$html.="</style>";
	$html.="</head>";
	$html.="<body class='phperror'>";
	$html.="<div>";
	$html.=$msg;
	$html.="<p>";
	$html.="<form action='xml.php' method='post' style='display:inline'>";
	$html.="<input type='hidden' name='page' value='home'/>";
	$html.="<input type='submit' value='".(LANG_LOADED()?LANG("gotohome"):"Go to home")."'/>";
	$html.="</form>";
	$html.="<form action='http://bugs.saltos.net/' method='post' style='display:inline'>";
	$html.="<input type='hidden' name='bug' value='".base64_encode(serialize(array("app"=>get_name_version_revision(),"msg"=>$msg)))."'/>";
	$html.="<input type='submit' value='".(LANG_LOADED()?LANG("notifybug"):"Notify bug")."'/>";
	$html.="</form>";
	$html.="</p>";
	$html.="</div>";
	$html.="</body>";
	return $html;
}

function __error_handler($type,$message,$file,$line) {
	$backtrace=debug_backtrace();
	array_shift($backtrace);
	show_php_error(array("phperror"=>"${message} (code ${type})","details"=>"Error on file '${file}' at line ${line}","backtrace"=>$backtrace));
}

function __exception_handler($e) {
	$backtrace=$e->getTrace();
	show_php_error(array("exception"=>$e->getMessage()." (code ".$e->getCode().")","details"=>"Error on file '".$e->getFile()."' at line ".$e->getLine(),"backtrace"=>$backtrace));
}

function __shutdown_handler() {
	$error=error_get_last();
	if(is_array($error) && $error["type"]==1) {
		while(ob_get_level()) ob_end_clean(); // TRICK TO CLEAR SCREEN
		show_php_error(array("phperror"=>"${error["message"]}","details"=>"Error on file '${error["file"]}' at line ${error["line"]}"));
	}
}

function program_error_handler() {
	global $_ERROR_HANDLER;
	$_ERROR_HANDLER=array("level"=>0,"msg"=>array());
	error_reporting(0);
	set_error_handler("__error_handler");
	set_exception_handler("__exception_handler");
	register_shutdown_function("__shutdown_handler");
}

function number_row_index($row) {
	$ret=array();
	foreach($row as $r) $ret[]=$r;
	return $ret;
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
	// GENERAL CHECKS
	if(!ini_get("date.timezone")) ini_set("date.timezone","Europe/Madrid");
	if(headers_sent()) show_php_error(array("phperror"=>"Has been Detected previous headers sended"));
	// CLASS CHECKS
	$array=array("DomDocument"=>"php-xml","XsltProcessor"=>"php-xml","DomElement"=>"php-xml");
	foreach($array as $key=>$val) if(!class_exists($key)) show_php_error(array("phperror"=>"Class $key not found","details"=>"Try to install $val package"));
	// FUNCTION CHECKS
	$array=array("imagecreatetruecolor"=>"php-gd","imagecreatefrompng"=>"php-gd",
		"mb_check_encoding"=>"php-mbstring","mb_convert_encoding"=>"php-mbstring","mb_strlen"=>"php-mbstring","mb_substr"=>"php-mbstring","mb_strpos"=>"php-mbstring");
	foreach($array as $key=>$val) if(!function_exists($key)) show_php_error(array("phperror"=>"Function $key not found","details"=>"Try to install $val package"));
	// INSTALL CHECK
	if(file_exists("install/install.php")) { include("install/install.php"); die(); }
}

function action_denied() {
	session_error(LANG("permdenied")." (".getParam("action").")");
	javascript_location_page("");
	die();
}

function dummy($param) {
	// NOTHING TO DO
}

function fix_input_vars() {
	$items=querystring2array(base64_decode(getParam("fix_input_vars")));
	if(isset($_GET["fix_input_vars"])) {
		unset($_GET["fix_input_vars"]);
		$_GET=array_merge($_GET,$items);
	}
	if(isset($_POST["fix_input_vars"])) {
		unset($_POST["fix_input_vars"]);
		$_POST=array_merge($_POST,$items);
	}
}
?>