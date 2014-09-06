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

if(typeof(__calendar__)=="undefined" && typeof(parent.__calendar__)=="undefined") {
	"use strict";
	var __calendar__=1;

	function update_calendar() {
		if(!$("[id^=cell_]").length) return;
		var data="action=calendar&offset="+getIntCookie("saltos_calendar_offset");
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				// PUT SELECT OPTIONS
				var options="";
				$("root>options>option",response).each(function() {
					var label=$("label",this).text();
					var value=$("value",this).text();
					var selected=($("selected",this).text()=="1")?"selected='selected'":"";
					options+="<option value='"+value+"' "+selected+">"+label+"</option>";
				});
				$("#mesano").html(options);
				// PUT CELLS DATA
				$("[id^=cell_]").each(function() {
					$(this).html("");
				});
				var extra=new Array();
				for(var i=1;i<=6;i++) extra[i]="";
				$("root>rows>row",response).each(function() {
					var cell=$("cell",this).text();
					var data=$("data",this).text();
					var clase=$("class",this).text();
					var estilo=$("style",this).text();
					var div="<div class='"+clase+"' style='"+estilo+"'>"+data+"</div>";
					if(substr(cell,0,1)=="g") {
						extra[substr(cell,1,1)]+=div;
					} else {
						$("#cell_"+cell).append(div);
					}
				});
				for(var i=1;i<=6;i++) $("#cell_f"+i).append(extra[i]);
				$("[id^=cell_] span.opened").each(function() {
					openclose_calendar(this,"[+]");
				});
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
			}
		});
	}

	function openclose_calendar(obj,force) {
		if(typeof(force)=="undefined") var force="";
		var ndash=$("<div/>").append("&ndash;").text();
		$("span",$(obj).parent()).each(function() {
			var action=$(this).text();
			var this2=$(this).parent().parent();
			if(action=="[+]" && (force=="" || force=="[+]")) {
				$(this).html("["+ndash+"]");
				$(this2).attr("oldheight",$(this2).height());
				$(this2).height("auto");
			} else if(action=="["+ndash+"]" && (force=="" || force=="[-]")) {
				$(this).html("[+]");
				$(this2).height($(this2).attr("oldheight")+"px");
			}
		});
	}

	function openclose_all_calendar(force) {
		$("[id^=cell_] span").each(function() {
			openclose_calendar(this,force);
		});
	}
}

"use strict";
$(function() { update_calendar(); });
