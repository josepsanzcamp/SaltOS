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

if(typeof(__checkindexing__)=="undefined" && typeof(parent.__checkindexing__)=="undefined") {
	"use strict";
	var __checkindexing__=1;

	var indexing_executing=0;

	function check_indexing() {
		if(indexing_executing) return;
		indexing_executing=1;
		// SOME CHECKS
		if($(".ui-layout-west").text()=="") {
			indexing_executing=0;
			return;
		}
		// NORMAL USAGE
		var data="action=indexing";
		$.ajax({
			url:"",
			data:data,
			type:"get",
			success:function(response) {
				$(".ui-layout-center").append(response);
				indexing_executing=0;
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				indexing_executing=0;
			}
		});
	}

	$(function() {
		if(config_indexing_interval()>0) {
			var indexing_counter=config_indexing_interval();
			setInterval(function() {
				indexing_counter=indexing_executing?0:indexing_counter+100;
				if(indexing_counter>=config_indexing_interval()) {
					check_indexing();
					indexing_counter=0;
				}
			},100);
		}
	});

}
