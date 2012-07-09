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

if(typeof(__checkagenda__)=="undefined" && typeof(parent.__checkagenda__)=="undefined") {
	"use strict";
	var __checkagenda__=1;

	var agenda_executing=0;

	function check_agenda() {
		if(agenda_executing) return;
		agenda_executing=1;
		// SOME CHECKS
		if($(".ui-layout-west").text()=="") {
			agenda_executing=0;
			return;
		}
		// GET ALL IDS AND HASHS
		var id_hash=new Array();
		$(".jGrowl-notification").each(function() {
			var id=get_class_id(this);
			var hash=get_class_hash(this);
			if(id && hash) id_hash.push(id+"_"+hash);
		});
		id_hash=implode(",",id_hash);
		// AJAX CALL
		var data="action=agenda&id_hash="+id_hash;
		$.ajax({
			url:"xml.php",
			data:data,
			type:"get",
			success:function(response) {
				$(".ui-layout-center").append(response);
				agenda_executing=0;
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				agenda_executing=0;
			}
		});
	}

	function menu_agenda(num) {
		var obj=$(".agenda");
		$(obj).each(function() {
			var padre=$(this).parent();
			unmake_numbers(padre);
			var html=$(this).html();
			html=strtok(html,"(");
			html=html+="("+num+")";
			$(this).html2(html);
			make_numbers(padre);
		});
	}

	$(document).ready(function() {
		if(config_agenda_interval()>0) {
			var agenda_counter=config_agenda_interval();
			setInterval(function() {
				agenda_counter=agenda_executing?0:agenda_counter+1000;
				if(agenda_counter>=config_agenda_interval()) {
					check_agenda();
					agenda_counter=0;
				}
			},1000);
		}
	});

}
