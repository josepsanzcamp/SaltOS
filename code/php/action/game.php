<?php
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
if(!check_user()) action_denied();
if(getParam("action")=="game") {
	include("php/defines.php");
	ob_start();
	echo __PAGE_HTML_OPEN__;
	echo "<canvas></canvas>";
	echo "<style type='text/css'>canvas{margin-top:45px;}div{text-align:right;}</style>";
	echo "<script type='text/javascript'>".base64_decode("Yz1kb2N1bWVudC5ib2R5LmNoaWxkcmVuWzBdO2g9dD0xNTA7TD13PWMud2lkdGg9ODAwO3U9RD01MDtIPVtdO1I9TWF0aC5yYW5kb207Zm9yKCQgaW4gQz1jLmdldENvbnRleHQoJzJkJykpQ1skW0o9WD1ZPTBdKygkWzZdfHwnJyldPUNbJF07c2V0SW50ZXJ2YWwoImlmKEQpZm9yKHg9NDA1LGk9eT1JPTA7aTwxZTQ7KUw9SFtpKytdPWk8OXxMPHcmUigpPC4zP3c6UigpKnUrODB8MDskPSsrdCU5OS11OyQ9JCokLzgrMjA7eSs9WTt4Kz15LUhbKHgrWCkvdXwwXT45PzA6WDtqPUhbbz14L3V8MF07WT15PGp8WTwwP1krMTooeT1qLEo/LTEwOjApO3dpdGgoQyl7QT1mdW5jdGlvbihjLHgseSxyKXtyJiZhcmMoeCx5LHIsMCw3LDApO2ZpbGxTdHlsZT1jLlA/YzonIycrJ2NlZmY5OWZmNzhmODZlZWFhZmZmZmQ0NTMzMycuc3Vic3RyKGMqMywzKTtmKCk7YmEoKX07Zm9yKEQ9Wj0wO1o8MjE7WisrKXtaPDcmJkEoWiU2LHcvMiwyMzUsWj8yNTAtMTUqWjp3KTtpPW8tNStaO1M9eC1pKnU7Qj1TPjkmUzw0MTt0YSh1LVMsMCk7Rz1jTCgwLFQ9SFtpXSwwLFQrOSk7VCU2fHwoQSgyLDI1LFQtNyw1KSx5Xmp8fEImJihIW2ldLT0uMSxJKyspKTtHLlA9Ry5hZGRDb2xvclN0b3A7Ry5QKDAsaSU3PycjN2UzJzooaV5vfHx5XlR8fCh5PUhbaV0rPSQvOTkpLCcjYzdhJykpO0cuUCgxLCcjY2E2Jyk7aSU0JiZBKDYsdC8yJTIwMCw5LGklMj8yNzozMyk7bSgtNixoKTtxdCgtNixULDMsVCk7bCg0NyxUKTtxdCg1NixULDU2LGgpO0EoRyk7aSUzPzA6VDx3PyhBKEcsMzMsVC0xNSwxMCksZmMoMzEsVC03LDQsOSkpOihBKDcsMjUsJCw5KSxBKEcsMjUsJCw1KSxmYygyNCwkLDIsaCksRD1CJnk+JC05PzE6RCk7dGEoUy11LDApfUEoNix1LHktOSwxMSk7QSg1LE09dStYKi43LFE9eS05K1kvNSw4KTtBKDgsTSxRLDUpO2Z4KEkrJ2MnLDUsMTUpfUQ9eT5oPzE6RCIsdSk7b25rZXlkb3duPW9ua2V5dXA9ZnVuY3Rpb24oZSl7RT1lLnR5cGVbNV0/NDowO2U9ZS5rZXlDb2RlO0o9ZV4zOD9KOkU7WD1lXjM3P2VeMzk/WDpFOi1FfQ==")."</script>";
	echo "<script type='text/javascript'>".file_get_contents("lib/timbre/timbre.min.js")."</script>";
	echo "<script type='text/javascript'>".minify_js(file_get_contents("lib/timbre/004.js"))."</script>";
	echo "<script type='text/javascript'>ex0.play();</script>";
	echo __TEXT_HTML_OPEN__;
	echo "<div>Game: <b>Legend of The Bouncing Beholder</b> | Copyright (c) 2010 Marijn Haverbeke (ZLib/LibPNG license) | <a href='javascript:void(0)' onclick='parent.openwin(\"http://marijn.haverbeke.nl/js1k.html\")'>marijn.haverbeke.nl/js1k.html</a></div>";
	echo "<div>Music: <b>JavaScript Library for Objective Sound Programming</b> | Timbre Synthesizer Example (MIT license) | <a href='javascript:void(0)' onclick='parent.openwin(\"http://mohayonao.github.com/timbre/\")'>mohayonao.github.com/timbre/</a></div>";
	echo __TEXT_HTML_CLOSE__;
	echo __PAGE_HTML_CLOSE__;
	$buffer=ob_get_clean();
	$hash=md5($buffer);
	header_etag($hash);
	ob_start_protected(getDefault("obhandler"));
	header_powered();
	header_expires();
	header("Content-Type: text/html");
	header("x-frame-options: SAMEORIGIN");
	echo $buffer;
	ob_end_flush();
	die();
}
?>