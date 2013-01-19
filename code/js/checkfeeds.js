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

if(typeof(__checkfeeds__)=="undefined" && typeof(parent.__checkfeeds__)=="undefined") {
	"use strict";
	var __checkfeeds__=1;

	var feeds_executing=0;

	function check_feeds(sync) {
		if(typeof(sync)=="undefined") var sync=0;
		// PREVENT OVERLOAD
		if(feeds_executing && sync) {
			alerta(lang_inbackground());
		}
		if(feeds_executing) return;
		feeds_executing=1;
		// SOME CHECKS
		if($(".ui-layout-west").text()=="") {
			feeds_executing=0;
			return;
		}
		// CHECK IF IT IS SYNC
		if(sync) {
			setParam("action","feeds");
			submit1(function () { feeds_executing=0; });
			return
		}
		// NORMAL USAGE
		var data="action=feeds&ajax=1";
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				$(".ui-layout-center").append(response);
				feeds_executing=0;
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				feeds_executing=0;
			}
		});
	}

	function is_feeds_list() {
		if(typeof(getParam)!='function') return 0;
		return (getParam("page")=="feeds" && getParam("action")=="list")?1:0;
	}

	function update_feeds_list() {
		var islist=(getParam("page")=="feeds" && getParam("action")=="list")?1:0;
		var nocheck=$("input.slave[type=checkbox]:checked").length?0:1;
		var istop=$(document).scrollTop()>1?0:1;
		var intab=$(".tabs").tabs("option","active")?0:1;
		var noover=$("td.ui-state-highlight").length?0:1;
		return islist && nocheck && istop && intab && noover;
	}

	function number_feeds(num) {
		var obj=$(".number_feeds");
		$(obj).each(function() {
			var padre=$(this).parent();
			unmake_numbers(padre);
			var html=$(this).html();
			html=strtok(html,"(");
			html=html+="("+num+")";
			$(this).html(html);
			make_numbers(padre);
		});
	}

	$(function() {
		if(config_feeds_interval()>0) {
			var feeds_counter=config_feeds_interval();
			setInterval(function() {
				feeds_counter=feeds_executing?0:feeds_counter+100;
				if(feeds_counter>=config_feeds_interval()) {
					if(is_feeds_list()) {
						var disabled=$("#recibir").hasClass("ui-state-disabled");
						$("#recibir").addClass("ui-state-disabled");
					}
					check_feeds();
					if(is_feeds_list()) {
						var interval=setInterval(function() {
							if(!feeds_executing) {
								clearInterval(interval);
								if(disabled) $("#recibir").addClass("ui-state-disabled");
								if(!disabled) $("#recibir").removeClass("ui-state-disabled");
							}
						},100);
					}
					feeds_counter=0;
				}
			},100);
		}
	});

}
