/*
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2015 by Josep Sanz Campderrós
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

if(typeof(__checksession__)=="undefined" && typeof(parent.__checksession__)=="undefined") {
	"use strict";
	var __checksession__=1;

	var session_executing=0;

	function check_session(action2) {
		if(session_executing) return;
		session_executing=1;
		// SOME CHECKS
		if($(".ui-layout-west").text()=="") {
			session_executing=0;
			return;
		}
		// NORMAL USAGE
		var data="action=session"+((typeof(action2)=="undefined")?"":"&action2="+action2);
		$.ajax({
			url:"index.php",
			data:data,
			type:"get",
			success:function(response) {
				$(".ui-layout-center").append(response);
				session_executing=0;
			},
			error:function(XMLHttpRequest,textStatus,errorThrown) {
				session_executing=0;
			}
		});
	}

	$(function() {
		if(config_session_interval()>0) {
			var session_counter=config_session_interval();
			setInterval(function() {
				session_counter=session_executing?0:session_counter+100;
				if(session_counter>=config_session_interval()) {
					check_session();
					session_counter=0;
				}
			},100);
		}
	});

}
