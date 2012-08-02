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
if(!check_user()) action_denied();
if(getParam("action")=="getmail") {
	require_once("php/getmail.php");
	require_once("php/defines.php");
	// CHECK FOR SESSION REQUEST
	if(getParam("id")=="session" && getParam("cid")) {
		$cid=getParam("cid");
		sess_init();
		$session=$_SESSION["correo"];
		sess_close();
		if($cid=="files") {
			$buffer="";
			$first=1;
			if(!isset($session["files"])) $session["files"]=array();
			foreach($session["files"] as $key=>$file) {
				if(!$first) $buffer.=" | ";
				$buffer.="<a href='javascript:void(0)' onclick='download2(\"correo\",\"session\",\"${key}\")'><b>${file["name"]}</b></a> (${file["size"]})";
				$first=0;
			}
			output_buffer($buffer,"text/html");
		} else {
			if(!isset($session["files"][$cid])) die();
			$temp=$session["files"][$cid];
			$name=$temp["name"];
			$type=$temp["mime"];
			$file=$temp["file"];
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires(false);
			header("Content-Type: ${type}");
			header("Content-Disposition: attachment; filename=\"${name}\"");
			readfile($file);
			ob_end_flush();
			die();
		}
		die();
	}
	// CHECK FOR SOURCE REQUEST
	if(getParam("id") && getParam("cid")=="source") {
		$id=abs(intval(getParam("id")));
		if(!__getmail_checkperm($id)) action_denied();
		$source=__getmail_getsource($id,8192);
		$source=__getmail_getutf8($source);
		$source=htmlentities($source,ENT_COMPAT,"UTF-8");
		$buffer="";
		$buffer.=__PAGE_HTML_OPEN__;
		$buffer.=__TEXT_PLAIN_OPEN__;
		$buffer.=$source;
		$buffer.=__TEXT_PLAIN_CLOSE__;
		$buffer.=__PAGE_HTML_CLOSE__;
		$hash=md5($buffer);
		header_etag($hash);
		ob_start_protected(getDefault("obhandler"));
		header_powered();
		header_expires();
		header("Content-Type: text/html");
		header("x-frame-options: SAMEORIGIN");
		echo $buffer;
		ob_end_flush();
		die();
	}
	// CHECK FOR CID REQUEST
	if(getParam("id") && getParam("cid")) {
		$id=abs(intval(getParam("id")));
		if(!__getmail_checkperm($id)) action_denied();
		$decoded=__getmail_getmime($id);
		if(!$decoded) {
			session_error(LANG("msgopenerrorpop3email","correo"));
			javascript_history(-1);
			die();
		}
		$cid=getParam("cid");
		if($cid=="body") {
			$result=__getmail_getfullbody(__getmail_getnode("0",$decoded));
			$buffer="";
			$buffer.=__PAGE_HTML_OPEN__;
			$first=1;
			$useimginline=eval_bool(getDefault("cache/useimginline"));
			foreach($result as $index=>$node) {
				$disp=$node["disp"];
				$type=$node["type"];
				if(__getmail_processplainhtml($disp,$type)) {
					$temp=$node["body"];
					if($type=="html") {
						$temp=__getmail_removescripts($temp);
						$temp=__getmail_make_clickable($temp);
						$temp=__getmail_href_replace($temp);
					}
					foreach($result as $index2=>$node2) {
						$disp2=$node2["disp"];
						$type2=$node2["type"];
						if(!__getmail_processplainhtml($disp2,$type2) && !__getmail_processmessage($disp2,$type2)) {
							$cid2=$node2["cid"];
							if($cid2!="") {
								$chash2=$node2["chash"];
								if($useimginline) {
									$data=base64_encode($node2["body"]);
									$data="data:image/png;base64,${data}";
									$temp=str_replace("cid:${cid2}",$data,$temp);
								} else {
									$url="xml.php?action=getmail&id=${id}&cid=${chash2}";
									$temp=str_replace("cid:${cid2}",$url,$temp);
								}
							}
						}
					}
					if(!$first) $buffer.=__TEXT_SEPARATOR__;
					$buffer.=($type=="plain")?__TEXT_PLAIN_OPEN__:__TEXT_HTML_OPEN__;
					$buffer.=($type=="plain")?htmlentities($temp,ENT_COMPAT,"UTF-8"):$temp;
					$buffer.=($type=="plain")?__TEXT_PLAIN_CLOSE__:__TEXT_HTML_CLOSE__;
					$first=0;
				}
			}
			$buffer.=__PAGE_HTML_CLOSE__;
			$hash=md5($buffer);
			header_etag($hash);
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires();
			header("Content-Type: text/html");
			header("x-frame-options: SAMEORIGIN");
			echo $buffer;
			ob_end_flush();
			die();
		} elseif($cid=="files") {
			$result=__getmail_getfiles(__getmail_getnode("0",$decoded));
			$buffer="";
			$first=1;
			foreach($result as $file) {
				$cname=$file["cname"];
				$chash=$file["chash"];
				$hsize=$file["hsize"];
				if(!$first) $buffer.=" | ";
				$buffer.="<a href='javascript:void(0)' onclick='download2(\"correo\",\"${id}\",\"${chash}\")'><b>${cname}</b></a> (${hsize})";
				$first=0;
			}
			$hash=md5($buffer);
			header_etag($hash);
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires();
			header("Content-Type: text/html");
			echo $buffer;
			ob_end_flush();
			die();
		} elseif($cid=="full") {
			$result=__getmail_getinfo(__getmail_getnode("0",$decoded));
			$lista=array("from","to","cc","bcc");
			foreach($lista as $temp) unset($result[$temp]);
			$buffer="";
			foreach($result["emails"] as $email) {
				if($email["nombre"]!="") $email["valor"]="${email["nombre"]} <${email["valor"]}>";
				if(!isset($result[$email["tipo"]])) $result[$email["tipo"]]=array();
				$result[$email["tipo"]][]=$email["valor"];
			}
			if(isset($result["from"])) $result["from"]=implode("; ",$result["from"]);
			if(isset($result["to"])) {
				$result["to"]=implode("; ",$result["to"]);
				$query="SELECT email_from FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}')";
				$result["to"]=str_replace("<>","<".execute_query($query).">",$result["to"]);
			}
			if(!isset($result["to"])) {
				$query="SELECT CASE WHEN (SELECT email_name FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}'))='' THEN (SELECT email_from FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}')) ELSE (SELECT /*SQLITE email_name || ' <' || email_from || '>' *//*MYSQL CONCAT(email_name,' <',email_from,'>') */ FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}')) END";
				$result["to"]=execute_query($query);
			}
			if(isset($result["cc"])) $result["cc"]=implode("; ",$result["cc"]);
			if(isset($result["bcc"])) $result["bcc"]=implode("; ",$result["bcc"]);
			$lista=array(
				"from"=>LANG("from","correo"),
				"to"=>LANG("to","correo"),
				"cc"=>LANG("cc","correo"),
				"bcc"=>LANG("bcc","correo"),
				"datetime"=>LANG("datetime","correo"),
				"subject"=>LANG("subject","correo")
			);
			if(!isset($result["from"])) unset($lista["from"]);
			if(!isset($result["to"])) unset($lista["to"]);
			if(!isset($result["cc"])) unset($lista["cc"]);
			if(!isset($result["bcc"])) unset($lista["bcc"]);
			if(!$result["subject"]) $result["subject"]=LANG("sinsubject","correo");
			$buffer.=__PAGE_HTML_OPEN__;
			$buffer.="<div style='background:#ffffff'>";
			$buffer.="<table>";
			foreach($lista as $key=>$val) {
				$result[$key]=str_replace(array("<",">"),array("&lt;","&gt;"),$result[$key]);
				$buffer.="<tr>";
				$buffer.="<td align='right' nowrap='nowrap'>";
				$buffer.=__TEXT_HTML_OPEN__;
				$buffer.=$lista[$key].":";
				$buffer.=__TEXT_HTML_CLOSE__;
				$buffer.="</td>";
				$buffer.="<td>";
				$buffer.="<b>";
				$buffer.=__TEXT_HTML_OPEN__;
				$buffer.=$result[$key];
				$buffer.=__TEXT_HTML_CLOSE__;
				$buffer.="</b>";
				$buffer.="</td>";
				$buffer.="</tr>";
			}
			$first=1;
			foreach($result["files"] as $file) {
				$cname=$file["cname"];
				$chash=$file["chash"];
				$hsize=$file["hsize"];
				if($first) {
					$buffer.="<tr>";
					$buffer.="<td align='right' nowrap='nowrap'>";
					$buffer.=__TEXT_HTML_OPEN__;
					$buffer.=LANG("attachments","correo").":";
					$buffer.=__TEXT_HTML_CLOSE__;
					$buffer.="</td>";
					$buffer.="<td>";
					$buffer.=__TEXT_HTML_OPEN__;
				} else {
					$buffer.=" | ";
				}
				$buffer.="<a href='xml.php?action=download&page=correo&id=${id}&cid=${chash}'><b>${cname}</b></a> (${hsize})";
				$first=0;
			}
			if(!$first) {
				$buffer.=__TEXT_HTML_CLOSE__;
				$buffer.="</td>";
				$buffer.="</tr>";
			}
			$buffer.="</table>";
			$buffer.="</div>";
			$buffer.=__TEXT_SEPARATOR__;
			$result=__getmail_getfullbody(__getmail_getnode("0",$decoded));
			$first=1;
			$useimginline=eval_bool(getDefault("cache/useimginline"));
			foreach($result as $index=>$node) {
				$disp=$node["disp"];
				$type=$node["type"];
				if(__getmail_processplainhtml($disp,$type)) {
					$temp=$node["body"];
					if($type=="html") {
						$temp=__getmail_removescripts($temp);
						$temp=__getmail_make_clickable($temp);
						$temp=__getmail_href_replace($temp);
					}
					foreach($result as $index2=>$node2) {
						$disp2=$node2["disp"];
						$type2=$node2["type"];
						if(!__getmail_processplainhtml($disp2,$type2) && !__getmail_processmessage($disp2,$type2)) {
							$cid2=$node2["cid"];
							if($cid2!="") {
								$chash2=$node2["chash"];
								if($useimginline) {
									$data=base64_encode($node2["body"]);
									$data="data:image/png;base64,${data}";
									$temp=str_replace("cid:${cid2}",$data,$temp);
								} else {
									$url="xml.php?action=getmail&id=${id}&cid=${chash2}";
									$temp=str_replace("cid:${cid2}",$url,$temp);
								}
							}
						}
					}
					if(!$first) $buffer.=__TEXT_SEPARATOR__;
					$buffer.=($type=="plain")?__TEXT_PLAIN_OPEN__:__TEXT_HTML_OPEN__;
					$buffer.=($type=="plain")?htmlentities($temp,ENT_COMPAT,"UTF-8"):$temp;
					$buffer.=($type=="plain")?__TEXT_PLAIN_CLOSE__:__TEXT_HTML_CLOSE__;
					$first=0;
				}
			}
			$buffer.=__PAGE_HTML_CLOSE__;
			$hash=md5($buffer);
			header_etag($hash);
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires();
			header("Content-Type: text/html");
			header("x-frame-options: SAMEORIGIN");
			echo $buffer;
			ob_end_flush();
			die();
		} else {
			$result=__getmail_getcid(__getmail_getnode("0",$decoded),$cid);
			if(!$result) die();
			$name=$result["cname"]?$result["cname"]:$result["cid"];
			$hash=md5($result["body"]);
			header_etag($hash);
			ob_start_protected(getDefault("obhandler"));
			header_powered();
			header_expires();
			header("Content-Type: ${result["type"]}");
			header("Content-Disposition: attachment; filename=\"${name}\"");
			echo $result["body"];
			ob_end_flush();
			die();
		}
		die();
	}
	if(eval_bool(getDefault("debug/cancelgetmail"))) die();
	// CHECK THE SEMAPHORE
	$semaphore=get_cache_file(array(getParam("action"),current_user()),getDefault("exts/semext",".sem"));
	if(!semaphore_acquire($semaphore,getDefault("semaphoretimeout",100000))) {
		if(!getParam("ajax")) {
			session_error(LANG("msgerrorsemaphore").getParam("action"));
			javascript_history(-1);
		} else {
			javascript_error(LANG("msgerrorsemaphore").getParam("action"));
		}
		die();
	}
	// FOR DEBUG PURPOSES
	if(getDefault("debug/getmailmsgid")) {
		$fext=getDefault("exts/emailext",".eml").getDefault("exts/gzipext",".gz");
		$file=get_directory("dirs/inboxdir").getDefault("debug/getmailmsgid").$fext;
		if(!file_exists($file)) $file=get_directory("dirs/outboxdir").getDefault("debug/getmailmsgid").$fext;
		$fp=gzopen($file,"r");
		$message="";
		while(!feof($fp)) $message.=gzread($fp,8192);
		gzclose($fp);
		__getmail_insert($message,getDefault("debug/getmailmsgid"),1,0,0,0,0,0,0,"");
		die();
	}
	// DATOS POP3
	$query="SELECT * FROM tbl_usuarios_c WHERE id_usuario='".current_user()."' AND email_disabled='0'";
	$result=execute_query($query);
	if(!$result) {
		if(!getParam("ajax")) {
			session_error(LANG("msgnotpop3email","correo"));
			javascript_history(-1);
		}
		semaphore_release($semaphore);
		die();
	}
	if(isset($result["id"])) $result=array($result);
	// BEGIN THE LOOP
	$newemail=0;
	$haserror=0;
	$voice_ids=array();
	foreach($result as $row) {
		$error="";
		if($row["pop3_host"]=="") {
			$temp=$row["email_from"];
			if(!$temp) $temp=$row["email_name"];
			if($temp) $temp=" ($temp)";
			$error=LANG("msgnotpop3host","correo").$temp;
		}
		if($error=="") {
			$id_cuenta=$row["id"];
			$prefix=get_directory("dirs/inboxdir").$id_cuenta;
			if(!file_exists($prefix)) {
				mkdir($prefix);
				chmod_protected($prefix,0777);
			}
			// DB code
			$query="SELECT uidl FROM tbl_correo WHERE id_cuenta='${id_cuenta}'";
			$olduidls=execute_query($query);
			if(!$olduidls) $olduidls=array();
			if(!is_array($olduidls)) $olduidls=array($olduidls);
			$query="SELECT uidl FROM tbl_correo_d WHERE id_cuenta='${id_cuenta}'";
			$olduidls_d=execute_query($query);
			if(!$olduidls_d) $olduidls_d=array();
			if(!is_array($olduidls_d)) $olduidls_d=array($olduidls_d);
			$olduidls=array_merge($olduidls,$olduidls_d);
			// POP3 code
			$pop3=new pop3_class;
			$pop3->hostname=$row["pop3_host"];
			if($row["pop3_port"]) $pop3->port=$row["pop3_port"];
			$pop3->tls=($row["pop3_extra"]=="tls")?1:0;
			capture_next_error();
			$error=$pop3->Open();
			$error2=get_clear_error();
			if($error2!="") {
				if(stripos($error2,"connection refused")!==false) {
					$error=LANG("msgconnrefusedpop3email","correo");
				} elseif(stripos($error2,"unable to connect to")!==false) {
					$error=LANG("msgconnerrorpop3email","correo");
				} else {
					$error=$error2;
				}
			}
		}
		if($error=="") {
			capture_next_error();
			$error=$pop3->Login($row["pop3_user"],$row["pop3_pass"]);
			$error2=get_clear_error();
			if($error2!="") {
				if(stripos($error2,"connection reset by peer")!==false) {
					$error=LANG("msgconnerrorpop3email","correo");
				} else {
					$error=$error2;
				}
			}
		}
		if($error=="") {
			$sizes=$pop3->ListMessages("",0);
			if(!is_array($sizes)) $error=$sizes;
		}
		if($error=="") {
			$uidls=$pop3->ListMessages("",1);
			if(!is_array($uidls)) $error=$uidls;
		}
		if($error=="") {
			// RETRIEVE ALL NEW MESSAGES
			$retrieve=array_diff($uidls,$olduidls);
			foreach($retrieve as $index=>$uidl) {
				if($error=="") {
					$fext=getDefault("exts/emailext",".eml").getDefault("exts/gzipext",".gz");
					$file=$prefix."/".$uidls[$index].$fext;
					if(!file_exists($file)) {
						// RETRIEVE THE ENTIRE MESSAGE
						$error=$pop3->OpenMessage($index,-1);
						if($error=="") {
							$message="";
							$eof=0;
							while(!$eof && $error=="") {
								$temp="";
								$error=$pop3->GetMessage($sizes[$index]+1,$temp,$eof);
								$message.=$temp;
							}
						}
						if($error=="") {
							// STORE THE MESSAGE INTO SINGLE FILE
							$fp=gzopen($file,"w");
							gzwrite($fp,$message);
							gzclose($fp);
							chmod_protected($file,0666);
						}
					} else {
						// EXIST IN OUR FILESYSTEM
						$fp=gzopen($file,"r");
						$message="";
						while(!feof($fp)) $message.=gzread($fp,8192);
						gzclose($fp);
					}
					if($error=="") {
						$messageid=$id_cuenta."/".$uidls[$index];
						$next_id=__getmail_insert($message,$messageid,1,0,0,0,0,0,0,"");
						$newemail++;
						$voice_ids[]=$next_id;
					}
				}
			}
		}
		if($error=="" && $row["pop3_delete"]) {
			// REMOVE ALL EXPIRED MESSAGES (IF CHECKED THE DELETE OPTION)
			$delete="'".implode("','",$uidls)."'";
			$query="SELECT uidl,`datetime` FROM (SELECT uidl,`datetime` FROM tbl_correo WHERE uidl IN ($delete) UNION SELECT uidl,`datetime` FROM tbl_correo_d WHERE uidl IN ($delete)) a";
			$result2=execute_query($query);
			if(!$result2) $result2=array();
			if(isset($result2["uidl"])) $result2=array($result2);
			$time1=strtotime(current_datetime());
			foreach($result2 as $row2) {
				$time2=strtotime($row2["datetime"]);
				if($time1-$time2>=$row["pop3_days"]*86400) {
					$index2=array_search($row2["uidl"],$uidls);
					$error=$pop3->DeleteMessage($index2);
					unset($uidls[$index2]);
				}
				if($error!="") break;
			}
		}
		if($error=="") {
			$error=$pop3->Close();
		}
		if($error=="") {
			// REMOVE ALL UNUSED UIDLS
			$delete=array_diff($olduidls_d,$uidls);
			$delete="'".implode("','",$delete)."'";
			$query="DELETE FROM tbl_correo_d WHERE uidl IN (${delete})";
			db_query($query);
		}
		if($error!="") {
			if(!getParam("ajax")) {
				session_error(LANG("msgerrorpop3email","correo").$error." (".$row["pop3_host"].")");
			} else {
				javascript_error(LANG("msgerrorpop3email","correo").$error." (".$row["pop3_host"].")");
			}
			$haserror=1;
		}
	}
	// GO BACK
	if(!getParam("ajax")) {
		if($newemail>0) {
			session_alert($newemail.LANG("msgnewokpop3email".min($newemail,2),"correo"));
		} elseif(!$haserror) {
			session_alert(LANG("msgnewkopop3email","correo"));
		}
		javascript_history(-1);
	} else {
		if($newemail>0) {
			$gotoemail=" [<a href='javascript:void(0)' onclick='gotoemail()'>".LANG("msggotoemail","correo")."</a>]";
			$condition="update_correo_list()";
			javascript_alert($newemail.LANG("msgnewokpop3email".min($newemail,2),"correo"),$condition);
			javascript_alert($newemail.LANG("msgnewokpop3email".min($newemail,2),"correo").$gotoemail,"!($condition)");
			$query="SELECT COUNT(*) count FROM tbl_correo WHERE state_new='1' AND id_cuenta IN (SELECT id FROM tbl_usuarios_c WHERE id_usuario='".current_user()."')";
			$count=execute_query($query);
			if($count) javascript_template("menu_correo($count);");
			if($count) javascript_template("favicon_animate($count);");
			javascript_history(0,$condition);
		}
	}
	// VOICE FEATURES
	if($newemail>0) {
		javascript_template("notify_voice('".$newemail.LANG("msgnewokpop3email".min($newemail,2),"correo")."')","saltos_voice()");
	}
	if(count($voice_ids)) {
		$query="SELECT /*MYSQL CONCAT(`from`,'. ',(CASE WHEN subject='' THEN '".LANG("sinsubject","correo")."' ELSE subject END)) *//*SQLITE `from` || '. ' || (CASE WHEN subject='' THEN '".LANG("sinsubject","correo")."' ELSE subject END) */ reader FROM tbl_correo WHERE state_spam='0' AND id IN (".implode(",",$voice_ids).") ORDER BY id DESC";
		$result=execute_query($query);
		if(!$result) $result=array();
		if(!is_array($result)) $result=array($result);
		foreach($result as $reader) javascript_template("notify_voice('".str_replace(array("'","\n","\r")," ",$reader)."')","saltos_voice()");
	}
	// RELEASE SEMAPHORE
	semaphore_release($semaphore);
	die();
}
if(getParam("page")=="correo") {
	$id_correo=abs(getParam("id"));
	$id_extra=explode("_",getParam("id"),3);
	if(isset($id_extra[1]) && isset($id_extra[2]) && in_array($id_extra[1],array("reply","replyall","forward"))) $id_correo=$id_extra[2];
	$id_correo=abs(getParam("id"));
	$id_extra=explode("_",getParam("id"),3);
	if(isset($id_extra[1]) && isset($id_extra[2]) && $id_extra[1]=="forward") $id_correo=$id_extra[2];
	if($id_correo) {
		// BUSCAR USUARIO DEL CORREO
		$query="SELECT ".make_extra_query_with_login()." FROM tbl_usuarios WHERE id=(SELECT id_usuario FROM tbl_registros WHERE id_registro='${id_correo}' AND id_aplicacion='".page2id("correo")."' AND primero='1')";
		$usuario=execute_query($query);
		// BUSCAR GRUPO DEL CORREO
		$query="SELECT nombre FROM tbl_grupos WHERE id=(SELECT id_grupo FROM tbl_usuarios WHERE id=(SELECT id_usuario FROM tbl_registros WHERE id_registro='${id_correo}' AND id_aplicacion='".page2id("correo")."' AND primero='1'))";
		$grupo=execute_query($query);
		// BUSCAR DATETIME DEL CORREO
		$query="SELECT `datetime` FROM tbl_correo WHERE id='${id_correo}'";
		$datetime=execute_query($query);
		// PROCESAR CORREO
		require_once("php/getmail.php");
		if(!__getmail_checkperm($id_correo)) action_denied();
		$decoded=__getmail_getmime($id_correo);
		if(!$decoded) {
			session_error(LANG("msgopenerrorpop3email","correo"));
			javascript_history(-1);
			die();
		}
		$result2=__getmail_getfiles(__getmail_getnode("0",$decoded));
		$rows2=array();
		foreach($result2 as $file) {
			$fichero=$file["cname"];
			$size=$file["hsize"];
			$chash=$file["chash"];
			$download="download2('correo','${id_correo}','${chash}')";
			$viewpdf="viewpdf2('correo','${id_correo}','${chash}')";
			$rows2[]=array("id"=>$chash,"usuario"=>$usuario,"grupo"=>$grupo,"datetime"=>$datetime,"fichero"=>$fichero,"fichero_size"=>$size,"download"=>$download,"viewpdf"=>$viewpdf);
		}
		// DEJAR ROWS EN LA ESTRUCTURA DE DATOS DEL XML
		foreach($rows2 as $row2) set_array($rows[$key],"row",$row2);
	}
	if(isset($id_extra[1]) && $id_extra[1]=="session") {
		sess_init();
		$session=$_SESSION["correo"];
		sess_close();
		$query="SELECT ".make_extra_query_with_login()." FROM tbl_usuarios WHERE id='".current_user()."'";
		$usuario=execute_query($query);
		$query="SELECT nombre FROM tbl_grupos WHERE id=(SELECT id_grupo FROM tbl_usuarios WHERE id='".current_user()."')";
		$grupo=execute_query($query);
		$datetime=current_datetime();
		if(!isset($session["files"])) $session["files"]=array();
		foreach($session["files"] as $key2=>$file) {
			$fichero=$file["name"];
			$size=$file["size"];
			$download="download2('correo','session','${key2}')";
			$viewpdf="viewpdf2('correo','session','${key2}')";
			set_array($rows[$key],"row",array("id"=>$key2,"usuario"=>$usuario,"grupo"=>$grupo,"datetime"=>$datetime,"fichero"=>$fichero,"fichero_size"=>$size,"download"=>$download,"viewpdf"=>$viewpdf));
		}
	}
	if(isset($id_extra[1]) && isset($id_extra[2]) && $id_extra[1]=="feed") {
		// MARCAR FEED COMO LEIDO SI ES EL PROPIETARIO
		$query="UPDATE tbl_feeds SET state_new='0' WHERE id=(SELECT id_registro FROM tbl_registros WHERE id_aplicacion='".page2id("feeds")."' AND id_registro='${id_extra[2]}' AND primero='1' AND id_usuario='".current_user()."')";
		db_query($query);
	}
}
?>