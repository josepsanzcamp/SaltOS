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

if(typeof(__voice__)=="undefined" && typeof(parent.__voice__)=="undefined") {
	"use strict";
	var __voice__=1;

	// FOR NORMAL VOICE OPERATIONS
	var voice_texts=[];
	var voice_executing=0;
	var voice_playing=0;

	function notify_voice(text) {
		if(!exists_voice()) return;
		if(!saltos_voice()) return;
		voice_texts.push(text);
	}

	function stop_voice() {
		if(!exists_voice()) return;
		$("#voice").jPlayer("stop");
	}

	function play_voice() {
		if(!exists_voice()) return;
		if(voice_executing) $("#voice").jPlayer("play");
	}

	function next_voice() {
		if(!exists_voice()) return;
		stop_voice();
		voice_executing=0;
		toolbar_voice();
	}

	function cancel_voice() {
		if(!exists_voice()) return;
		while(voice_texts.length>0) voice_texts.shift();
		next_voice();
	}

	function enable_voice() {
		if(!exists_voice()) return;
		setIntCookie("saltos_voice",1);
		toolbar_voice();
		notify_voice(lang_voicetxt());
	}

	function disable_voice() {
		if(!exists_voice()) return;
		setIntCookie("saltos_voice",0);
		cancel_voice();
	}

	function saltos_voice() {
		return getIntCookie("saltos_voice");
	}

	function exists_voice() {
		return $(".playvoice").length;
	}

	function toolbar_voice() {
		if(!exists_voice()) return;
		// NORMAL TOOLBAR
		if(!saltos_voice() || !voice_executing) {
			$(".playvoice,.stopvoice,.nextvoice,.cancelvoice").parent().addClass("none");
		} else {
			if(voice_executing) {
				if(voice_playing) {
					$(".playvoice").addClass("ui-state-disabled");
					$(".stopvoice,.cancelvoice").removeClass("ui-state-disabled");
				} else {
					$(".stopvoice").addClass("ui-state-disabled");
					$(".playvoice,.cancelvoice").removeClass("ui-state-disabled");
				}
				if(voice_texts.length>0) {
					$(".nextvoice").removeClass("ui-state-disabled");
				} else {
					$(".nextvoice").addClass("ui-state-disabled");
				}
			} else {
				$(".playvoice,.stopvoice,.nextvoice,.cancelvoice").addClass("ui-state-disabled");
			}
			$(".playvoice,.stopvoice,.nextvoice,.cancelvoice").parent().removeClass("none");
		}
	}

	$(function() {
		if(!exists_voice()) return;
		$("body").append("<div id='voice'></div>");
		$("#voice").jPlayer({
			swfPath:"lib/jquery/jquery.jplayer.swf",
			volume:1,
			play:function() {
				voice_playing=1;
				toolbar_voice();
			},
			pause:function() {
				voice_playing=0;
				toolbar_voice();
			},
			ended:function() {
				voice_executing=0;
				toolbar_voice();
			},
			errorAlerts:false,
			warningAlerts:false,
			solution:"html,flash",
			supplied:"mp3",
			ready:function() {
				setInterval(function() {
					if(voice_executing) {
						// NOTHING TO DO
					} else if(voice_texts.length>0) {
						var text=voice_texts.shift();
						var url="?action=voice&text="+encodeURIComponent(text);
						$("#voice").jPlayer("setMedia",{ mp3:url });
						$("#voice").jPlayer("play");
						voice_executing=1;
						toolbar_voice();
					}
				},100);
			},
			error:function(event) {
				//~ console.debug("Error event: type = " + event.jPlayer.error.type);
			}
		});
		setTimeout(function() {
			toolbar_voice();
			notify_voice(lang_welcometosaltos());
		},100);
	});

}
