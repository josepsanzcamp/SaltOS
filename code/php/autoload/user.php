<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2021 by Josep Sanz Campderrós
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
	$user=getSession("user");
	$pass=getSession("pass");
	if($user!="" && $pass!="") {
		reset_datauser();
		$query="SELECT * FROM tbl_usuarios WHERE ".make_where_query(array(
			"activo"=>1,
			"login"=>$user,
			"password"=>$pass
		));
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
	if(isset($_USER["id"])) {
		$query="SELECT * FROM tbl_usuarios WHERE id='".$_USER["id"]."'";
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
			session_error(LANG("nocheckuser"));
		}
		db_free($result);
	}
}

function check_time() {
	global $_USER;
	if(!isset($_USER["id"])) return false;
	if(!$_USER["id"]) return false;
	$hora=current_time();
	$dia=(date("w",time())+6)%7;
	$check_hora1=($_USER["hora_ini"]<=$_USER["hora_fin"] && ($hora<$_USER["hora_ini"] || $hora>$_USER["hora_fin"]));
	$check_hora2=($_USER["hora_ini"]>$_USER["hora_fin"] && $hora<$_USER["hora_ini"] && $hora>$_USER["hora_fin"]);
	$check_dia=($_USER["dias_sem"][$dia]=="0");
	if($check_hora1 || $check_hora2) {
		session_error(LANG("nochecktime"));
		reset_datauser();
	} elseif($check_dia) {
		session_error(LANG("nocheckday"));
		reset_datauser();
	}
}

function check_user($aplicacion="",$permiso="") {
	global $_USER;
	if(!isset($_USER["id"])) return 0;
	if(!$_USER["id"]) return 0;
	if($aplicacion=="") return 1;
	if(!isset($_USER[$aplicacion])) return 0;
	if(!isset($_USER[$aplicacion][$permiso])) return 0;
	return $_USER[$aplicacion][$permiso];
}

function check_sql($aplicacion,$permiso,$id_usuario="id_usuario",$id_grupo="id_grupo") {
	global $_USER;
	// CHECK FOR USER/GROUP/ALL PERMISSIONS
	$sql=array();
	$sql["all"]="1=1";
	$sql["group"]="${id_grupo} IN (".check_ids($_USER["id_grupo"],execute_query_array("SELECT id_grupo FROM tbl_usuarios_g WHERE id_usuario='".$_USER["id"]."'")).")";
	$sql["user"]="${id_usuario}='".$_USER["id"]."'";
	foreach($sql as $key=>$val) if(check_user($aplicacion,"${key}_${permiso}")) return $val;
	return "1=0";
}

function check_sql2($permiso,$id_aplicacion="id_aplicacion",$id_usuario="id_usuario",$id_grupo="id_grupo") {
	$query="SELECT * FROM tbl_aplicaciones WHERE tabla!=''";
	$result=db_query($query);
	$cases=array();
	while($row=db_fetch_row($result)) {
		$cases[]="(${id_aplicacion}='${row["id"]}' AND (".check_sql($row["codigo"],$permiso,$id_usuario,$id_grupo)."))";
	}
	db_free($result);
	$cases="(".implode(" OR ",$cases).")";
	return $cases;
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

function __usuarios($tipo,$dato) {
	static $diccionario=array();
	if(!count($diccionario)) {
		$query="SELECT id,login FROM tbl_usuarios";
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

function check_remember() {
	if(!eval_bool(getDefault("security/allowremember"))) return;
	if(getSession("user")) return;
	if(getSession("pass")) return;
	if(!getCookie2("remember")) return;
	if(!getCookie2("user")) return;
	if(!getCookie2("pass")) return;
	if(!check_security("retries") && !check_security("logouts")) {
		sess_close();
		setParam("action","logout");
		include("php/action/logout.php");
	}
	setSession("user",getCookie2("user"));
	setSession("pass",getCookie2("pass"));
	pre_datauser();
	check_security("login");
	return;
}

function remake_password($user,$pass) {
	$query="SELECT * FROM tbl_usuarios WHERE ".make_where_query(array(
		"activo"=>1,
		"login"=>$user,
	));
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
	if(getSession("user")) return;
	if(getSession("pass")) return;
	if(!getServer("PHP_AUTH_USER")) return;
	if(!getServer("PHP_AUTH_PW")) return;
	if(!check_security("retries") && !check_security("logouts")) {
		sess_close();
		setParam("action","logout");
		include("php/action/logout.php");
	}
	setSession("user",getServer("PHP_AUTH_USER"));
	setSession("pass",remake_password(getServer("PHP_AUTH_USER"),getServer("PHP_AUTH_PW")));
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
	$id_security=execute_query($query);
	if(is_array($id_security)) {
		$query="DELETE FROM tbl_security WHERE id_session='${id_session}'";
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
		$query="SELECT id FROM tbl_security WHERE id_session='${id_session}'";
		$id_security=execute_query($query);
	}
	// BUSCAR ID_SECURITY_IP
	$query="SELECT id FROM tbl_security_ip WHERE id_session='${id_session}' AND remote_addr='${remote_addr}'";
	$id_security_ip=execute_query($query);
	if(is_array($id_security_ip)) {
		$query="DELETE FROM tbl_security_ip WHERE id_session='${id_session}' AND remote_addr='${remote_addr}'";
		db_query($query);
		$id_security_ip=0;
	}
	if(!$id_security_ip) {
		$query=make_insert_query("tbl_security_ip",array(
			"id_session"=>$id_session,
			"remote_addr"=>$remote_addr
		));
		db_query($query);
		$query="SELECT id FROM tbl_security_ip WHERE id_session='${id_session}' AND remote_addr='${remote_addr}'";
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
			$query=make_update_query("tbl_security_ip",array(
				"retries"=>execute_query("SELECT retries+1 FROM tbl_security_ip WHERE id='${id_security_ip}'"),
			),"id='${id_security_ip}'");
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
	$captcha2=getSession($id);
	if($captcha!="") setSession($id,"");
	if(strlen($captcha1)==0) $valid=0;
	if(strlen($captcha2)==0) $valid=0;
	if($captcha1=="null") $valid=0;
	if($captcha2=="null") $valid=0;
	if($captcha1!=$captcha2) $valid=0;
	// CONTINUE
	return $valid;
}

function action_denied() {
	// TODO FIXED IN THE FUTURE
	if(getDefault("engine")!="default") die();
	session_error(LANG("permdenied")." (".getParam("action").")");
	javascript_location_page("");
	die();
}

?>