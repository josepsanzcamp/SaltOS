<?php
/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2016 by Josep Sanz Campderrós
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
if(!check_user()) action_denied();
if(getParam("action")=="getmail") {
	require_once("php/getmail.php");
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
				$name=$file["name"];
				$size=__getmail_gethumansize($file["size"]);
				$buffer.="<a href='javascript:void(0)' onclick='download2(\"correo\",\"session\",\"${key}\")'><b>${name}</b></a> (${size})";
				$first=0;
			}
			output_handler(array(
				"data"=>$buffer,
				"type"=>"text/html",
				"cache"=>false
			));
		} else {
			if(!isset($session["files"][$cid])) die();
			$temp=$session["files"][$cid];
			$name=$temp["name"];
			$type=$temp["mime"];
			$file=$temp["file"];
			$size=$temp["size"];
			output_handler(array(
				"file"=>$file,
				"cache"=>false,
				"name"=>$name
			));
		}
		die();
	}
	// CHECK FOR SOURCE REQUEST
	if(getParam("id") && getParam("cid")=="source") {
		$id=abs(intval(getParam("id")));
		if(!__getmail_checkperm($id)) action_denied();
		$source=__getmail_getsource($id,8192);
		$source=getutf8($source);
		$source=wordwrap($source,120);
		$source=htmlentities($source,ENT_COMPAT,"UTF-8");
		$source=str_replace(array(" ","\t","\n"),array("&nbsp;",str_repeat("&nbsp;",8),"<br/>"),$source);
		$buffer="";
		$buffer.=__HTML_PAGE_OPEN__;
		$buffer.=__PLAIN_TEXT_OPEN__;
		$buffer.=$source;
		$buffer.=__PLAIN_TEXT_CLOSE__;
		$buffer.=__HTML_PAGE_CLOSE__;
		output_handler(array(
			"data"=>$buffer,
			"type"=>"text/html",
			"cache"=>false,
			"extra"=>array("x-frame-options: SAMEORIGIN")
		));
	}
	// CHECK FOR CID REQUEST
	if(getParam("id") && getParam("cid")) {
		$id=abs(getParam("id"));
		if(!__getmail_checkperm($id)) action_denied();
		$decoded=__getmail_getmime($id);
		if(!$decoded) {
			session_error(LANG("msgopenerrorpop3email","correo"));
			javascript_history(-1);
			die();
		}
		$cid=getParam("cid");
		if($cid=="body") {
			// MARCAR CORREO COMO LEIDO SI ES EL PROPIETARIO
			$query=make_update_query("tbl_correo",array(
				"state_new"=>0
			),"id=(SELECT id_registro FROM tbl_registros_i WHERE id_aplicacion='".page2id("correo")."' AND id_registro='${id}' AND id_usuario='".current_user()."')");
			db_query($query);
			// CONTINUE
			$result=__getmail_getfullbody(__getmail_getnode("0",$decoded));
			$buffer="";
			$buffer.=__HTML_PAGE_OPEN__;
			$first=1;
			$useimginline=eval_bool(getDefault("cache/useimginline"));
			foreach($result as $index=>$node) {
				$disp=$node["disp"];
				$type=$node["type"];
				if(__getmail_processplainhtml($disp,$type)) {
					$temp=$node["body"];
					if($type=="plain") {
						$temp=wordwrap($temp,120);
						$temp=htmlentities($temp,ENT_COMPAT,"UTF-8");
						$temp=str_replace(array(" ","\t","\n"),array("&nbsp;",str_repeat("&nbsp;",8),"<br/>\n"),$temp);
						$temp=saltos_make_clickable($temp);
						$temp=href_replace($temp);
					}
					if($type=="html") {
						$temp=remove_script_tag($temp);
						$temp=remove_style_tag($temp);
						$temp=saltos_make_clickable($temp);
						$temp=href_replace($temp);
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
									$url="?action=getmail&id=${id}&cid=${chash2}";
									$temp=str_replace("cid:${cid2}",$url,$temp);
								}
							}
						}
					}
					if(!$first) $buffer.=__HTML_SEPARATOR__;
					if($type=="plain") $buffer.=__PLAIN_TEXT_OPEN__.$temp.__PLAIN_TEXT_CLOSE__;
					if($type=="html") $buffer.=__HTML_TEXT_OPEN__.$temp.__HTML_TEXT_CLOSE__;
					$first=0;
				}
			}
			$buffer.=__HTML_PAGE_CLOSE__;
			output_handler(array(
				"data"=>$buffer,
				"type"=>"text/html",
				"cache"=>false,
				"extra"=>array("x-frame-options: SAMEORIGIN")
			));
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
			output_handler(array(
				"data"=>$buffer,
				"type"=>"text/html",
				"cache"=>false,
				"extra"=>array("x-frame-options: SAMEORIGIN")
			));
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
				$query="SELECT CASE WHEN (SELECT email_name FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}'))='' THEN (SELECT email_from FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}')) ELSE (SELECT SUBSTR(CONCAT(email_name,' <',email_from,'>'),1,255) FROM tbl_usuarios_c WHERE id=(SELECT id_cuenta FROM tbl_correo WHERE id='${id}')) END";
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
			$buffer.=__HTML_PAGE_OPEN__;
			$buffer.=__HTML_BOX_OPEN__;
			$buffer.=__HTML_TABLE_OPEN__;
			foreach($lista as $key=>$val) {
				$result[$key]=str_replace(array("<",">"),array("&lt;","&gt;"),$result[$key]);
				$buffer.=__HTML_ROW_OPEN__;
				$buffer.=__HTML_RCELL_OPEN__;
				$buffer.=__HTML_TEXT_OPEN__;
				$buffer.=$lista[$key].":";
				$buffer.=__HTML_TEXT_CLOSE__;
				$buffer.=__HTML_CELL_CLOSE__;
				$buffer.=__HTML_CELL_OPEN__;
				$buffer.=__HTML_TEXT_OPEN__;
				$buffer.="<b>".$result[$key]."</b>";
				$buffer.=__HTML_TEXT_CLOSE__;
				$buffer.=__HTML_CELL_CLOSE__;
				$buffer.=__HTML_ROW_CLOSE__;
			}
			$first=1;
			foreach($result["files"] as $file) {
				$cname=$file["cname"];
				$chash=$file["chash"];
				$hsize=$file["hsize"];
				if($first) {
					$buffer.=__HTML_ROW_OPEN__;
					$buffer.=__HTML_RCELL_OPEN__;
					$buffer.=__HTML_TEXT_OPEN__;
					$buffer.=LANG("attachments","correo").":";
					$buffer.=__HTML_TEXT_CLOSE__;
					$buffer.=__HTML_CELL_CLOSE__;
					$buffer.=__HTML_CELL_OPEN__;
					$buffer.=__HTML_TEXT_OPEN__;
				} else {
					$buffer.=" | ";
				}
				$buffer.="<a href='?action=download&page=correo&id=${id}&cid=${chash}'><b>${cname}</b></a> (${hsize})";
				$first=0;
			}
			if(!$first) {
				$buffer.=__HTML_TEXT_CLOSE__;
				$buffer.=__HTML_CELL_CLOSE__;
				$buffer.=__HTML_ROW_CLOSE__;
			}
			$buffer.=__HTML_TABLE_CLOSE__;
			$buffer.=__HTML_BOX_CLOSE__;
			$buffer.=__HTML_SEPARATOR__;
			$result=__getmail_getfullbody(__getmail_getnode("0",$decoded));
			$first=1;
			$useimginline=eval_bool(getDefault("cache/useimginline"));
			foreach($result as $index=>$node) {
				$disp=$node["disp"];
				$type=$node["type"];
				if(__getmail_processplainhtml($disp,$type)) {
					$temp=$node["body"];
					if($type=="plain") {
						$temp=wordwrap($temp,120);
						$temp=htmlentities($temp,ENT_COMPAT,"UTF-8");
						$temp=str_replace(array(" ","\t","\n"),array("&nbsp;",str_repeat("&nbsp;",8),"<br/>\n"),$temp);
						$temp=saltos_make_clickable($temp);
						$temp=href_replace($temp);
					}
					if($type=="html") {
						$temp=remove_script_tag($temp);
						$temp=remove_style_tag($temp);
						$temp=saltos_make_clickable($temp);
						$temp=href_replace($temp);
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
									$url="?action=getmail&id=${id}&cid=${chash2}";
									$temp=str_replace("cid:${cid2}",$url,$temp);
								}
							}
						}
					}
					if(!$first) $buffer.=__HTML_SEPARATOR__;
					if($type=="plain") $buffer.=__PLAIN_TEXT_OPEN__.$temp.__PLAIN_TEXT_CLOSE__;
					if($type=="html") $buffer.=__HTML_TEXT_OPEN__.$temp.__HTML_TEXT_CLOSE__;
					$first=0;
				}
			}
			$buffer.=__HTML_PAGE_CLOSE__;
			output_handler(array(
				"data"=>$buffer,
				"type"=>"text/html",
				"cache"=>false,
				"extra"=>array("x-frame-options: SAMEORIGIN")
			));
		} else {
			$result=__getmail_getcid(__getmail_getnode("0",$decoded),$cid);
			if(!$result) die();
			$name=$result["cname"]?$result["cname"]:$result["cid"];
			output_handler(array(
				"data"=>$result["body"],
				"type"=>$result["ctype"],
				"size"=>$result["csize"],
				"cache"=>false,
				"name"=>$name
			));
		}
		die();
	}
	if(eval_bool(getDefault("debug/cancelgetmail"))) die();
	// CHECK THE SEMAPHORE
	$semaphore=array(getParam("action"),current_user());
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
		__getmail_insert($file,getDefault("debug/getmailmsgid"),1,0,0,0,0,0,0,"");
		die();
	}
	// DATOS POP3
	$query="SELECT * FROM tbl_usuarios_c WHERE id_usuario='".current_user()."' AND email_disabled='0'";
	$result=execute_query_array($query);
	if(!count($result)) {
		if(!getParam("ajax")) {
			session_error(LANG("msgnotpop3email","correo"));
			javascript_history(-1);
		}
		semaphore_release($semaphore);
		javascript_headers();
		die();
	}
	// BEGIN THE LOOP
	$newemail=0;
	$haserror=0;
	$voice_ids=array();
	foreach($result as $row) {
		if(time_get_usage()>getDefault("server/percentstop")) break;
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
			$olduidls=execute_query_array($query);
			$query="SELECT uidl FROM tbl_correo_d WHERE id_cuenta='${id_cuenta}'";
			$olduidls_d=execute_query_array($query);
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
				if(time_get_usage()>getDefault("server/percentstop")) break;
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
							$message=""; // TRICK TO RELEASE MEMORY
						}
					}
					if($error=="") {
						$messageid=$id_cuenta."/".$uidls[$index];
						$last_id=__getmail_insert($file,$messageid,1,0,0,0,0,0,0,"");
						$newemail++;
						$voice_ids[]=$last_id;
					}
				}
			}
		}
		if($error=="" && $row["pop3_delete"]) {
			// REMOVE ALL EXPIRED MESSAGES (IF CHECKED THE DELETE OPTION)
			$delete="'".implode("','",$uidls)."'";
			$query="SELECT uidl,datetime FROM (SELECT uidl,datetime FROM tbl_correo WHERE uidl IN ($delete) UNION SELECT uidl,datetime FROM tbl_correo_d WHERE uidl IN ($delete)) a";
			$result2=execute_query_array($query);
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
			$query=make_delete_query("tbl_correo_d","uidl IN (${delete})");
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
			$query="SELECT COUNT(*) FROM tbl_correo WHERE state_new='1' AND id_cuenta IN (SELECT id FROM tbl_usuarios_c WHERE id_usuario='".current_user()."')";
			$count=execute_query($query);
			if($count) javascript_template("number_correo($count);");
			if($count) javascript_template("favicon_animate($count);");
			javascript_history(0,$condition);
		}
	}
	// VOICE FEATURES
	if($newemail>0) {
		javascript_template("notify_voice('".$newemail.LANG_ESCAPE("msgnewokpop3email".min($newemail,2),"correo")."')","typeof(saltos_voice)=='function' && saltos_voice()");
	}
	if(count($voice_ids)) {
		$query="SELECT SUBSTR(CONCAT(de,'. ',(CASE WHEN subject='' THEN '".LANG("sinsubject","correo")."' ELSE subject END)),1,255) reader FROM tbl_correo WHERE state_spam='0' AND id IN (".implode(",",$voice_ids).") ORDER BY id DESC";
		$result=execute_query_array($query);
		foreach($result as $reader) javascript_template("notify_voice('".str_replace(array("'","\n","\r")," ",$reader)."')","typeof(saltos_voice)=='function' && saltos_voice()");
	}
	// RELEASE SEMAPHORE
	semaphore_release($semaphore);
	javascript_headers();
	die();
}
if(getParam("page")=="correo") {
	$id_correo=abs(getParam("id"));
	$id_extra=explode("_",getParam("id"),3);
	if(isset($id_extra[1]) && isset($id_extra[2]) && $id_extra[1]=="forward") $id_correo=$id_extra[2];
	if($id_correo) {
		// BUSCAR USUARIO DEL CORREO
		$query="SELECT ".make_extra_query_with_login()." FROM tbl_usuarios WHERE id=(SELECT id_usuario FROM tbl_registros_i WHERE id_registro='${id_correo}' AND id_aplicacion='".page2id("correo")."')";
		$usuario=execute_query($query);
		// BUSCAR GRUPO DEL CORREO
		$query="SELECT nombre FROM tbl_grupos WHERE id=(SELECT id_grupo FROM tbl_usuarios WHERE id=(SELECT id_usuario FROM tbl_registros_i WHERE id_registro='${id_correo}' AND id_aplicacion='".page2id("correo")."'))";
		$grupo=execute_query($query);
		// BUSCAR DATETIME DEL CORREO
		$query="SELECT datetime FROM tbl_correo WHERE id='${id_correo}'";
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
		foreach($result2 as $file) {
			$fichero=$file["cname"];
			$size=$file["hsize"];
			$chash=$file["chash"];
			$download="download2('correo','${id_correo}','${chash}')";
			$viewpdf="viewpdf2('correo','${id_correo}','${chash}')";
			set_array($rows[$key],"row",array("id"=>$chash,"usuario"=>$usuario,"grupo"=>$grupo,"datetime"=>$datetime,"fichero"=>$fichero,"fichero_size"=>$size,"download"=>$download,"viewpdf"=>$viewpdf));
		}
	}
	if(isset($id_extra[1]) && $id_extra[1]=="session") {
		require_once("php/getmail.php");
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
			$size=__getmail_gethumansize($file["size"]);
			$download="download2('correo','session','${key2}')";
			$viewpdf="viewpdf2('correo','session','${key2}')";
			set_array($rows[$key],"row",array("id"=>$key2,"usuario"=>$usuario,"grupo"=>$grupo,"datetime"=>$datetime,"fichero"=>$fichero,"fichero_size"=>$size,"download"=>$download,"viewpdf"=>$viewpdf));
		}
	}
	if(isset($id_extra[1]) && isset($id_extra[2]) && $id_extra[1]=="feed") {
		// MARCAR FEED COMO LEIDO SI ES EL PROPIETARIO
		$query=make_update_query("tbl_feeds",array(
			"state_new"=>0
		),"id=(SELECT id_registro FROM tbl_registros_i WHERE id_aplicacion='".page2id("feeds")."' AND id_registro='${id_extra[2]}' AND id_usuario='".current_user()."')");
		db_query($query);
	}
}
?>