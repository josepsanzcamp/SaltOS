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

if(typeof(__favicon__)=="undefined" && typeof(parent.__favicon__)=="undefined") {
	"use strict";
	var __favicon__=1;

	var favicon_running=0;

	function favicon_animate(num) {
		if(num>0) {
			var num1=((num>99)?99:num)*2;
			var num2=num1+1;
			jQuery.favicon.animate(
				"?action=favicon&format=animation",
				"?action=favicon&format=alternate",
				{
					frames:[num1,num2],
					interval:1000,
					onDraw:function() {
						favicon_running=1;
					},
					onStop:function() {
						jQuery.favicon("img/favicon.png");
						favicon_running=0;
					}
				}
			);
		} else {
			jQuery.favicon.unanimate();
		}
	}

	$(function() {
		$(document).on("mouseover",function() {
			if(favicon_running) favicon_animate(0);
		}).on("keydown",function() {
			if(favicon_running) favicon_animate(0);
		});
	});

}
