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

if(typeof(__qrcode__)=="undefined" && typeof(parent.__qrcode__)=="undefined") {
	"use strict";
	var __qrcode__=1;

	function qrcode2(page,id) {
		loadingcontent(lang_view2opening());
		var title=lang_qrcode();
		var url="?action=qrcode&page="+page+"&id="+abs(id);
		var html="<img src='"+url+"' />";
		if(typeof(id)=="undefined") {
			var temp=explode(":",page);
			if(temp.length==2 &&  temp[0]=="tel") {
				title=lang_telefono();
				var tel="<h1 class='tel'>"+temp[1]+"</h1>";
				temp[1]=str_replace(" ","",temp[1]);
				temp=implode(":",temp);
				var url="?action=qrcode&msg="+encodeURIComponent(temp);
				var img="<img src='"+url+"' />";
				html=tel+img;
			} else {
				var url="?action=qrcode&msg="+encodeURIComponent(page);
				var img="<img src='"+url+"' />";
				html=img;
			}
		}
		var dialog2=$("#dialog");
		$(dialog2).html(html);
		var timeout=300;
		var interval=setInterval(function() {
			$("img",dialog2).each(function() {
				if(this.complete) {
					clearInterval(interval);
					unloadingcontent();
					dialog(title);
					$(dialog2).dialog("open");
					$(dialog2).dialog("option","width",$(this).width()+33);
					$(dialog2).dialog("option","position",{ my:"center",at:"center",of:window });
				} else {
					timeout--;
				}
				if(timeout<=0) {
					clearInterval(interval);
					unloadingcontent();
					dialog(lang_error(),lang_view2error(),{});
					return;
				}
			});
		},100);
	}

}
