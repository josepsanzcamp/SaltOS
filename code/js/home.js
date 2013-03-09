/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2013 by Josep Sanz Campderrós
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

if(typeof(__home__)=="undefined" && typeof(parent.__home__)=="undefined") {
	"use strict";
	var __home__=1;

	function __update_home_helper(id_folder) {
		if(typeof(id_folder)=="undefined") var id_folder=0;
		$(".boxs:visible").each(function() {
			if(!id_folder) {
				id_folder=get_class_id(this);
				if(!id_folder) {
					$(this).trigger("resize");
					return;
				}
			}
			$(this).removeClass("id_"+id_folder);
			var boxs=this;
			var data="action=home&id_folder="+id_folder;
			$.ajax({
				url:"xml.php",
				data:data,
				type:"get",
				success:function(response) {
					$(boxs).html("");
					$("root > *",response).each(function() {
						$(boxs).append("<div></div>");
						var divs=$("div:last",boxs);
						$("row",this).each(function() {
							var data=$("data",this).text();
							var clase=$("class",this).text();
							var estilo=$("style",this).text();
							var div="<div class='"+clase+"' style='"+estilo+"'>"+data+"</div>";
							$(divs).append(div);
						});
						// SUPPORT FOR LTR AND RTL LANGS
						var dir=$("html").attr("dir");
						$(divs).masonry({
							itemSelector:".box",
							isResizable:true,
							isRTL:(dir=="rtl")
						});
					});
					unloadingcontent();
				},
				error:function(XMLHttpRequest,textStatus,errorThrown) {
					errorcontent(XMLHttpRequest.status,XMLHttpRequest.statusText);
				}
			});
		});
	};

	function update_home(id_folder) {
		if(typeof(id_folder)=="undefined") var id_folder=0;
		if(id_folder) {
			__update_home_helper(id_folder);
		} else {
			setTimeout(function() {
				var active=getIntCookie("saltos_home_tab");
				$(".tabs").tabs("option","active",active);
				$(".tabs").bind("tabsactivate",function(event,ui) {
					__update_home_helper();
					if(!$(".boxs:visible").length) return;
					var active=$(".tabs").tabs("option","active");
					setIntCookie("saltos_home_tab",active);
				});
				__update_home_helper();
			},100);
		}
	}

}

"use strict";
$(function() { update_home(); });
