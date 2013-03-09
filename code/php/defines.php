<?php
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
define("__INFO_NAME__","SaltOS");
define("__INFO_VERSION__","3.1");
define("__INFO_REVISION__",svnversion("../code"));
define("__INFO_COPYRIGHT__","Copyright (C) 2013 by Josep Sanz Campderrós");

function post_defines() {
	define("__HTML_PAGE_OPEN__",'<html><head><style type="text/css">'.getDefault("defines/htmlpage").'</style></head><body>');
	define("__HTML_PAGE_CLOSE__",'</body></html>');
	define("__HTML_BOX_OPEN__",'<div style="'.getDefault("defines/htmlbox").'">');
	define("__HTML_BOX_CLOSE__",'</div>');
	define("__HTML_TABLE_OPEN__",'<table>');
	define("__HTML_TABLE_CLOSE__",'</table>');
	define("__HTML_ROW_OPEN__",'<tr>');
	define("__HTML_ROW_CLOSE__",'</tr>');
	define("__HTML_CELL_OPEN__",'<td>');
	define("__HTML_RCELL_OPEN__",'<td align="right" nowrap="nowrap">');
	define("__HTML_CELL_CLOSE__",'</td>');
	define("__HTML_TEXT_OPEN__",'<span style="'.getDefault("defines/htmltext").'">');
	define("__HTML_TEXT_CLOSE__",'</span>');
	define("__PLAIN_TEXT_OPEN__",'<span style="'.getDefault("defines/plaintext").'">');
	define("__PLAIN_TEXT_CLOSE__",'</span>');
	define("__HTML_SEPARATOR__",'<hr style="'.getDefault("defines/separator").'"/>');
	define("__HTML_NEWLINE__",'<br/>');
	define("__BLOCKQUOTE_OPEN__",'<blockquote style="'.getDefault("defines/blockquote").'">');
	define("__BLOCKQUOTE_CLOSE__",'</blockquote>');
	define("__SIGNATURE_OPEN__",'<span style="'.getDefault("defines/signature").'">');
	define("__SIGNATURE_CLOSE__",'</span>');
}
?>