/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2018 by Josep Sanz Campderrós
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

if(typeof(__checkpurge__)=="undefined" && typeof(parent.__checkpurge__)=="undefined") {
	"use strict";
	var __checkpurge__=1;

	var purge_executing=0;

	function check_purge() {
		if(purge_executing) return;
		purge_executing=1;
		// SOME CHECKS
		if($(".ui-layout-west").text()=="") {
			purge_executing=0;
			return;
		}
		// NORMAL USAGE
		var data="action=purge";
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				$(".ui-layout-center").append(response);
				purge_executing=0;
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				purge_executing=0;
			}
		});
	}

	$(function() {
		if(config_purge_interval()>0) {
			var purge_counter=config_purge_interval();
			setInterval(function() {
				purge_counter=purge_executing?0:purge_counter+100;
				if(purge_counter>=config_purge_interval()) {
					check_purge();
					purge_counter=0;
				}
			},100);
		}
	});

}
