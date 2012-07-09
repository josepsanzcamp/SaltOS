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

"use strict";
$(document).ready(function() {
	// FOR ABOUT TAB
	$(".item").each(function() { var old=$(this).html(); $(this).html2("&nbsp;&diams;&nbsp;"+old); });
	$(".item a").css("font-weight","normal");
	$(".sectiontitle").addClass("ui-widget-header ui-corner-top");
	$(".sectioncontent").addClass("ui-state-default").css("border","none");
	$(".sectioncontentl").addClass("ui-state-default").css("border-top","none").css("border-bottom","none").css("border-right","none");
	$(".sectioncontentr").addClass("ui-state-default").css("border-top","none").css("border-bottom","none").css("border-left","none");
	$(".sectioncontentb").addClass("ui-state-default").css("border-top","none").css("border-right","none").css("border-left","none");
	$(".sectioncontentbl").addClass("ui-state-default ui-corner-bl").css("border-top","none").css("border-right","none");
	$(".sectioncontentbr").addClass("ui-state-default ui-corner-br").css("border-top","none").css("border-left","none");
	$(".vxinfo").each(function() { var temp=$(this).html(); temp=explode(":",temp); temp[0]="<b>"+temp[0]+"</b>"; temp=implode(":",temp); $(this).html2(temp); });
	// FOR FOCUS THE GAME & MUSIC
	setTimeout(function() {
		$("#tabs").bind("tabsshow",function(event,ui) {
			var pattern="iframe[name$=default_0_game]";
			if($(pattern).is(":visible")) {
				$(pattern).focus();
				ex0.play();
				var interval=setInterval(function() {
					if(!$(pattern).is(":visible")) {
						clearInterval(interval);
						ex0.pause();
					}
				},100);
			}
		});
	},100);
});
