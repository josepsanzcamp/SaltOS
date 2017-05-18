/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2017 by Josep Sanz Campderrós
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

if(typeof(__correo__)=="undefined" && typeof(parent.__correo__)=="undefined") {
	"use strict";
	var __correo__=1;

	function update_state(id,type,value) {
		if(!id) return;
		var data="action=ajax&query=state&id="+id+"&type="+type+"&value="+value;
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function (response) {
				if(type=="new") {
					if(value==1) notice(lang_alert(),lang_msgnumnoleidos());
					if(value==0) notice(lang_alert(),lang_msgnumsileidos());
				}
				if(type=="wait") {
					if(value==1) notice(lang_alert(),lang_msgnumsiwait());
					if(value==0) notice(lang_alert(),lang_msgnumnowait());
				}
				if(type=="spam") {
					if(value==1) notice(lang_alert(),lang_msgnumsispam());
					if(value==0) notice(lang_alert(),lang_msgnumnospam());
				}
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function update_files() {
		if(getParam("action")!="form") return;
		var id=abs(getParam("id"));
		var id_extra=explode("_",$("input[name$=id_extra]").val(),3);
		if(id_extra.length==3 && id_extra[1]=="forward") id=id_extra[2];
		if(id_extra.length==3 && id_extra[1]=="session") id=id_extra[1];
		if(!$("td[id$=files],label[id$=files]").length) id=0;
		if(!id) return;
		var data="action=getmail&id="+id+"&cid=files";
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function (response) {
				$("td[id$=files],label[id$=files]").html(response);
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function update_auto() {
		if(getParam("action")!="form") return;
		var id=abs(getParam("id"));
		if(id) return;
		var pattern="input[name$=_para],input[name$=_cc],input[name$=_bcc]";
		$(pattern).autocomplete({
			delay:300,
			source:function(request,response) {
				var emails=explode(";",request.term);
				var term=trim(emails.pop());
				var input=this.element;
				var data="action=ajax&query=emails&term="+encodeURIComponent(term);
				$.ajax({
					url:"index.php",
					data:data,
					type:"get",
					success:function(data) {
						// TO CANCEL OLD REQUESTS
						var emails=explode(";",$(input).val());
						var term2=trim(emails.pop());
						if(term==term2) response(data["rows"]);
					},
					error:function(XMLHttpRequest,textStatus,errorThrown) {
						errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
					}
				});
			},
			search:function() {
				var emails=explode(";",this.value);
				var term=trim(emails.pop());
				return term.length>0;
			},
			focus:function() {
				return false;
			},
			select:function(event,ui) {
				var emails1=explode(";",this.value);
				emails1.pop();
				emails1.push(ui.item.label);
				for(var i=0,len=emails1.length;i<len;i++) {
					emails1[i]=trim(emails1[i]);
				}
				for(var i=emails1.length-1;i>0;i--) {
					for(var j=i-1;j>=0;j--) {
						if(emails1[i]==emails1[j]) emails1[i]="";
					}
				}
				var emails2="";
				for(var i=0,len=emails1.length;i<len;i++) {
					if(emails1[i]!="") emails2+=emails1[i]+"; ";
				}
				this.value=emails2;
				return false;
			}
		});
	}

	var old_signature="";

	function update_signature(update) {
		if(getParam("action")!="form") return;
		if(typeof(update)=="undefined") var update=0;
		var id_cuenta=$("select[name$=id_cuenta]").val();
		if(!id_cuenta) return;
		if(update) {
			var body=$("textarea[name$=body]");
			var cc=$("input[name$=_cc]");
			var state_crt=$("input[name$=state_crt]");
			var data="action=signature";
			data+="&old="+old_signature;
			data+="&new="+id_cuenta;
			data+="&body="+encodeURIComponent($(body).val());
			data+="&cc="+encodeURIComponent($(cc).val());
			data+="&state_crt="+($(state_crt).prop("checked")?1:0);
			$.ajax({
				url:"index.php",
				data:data,
				type:"post",
				success:function (response) {
					$(response["rows"]).each(function() {
						$(body).val(this["body"]);
						$(cc).val(this["cc"]);
						$(state_crt).prop("checked",intval(this["state_crt"])?true:false);
					});
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		}
		old_signature=id_cuenta;
	}

	function update_checkbox() {
		if(getParam("action")!="form") return;
		var checkbox="input[name="+$("input[name$=state_new]").attr("name")+"]";
		if($(checkbox).prop("checked")) {
			var interval=setInterval(function() {
				if(!$(checkbox).length) {
					clearInterval(interval);
				} else if($(checkbox).prop("writed")) {
					clearInterval(interval);
				} else {
					$(checkbox).prop("checked",!$(checkbox).prop("checked"));
					if(typeof(__mobile__)!="undefined") $(checkbox).checkboxradio("refresh");
				}
			},1000);
		}
	}

	function update_screen() {
		setTimeout(function() {
			var delta=$(".ui-layout-center table:first").width()-800;
			var width=$(".ui-layout-center").width()-50-delta;
			if(width<800) return;
			$("input,textarea,select,iframe").filter(function () {
				return $(this).width()==800;
			}).width(width);
		},100);
	}

}

"use strict";
$(function() {
	update_files();
	update_auto();
	update_signature();
	update_checkbox();
	update_screen();
});
