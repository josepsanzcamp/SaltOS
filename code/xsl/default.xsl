<?xml version="1.0" encoding="UTF-8" ?>
<!--
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
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="html" version="1.0" encoding="UTF-8" indent="no"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system ="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/>

<xsl:template name="head">
	<xsl:for-each select="/root">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="msapplication-TileColor" content="{info/color}"/>
		<meta name="msapplication-TileImage" content="{info/favicon}"/>
		<meta name="theme-color" content="{info/color}"/>
		<link href="{info/favicon}" rel="icon"></link>
		<link href="{info/favicon}" rel="shortcut icon"></link>
		<title><xsl:call-template name="title_2"/></title>
		<xsl:call-template name="styles"/>
		<xsl:call-template name="javascript"/>
	</xsl:for-each>
</xsl:template>

<xsl:template name="title">
	<xsl:for-each select="/root">
		<div class="tabs2">
			<ul>
				<xsl:for-each select="menu/header/option">
					<li taborder="{taborder}" class="{class2}"><a href="javascript:void(0)" onclick="{onclick}" title="{tip}" class="{class}">
						<xsl:if test="icon!=''">
							<span class="{icon}"></span>
							<xsl:if test="label!=''"><xsl:text> </xsl:text></xsl:if>
						</xsl:if>
						<xsl:value-of select="label"/>
					</a></li>
				</xsl:for-each>
				<li class="texto"><a href="javascript:void(0)" onclick="opencontent('?page=about')"><xsl:call-template name="title_2"/></a></li>
			</ul>
		</div>
	</xsl:for-each>
</xsl:template>

<xsl:template name="title_2">
	<xsl:value-of select="list/title"/><xsl:value-of select="form/title"/><xsl:text> - </xsl:text><xsl:value-of select="info/title"/><xsl:text> - </xsl:text><xsl:value-of select="info/name"/><xsl:text> </xsl:text>v<xsl:value-of select="info/version"/><xsl:text> </xsl:text>r<xsl:value-of select="info/revision"/>
</xsl:template>

<xsl:template name="javascript">
	<xsl:for-each select="javascript/*">
		<xsl:choose>
			<xsl:when test="name()='function'">
				<script type="text/javascript">function <xsl:value-of select="."/></script>
			</xsl:when>
			<xsl:when test="name()='include'">
				<script type="text/javascript" src="{.}?r={/root/info/revision}">
					<xsl:if test="contains(.,'?')">
						<xsl:attribute name="src"><xsl:value-of select="."/>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
					</xsl:if>
				</script>
			</xsl:when>
			<xsl:when test="name()='inline'">
				<script type="text/javascript"><xsl:value-of select="."/></script>
			</xsl:when>
			<xsl:when test="name()='cache'">
				<xsl:choose>
					<xsl:when test="/root/info/usejscache='true'">
						<xsl:if test="count(include)>0">
							<script type="text/javascript" src="">
								<xsl:attribute name="src">?action=cache&amp;files=<xsl:for-each select="include"><xsl:value-of select="."/><xsl:if test="not(position()=last())">,</xsl:if></xsl:for-each>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
							</script>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="include">
							<script type="text/javascript" src="{.}?r={/root/info/revision}">
								<xsl:if test="contains(.,'?')">
									<xsl:attribute name="src"><xsl:value-of select="."/>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
								</xsl:if>
							</script>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
		</xsl:choose>
	</xsl:for-each>
</xsl:template>

<xsl:template name="styles">
	<xsl:for-each select="styles/*">
		<xsl:choose>
			<xsl:when test="name()='include'">
				<link href="{.}?r={/root/info/revision}" rel="stylesheet" type="text/css">
					<xsl:if test="contains(.,'?')">
						<xsl:attribute name="href"><xsl:value-of select="."/>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
					</xsl:if>
				</link>
			</xsl:when>
			<xsl:when test="name()='inline'">
				<style type="text/css"><xsl:value-of select="."/></style>
			</xsl:when>
			<xsl:when test="name()='cache'">
				<xsl:choose>
					<xsl:when test="/root/info/usecsscache='true'">
						<xsl:if test="count(include)>0">
							<link href="" rel="stylesheet" type="text/css">
								<xsl:attribute name="href">?action=cache&amp;files=<xsl:for-each select="include"><xsl:value-of select="."/><xsl:if test="not(position()=last())">,</xsl:if></xsl:for-each>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
							</link>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="include">
							<link href="{.}?r={/root/info/revision}" rel="stylesheet" type="text/css">
								<xsl:if test="contains(.,'?')">
									<xsl:attribute name="href"><xsl:value-of select="."/>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
								</xsl:if>
							</link>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
		</xsl:choose>
	</xsl:for-each>
</xsl:template>

<xsl:template name="menu">
	<xsl:for-each select="/root/menu/group">
		<div class="{class}" id="{name}">
			<h3 title="{tip}"><xsl:value-of select="label"/></h3>
			<div>
				<ul class="accordion-link">
					<xsl:for-each select="option">
						<li><a href="javascript:void(0)" class="{class} ui-state-default" onclick="{onclick}" title="{tip}" id="{name}">
							<xsl:if test="icon!=''">
								<span class="{icon}"></span>
								<xsl:if test="label!=''"><xsl:text> </xsl:text></xsl:if>
							</xsl:if>
							<xsl:value-of select="label"/>
						</a></li>
					</xsl:for-each>
				</ul>
			</div>
		</div>
	</xsl:for-each>
</xsl:template>

<xsl:template name="alert">
	<xsl:for-each select="/root/alerts/alert">
		<script type="text/javascript">$(function() { if(typeof(parent.notice)=="function") parent.notice(lang_alert(),"<xsl:value-of select="."/>",false,"ui-state-highlight");else if(typeof(notice)=="function") notice(lang_alert(),"<xsl:value-of select="."/>",false,"ui-state-highlight"); });</script>
	</xsl:for-each>
</xsl:template>

<xsl:template name="error">
	<xsl:for-each select="/root/errors/error">
		<script type="text/javascript">$(function() { if(typeof(parent.notice)=="function") parent.notice(lang_error(),"<xsl:value-of select="."/>",false,"ui-state-error");else if(typeof(notice)=="function") notice(lang_error(),"<xsl:value-of select="."/>",false,"ui-state-error"); });</script>
	</xsl:for-each>
</xsl:template>

<xsl:template name="list_table_head">
	<td class="width1 thead shortcut_ctrl_a" oldwidth=""><input type="checkbox" class="master" name="master" id="master" value="1"/></td>
	<xsl:for-each select="fields/field">
		<td class="thead" style="width:{width}" oldwidth="{width}">
			<xsl:choose>
				<xsl:when test="tip!=''">
					<span title="{tip}"><xsl:value-of select="label"/></span>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="label"/>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:if test="sort='true'">
				<a href="javascript:void(0)" title="{../../sort/labelasc}">
					<xsl:attribute name="onclick">
						<xsl:call-template name="replace_string">
							<xsl:with-param name="find">FIELD</xsl:with-param>
							<xsl:with-param name="replace"><xsl:choose><xsl:when test="orderasc!=''"><xsl:value-of select="orderasc"/></xsl:when><xsl:when test="order!=''"><xsl:value-of select="order"/></xsl:when><xsl:otherwise><xsl:value-of select="name"/></xsl:otherwise></xsl:choose> asc</xsl:with-param>
							<xsl:with-param name="string" select="../../sort/onclick"/>
						</xsl:call-template>
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="selected='asc'">
							<span class="{../../sort/iconascin}"></span>
						</xsl:when>
						<xsl:otherwise>
							<span class="{../../sort/iconascout}" hover="true" toggle="{../../sort/iconascin} {../../sort/iconascout}"></span>
						</xsl:otherwise>
					</xsl:choose>
				</a>
				<a href="javascript:void(0)" title="{../../sort/labeldesc}">
					<xsl:attribute name="onclick">
						<xsl:call-template name="replace_string">
							<xsl:with-param name="find">FIELD</xsl:with-param>
							<xsl:with-param name="replace"><xsl:choose><xsl:when test="orderdesc!=''"><xsl:value-of select="orderdesc"/></xsl:when><xsl:when test="order!=''"><xsl:value-of select="order"/></xsl:when><xsl:otherwise><xsl:value-of select="name"/></xsl:otherwise></xsl:choose> desc</xsl:with-param>
							<xsl:with-param name="string" select="../../sort/onclick"/>
						</xsl:call-template>
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="selected='desc'">
							<span class="{../../sort/icondescin}"></span>
						</xsl:when>
						<xsl:otherwise>
							<span class="{../../sort/icondescout}" hover="true" toggle="{../../sort/icondescin} {../../sort/icondescout}"></span>
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</xsl:if>
		</td>
	</xsl:for-each>
	<td class=" width1 thead" colspan="100" oldwidth=""><span class="saltos-icon saltos-icon-none"></span></td>
</xsl:template>

<xsl:template name="list_table_data">
	<td class="width1 tbody"><input type="checkbox" class="slave id_{id}" name="slave_{id}" id="slave_{id}" value="1"/></td>
	<xsl:variable name="style" select="action_style"/>
	<xsl:variable name="row" select="*"/>
	<xsl:variable name="id" select="action_id"/>
	<xsl:for-each select="../../fields/field">
		<xsl:variable name="name" select="name"/>
		<xsl:variable name="size" select="size"/>
		<xsl:variable name="class" select="class"/>
		<xsl:for-each select="$row[name()=$name]">
			<td class="tbody {$class} {$style}">
				<xsl:choose>
					<xsl:when test=".=''">
						<xsl:text>-</xsl:text>
					</xsl:when>
					<xsl:when test="substring(.,1,4)='tel:'">
						<a class="tellink draggable id_{$id}" href="javascript:void(0)" onclick="">
							<xsl:attribute name="onclick">qrcode2('<xsl:call-template name="replace_string">
								<xsl:with-param name="find">'</xsl:with-param>
								<xsl:with-param name="replace"> </xsl:with-param>
								<xsl:with-param name="string" select="."/>
							</xsl:call-template>')</xsl:attribute>
							<xsl:call-template name="print_string_length">
								<xsl:with-param name="text" select="substring(.,5)"/>
								<xsl:with-param name="size" select="$size"/>
							</xsl:call-template>
						</a>
					</xsl:when>
					<xsl:when test="substring(.,1,7)='mailto:'">
						<a class="maillink draggable id_{$id}" href="javascript:void(0)" onclick="">
							<xsl:attribute name="onclick">mailto('<xsl:call-template name="replace_string">
								<xsl:with-param name="find">'</xsl:with-param>
								<xsl:with-param name="replace"> </xsl:with-param>
								<xsl:with-param name="string" select="substring(.,8)"/>
							</xsl:call-template>')</xsl:attribute>
							<xsl:call-template name="print_string_length">
								<xsl:with-param name="text" select="substring(.,8)"/>
								<xsl:with-param name="size" select="$size"/>
							</xsl:call-template>
						</a>
					</xsl:when>
					<xsl:when test="substring(.,1,5)='href:'">
						<a class="weblink draggable id_{$id}" href="javascript:void(0);" onclick="">
							<xsl:attribute name="onclick">openwin('<xsl:call-template name="replace_string">
								<xsl:with-param name="find">'</xsl:with-param>
								<xsl:with-param name="replace"> </xsl:with-param>
								<xsl:with-param name="string" select="substring(.,6)"/>
							</xsl:call-template>')</xsl:attribute>
							<xsl:call-template name="print_string_length">
								<xsl:with-param name="text" select="substring(.,6)"/>
								<xsl:with-param name="size" select="$size"/>
							</xsl:call-template>
						</a>
					</xsl:when>
					<xsl:when test="substring(.,1,5)='link:'">
						<a class="applink draggable id_{$id}" href="javascript:void(0)" onclick="">
							<xsl:attribute name="onclick"><xsl:call-template name="replace_string">
								<xsl:with-param name="find">%3A</xsl:with-param>
								<xsl:with-param name="replace">:</xsl:with-param>
								<xsl:with-param name="string" select="substring-before(substring(.,6),':')"/>
							</xsl:call-template></xsl:attribute>
							<xsl:call-template name="print_string_length">
								<xsl:with-param name="text" select="substring-after(substring(.,6),':')"/>
								<xsl:with-param name="size" select="$size"/>
							</xsl:call-template>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="print_string_length">
							<xsl:with-param name="text" select="."/>
							<xsl:with-param name="size" select="$size"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:for-each>
	</xsl:for-each>
</xsl:template>

<xsl:template name="print_string_length">
	<xsl:param name="text"/>
	<xsl:param name="size"/>
	<xsl:choose>
		<xsl:when test="$size!=''">
			<xsl:choose>
				<xsl:when test="string-length($text)>=$size">
					<span title="{$text}"><xsl:value-of select="substring($text,1,$size)"/><xsl:text>...</xsl:text></span>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$text"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$text"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="replace_string">
	<xsl:param name="find"/>
	<xsl:param name="replace"/>
	<xsl:param name="string"/>
	<xsl:choose>
		<xsl:when test="contains($string, $find)">
			<xsl:value-of select="substring-before($string, $find)"/>
			<xsl:value-of select="$replace"/>
			<xsl:call-template name="replace_string">
				<xsl:with-param name="find" select="$find"/>
				<xsl:with-param name="replace" select="$replace"/>
				<xsl:with-param name="string" select="substring-after($string, $find)"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$string"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="list_table_actions">
	<xsl:variable name="id" select="action_id"/>
	<xsl:for-each select="*[substring(name(),1,7)='action_']">
		<xsl:variable name="name" select="substring(name(),8)"/>
		<xsl:variable name="value" select="."/>
		<xsl:for-each select="../../../actions/*[name()=$name]">
			<td class="width1 actions1 tbody none">
				<xsl:choose>
					<xsl:when test="$value='true'">
						<a href="javascript:void(0)">
							<xsl:attribute name="onclick">
								<xsl:call-template name="replace_string">
									<xsl:with-param name="find">ID</xsl:with-param>
									<xsl:with-param name="replace" select="$id"/>
									<xsl:with-param name="string" select="onclick"/>
								</xsl:call-template>
							</xsl:attribute>
							<span class="{icon}" alt="{label}" title="{label}" labeled="{label}"></span>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<span class="{icon} ui-state-disabled" alt="{label}" title="{label}" labeled="{label}" disabled="true"></span>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:for-each>
	</xsl:for-each>
	<td class="width1 actions2 tbody"><a href="javascript:void(0)">
		<xsl:attribute name="title">
			<xsl:value-of select="../../actions2/label"/>
		</xsl:attribute>
		<span class="{../../actions2/icon}"></span>
	</a></td>
</xsl:template>

<xsl:template name="list_quick">
	<xsl:for-each select="quick">
		<table class="width100" cellpadding="0" cellspacing="0" border="0">
			<xsl:call-template name="form_by_rows">
				<xsl:with-param name="form" select="null"/>
				<xsl:with-param name="node" select="null"/>
				<xsl:with-param name="prefix" select="null"/>
				<xsl:with-param name="iter" select="row"/>
			</xsl:call-template>
		</table>
	</xsl:for-each>
</xsl:template>

<xsl:template name="list_pager">
	<xsl:for-each select="pager">
		<table class="width100" cellpadding="0" cellspacing="0" border="0">
			<xsl:call-template name="form_by_rows">
				<xsl:with-param name="form" select="null"/>
				<xsl:with-param name="node" select="null"/>
				<xsl:with-param name="prefix" select="null"/>
				<xsl:with-param name="iter" select="row"/>
			</xsl:call-template>
		</table>
	</xsl:for-each>
</xsl:template>

<xsl:template name="list_table">
	<table class="tabla" style="width:{width}" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<xsl:call-template name="list_table_head"/>
			</tr>
		</thead>
		<xsl:choose>
			<xsl:when test="count(rows/row)=0">
				<tr>
					<td colspan="{1+count(fields/field)+100}" class="nodata italic"><xsl:value-of select="nodata/label"/></td>
				</tr>
			</xsl:when>
			<xsl:otherwise>
				<xsl:for-each select="rows/row">
					<tr>
						<xsl:call-template name="list_table_data"/>
						<xsl:call-template name="list_table_actions"/>
					</tr>
				</xsl:for-each>
				<xsl:call-template name="math_row">
					<xsl:with-param name="iter" select="fields"/>
					<xsl:with-param name="checkbox">true</xsl:with-param>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</table>
</xsl:template>

<xsl:template name="tabs">
	<xsl:variable name="fields" select="fields"/>
	<xsl:variable name="rows" select="rows"/>
	<xsl:choose>
		<xsl:when test="count($fields/row)!=0">
			<xsl:for-each select="$fields[title!='']">
				<li taborder="{taborder}"><a href="#tab{generate-id(.)}"><xsl:value-of select="title"/></a></li>
			</xsl:for-each>
		</xsl:when>
		<xsl:otherwise>
			<xsl:for-each select="$fields/*">
				<xsl:variable name="name1" select="name()"/>
				<xsl:variable name="node1" select="."/>
				<xsl:for-each select="$rows/*[name()=$name1]">
					<xsl:variable name="name2" select="name()"/>
					<xsl:variable name="node2" select="."/>
					<xsl:choose>
						<xsl:when test="count($node2/*)=0"/>
						<xsl:when test="count($node2/*[name()=$name2])=0">
							<xsl:for-each select="$node2/row">
								<xsl:for-each select="$node1/fieldset[title!='']">
									<li taborder="{taborder}"><a href="#tab{generate-id(.)}"><xsl:value-of select="title"/></a></li>
								</xsl:for-each>
							</xsl:for-each>
						</xsl:when>
						<xsl:when test="count($node2/*[name()=$name2])=1">
							<xsl:for-each select="$node1/fieldset[title!='']">
								<li taborder="{taborder}"><a href="#tab{generate-id(.)}"><xsl:value-of select="title"/></a></li>
							</xsl:for-each>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
			</xsl:for-each>
		</xsl:otherwise>
	</xsl:choose>
	<li class="help" taborder=""><a href="javascript:void(0)"><span class="saltos-icon saltos-icon-none"></span></a></li>
</xsl:template>

<xsl:template name="brtag">
	<table cellpadding="0" cellspacing="0" border="0">
		<xsl:call-template name="brtag2"/>
	</table>
</xsl:template>

<xsl:template name="brtag2">
	<tr>
		<td class="separator"></td>
	</tr>
</xsl:template>

<xsl:template name="list">
	<xsl:if test="count(/root/list)!=0">
		<div class="tabs">
			<ul>
				<xsl:for-each select="/root/list">
					<xsl:if test="title!=''">
						<li taborder="{taborder}"><a href="#tab{generate-id(.)}"><xsl:value-of select="title"/></a></li>
					</xsl:if>
					<xsl:for-each select="form">
						<xsl:call-template name="tabs"/>
					</xsl:for-each>
				</xsl:for-each>
			</ul>
			<xsl:for-each select="/root/list">
				<xsl:call-template name="styles"/>
				<xsl:call-template name="javascript"/>
				<div>
					<xsl:choose>
						<xsl:when test="title!=''">
							<xsl:attribute name="class">sitabs</xsl:attribute>
							<xsl:attribute name="id">tab<xsl:value-of select="generate-id(.)"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="class">notabs</xsl:attribute>
							<xsl:call-template name="brtag"/>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:call-template name="list_quick"/>
					<xsl:call-template name="brtag"/>
					<xsl:call-template name="list_table"/>
					<xsl:call-template name="brtag"/>
					<xsl:call-template name="list_pager"/>
				</div>
				<xsl:for-each select="form">
					<xsl:call-template name="form_maker"/>
				</xsl:for-each>
			</xsl:for-each>
		</div>
	</xsl:if>
</xsl:template>

<xsl:template name="form_field">
	<xsl:param name="form"/>
	<xsl:param name="node"/>
	<xsl:param name="prefix"/>
	<xsl:variable name="name" select="name"/>
	<xsl:choose>
		<xsl:when test="type='hidden'">
			<input type="hidden" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" class="{class}">
				<xsl:for-each select="$node/*[name()=$name]">
					<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
				</xsl:for-each>
			</input>
		</xsl:when>
		<xsl:when test="type='text'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}" isautocomplete="{autocomplete}" querycomplete="{querycomplete}" filtercomplete="{filtercomplete}" oncomplete="{oncomplete}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:if test="speech='true'">
								<xsl:attribute name="x-webkit-speech">true</xsl:attribute>
								<xsl:attribute name="onwebkitspeechchange">this.value=ucfirst(this.value)</xsl:attribute>
							</xsl:if>
						</xsl:otherwise>
					</xsl:choose>
				</input>
				<xsl:if test="link!=''">
					<xsl:if test="readonly='true'">
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" islink="true" fnlink="{link}" forlink="{$prefix}{name}">
							<span class="{icon}" title="{tip2}"></span>
						</a>
					</xsl:if>
				</xsl:if>
			</td>
		</xsl:when>
		<xsl:when test="type='integer'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="isinteger">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
			</td>
		</xsl:when>
		<xsl:when test="type='float'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="isfloat">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
			</td>
		</xsl:when>
		<xsl:when test="type='color'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="iscolor">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
				<xsl:choose>
					<xsl:when test="readonly='true'"/>
					<xsl:otherwise>
						<a href="javascript:void(0)" id="{$prefix}{name}_color" class="ui-state-default ui-corner-all" iscolor="true">
							<xsl:for-each select="$node/*[name()=$name]">
								<xsl:attribute name="style">background:<xsl:value-of select="."/></xsl:attribute>
							</xsl:for-each>
							<xsl:choose>
								<xsl:when test="icon!=''">
									<span class="{icon}" title="{tip2}"></span>
								</xsl:when>
								<xsl:otherwise>
									<span class="saltos-icon saltos-icon-none" title="{tip2}"></span>
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:when>
		<xsl:when test="type='date'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="isdate">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
				<xsl:choose>
					<xsl:when test="readonly='true'"/>
					<xsl:otherwise>
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" isdate="true">
							<xsl:choose>
								<xsl:when test="icon!=''">
									<span class="{icon}" title="{tip2}"></span>
								</xsl:when>
								<xsl:otherwise>
									<span class="saltos-icon saltos-icon-none" title="{tip2}"></span>
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:when>
		<xsl:when test="type='time'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="istime">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
				<xsl:choose>
					<xsl:when test="readonly='true'"/>
					<xsl:otherwise>
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" istime="true">
							<xsl:choose>
								<xsl:when test="icon!=''">
									<span class="{icon}" title="{tip2}"></span>
								</xsl:when>
								<xsl:otherwise>
									<span class="saltos-icon saltos-icon-none" title="{tip2}"></span>
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:when>
		<xsl:when test="type='datetime'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="hidden" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}">
					<xsl:choose>
						<xsl:when test="readonly='true'"/>
						<xsl:otherwise>
							<xsl:attribute name="isdatetime">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
				</input>
				<xsl:variable name="width" select="concat(string(number(substring-before(width,'px'))*0.5),'px')"/>
				<input type="text" name="{$prefix}{name}_date" id="{$prefix}{name}_date" value="{substring-before(value,' ')}" style="width:{$width}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="substring-before(.,' ')"/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="isdate">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
				<xsl:choose>
					<xsl:when test="readonly='true'"/>
					<xsl:otherwise>
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" isdate="true">
							<xsl:choose>
								<xsl:when test="icon!=''">
									<span class="{icon}" title="{tip2}"></span>
								</xsl:when>
								<xsl:otherwise>
									<span class="saltos-icon saltos-icon-none" title="{tip2}"></span>
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</xsl:otherwise>
				</xsl:choose>
				<input type="text" name="{$prefix}{name}_time" id="{$prefix}{name}_time" value="{substring-after(value,' ')}" style="width:{$width}" onkeydown="{onkey}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="substring-after(.,' ')"/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="readonly='true'">
							<xsl:attribute name="readonly">true</xsl:attribute>
							<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="istime">true</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</input>
				<xsl:choose>
					<xsl:when test="readonly='true'"/>
					<xsl:otherwise>
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" istime="true">
							<xsl:choose>
								<xsl:when test="icon2!=''">
									<span class="{icon2}" title="{tip2}"></span>
								</xsl:when>
								<xsl:otherwise>
									<span class="saltos-icon saltos-icon-none" title="{tip2}"></span>
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:when>
		<xsl:when test="type='textarea'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<textarea name="{$prefix}{name}" id="{$prefix}{name}" style="width:{width};height:{height}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}" ckeditor="{ckeditor}" codemirror="{codemirror}" isautocomplete="{autocomplete}" querycomplete="{querycomplete}" filtercomplete="{filtercomplete}" oncomplete="{oncomplete}">
					<xsl:if test="readonly='true'">
						<xsl:attribute name="readonly">true</xsl:attribute>
						<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
					</xsl:if>
					<xsl:value-of select="value"/>
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:value-of select="."/>
					</xsl:for-each>
				</textarea>
			</td>
		</xsl:when>
		<xsl:when test="type='iframe'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<iframe src="" url="" name="{$prefix}{name}" id="{$prefix}{name}" style="width:{width};height:{height}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" frameborder="0" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:if test="readonly='true'">
						<xsl:attribute name="readonly">true</xsl:attribute>
						<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
					</xsl:if>
					<xsl:attribute name="url"><xsl:value-of select="value"/></xsl:attribute>
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="url"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
				</iframe>
			</td>
		</xsl:when>
		<xsl:when test="type='select'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<select name="{$prefix}{name}" id="{$prefix}{name}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}" original="{value}">
					<xsl:if test="readonly='true'">
						<xsl:attribute name="disabled">true</xsl:attribute>
						<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
					</xsl:if>
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="original"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:for-each select="rows/row">
						<option value="{value}">
							<xsl:if test="value=../../value">
								<xsl:attribute name="selected">true</xsl:attribute>
							</xsl:if>
							<xsl:variable name="value" select="value"/>
							<xsl:for-each select="$node/*[name()=$name][.=$value]">
								<xsl:attribute name="selected">true</xsl:attribute>
							</xsl:for-each>
							<xsl:value-of select="label"/>
						</option>
					</xsl:for-each>
				</select>
				<xsl:if test="link!=''">
					<xsl:if test="readonly='true'">
						<a href="javascript:void(0)" class="ui-state-default ui-corner-all" islink="true" fnlink="{link}" forlink="{$prefix}{name}">
							<span class="{icon}" title="{tip2}"></span>
						</a>
					</xsl:if>
				</xsl:if>
				<xsl:if test="readonly='true'">
					<input type="hidden" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" class="{class}">
						<xsl:for-each select="$node/*[name()=$name]">
							<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
						</xsl:for-each>
					</input>
				</xsl:if>
			</td>
		</xsl:when>
		<xsl:when test="type='checkbox'">
			<td class="right {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width}">
				<input type="{type}" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" labeled="{label}" title="{tip}" class="{class3}">
					<xsl:if test="checked='true'">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
					<xsl:variable name="value" select="value"/>
					<xsl:for-each select="$node/*[name()=$name][.=$value]">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:for-each>
					<xsl:if test="readonly='true'">
						<xsl:attribute name="disabled">true</xsl:attribute>
					</xsl:if>
				</input>
			</td>
			<xsl:if test="label!=''">
				<td class="left nowrap label {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width2}">
					<xsl:choose>
						<xsl:when test="icon!=''">
							<label for="{$prefix}{name}">
								<span class="{icon} {class3}" alt="{label}" title="{label}"></span>
							</label>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="readonly='true'">
									<span class="disabled"><xsl:value-of select="label"/></span>
								</xsl:when>
								<xsl:otherwise>
									<label for="{$prefix}{name}" title="{tip}"><xsl:value-of select="label"/></label>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</xsl:if>
		</xsl:when>
		<xsl:when test="type='button'">
			<td colspan="{colspan}" rowspan="{rowspan}" class="{class}" style="width:{width}">
				<a href="javascript:void(0)" onclick="{onclick}" focused="{focus}" labeled="{label}" style="width:{width2}" title="{tip}" class="ui-state-default ui-corner-all {class2}" id="{$prefix}{name}">
					<xsl:if test="disabled='true'">
						<xsl:attribute name="onclick"></xsl:attribute>
						<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class2"/></xsl:attribute>
					</xsl:if>
					<xsl:if test="icon!=''">
						<span class="{icon}"></span>
						<xsl:if test="value!=''"><xsl:text> </xsl:text></xsl:if>
					</xsl:if>
					<xsl:value-of select="value"/>
				</a>
			</td>
		</xsl:when>
		<xsl:when test="type='password'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<input type="{type}" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class3}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:if test="readonly='true'">
						<xsl:attribute name="readonly">true</xsl:attribute>
						<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class3"/></xsl:attribute>
					</xsl:if>
				</input>
			</td>
		</xsl:when>
		<xsl:when test="type='file'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}" nowrap="nowrap">
				<input type="{type}" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" style="width:{width}" size="{size}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="{class3}"/>
			</td>
		</xsl:when>
		<xsl:when test="type='link'">
			<xsl:if test="label2!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:value-of select="label2"/></td>
			</xsl:if>
			<td class="left nowrap {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width}">
				<xsl:variable name="tip">
					<xsl:choose>
						<xsl:when test="tip!=''">
							<xsl:value-of select="tip"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="label"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<a href="javascript:void(0)" onclick="{onclick}" focused="{focus}" labeled="{label}" title="{$tip}" class="{class3}" id="{$prefix}{name}">
					<xsl:for-each select="$node/*[name()=$name]">
						<xsl:attribute name="onclick">javascript:<xsl:value-of select="."/></xsl:attribute>
					</xsl:for-each>
					<xsl:choose>
						<xsl:when test="icon!=''">
							<span class="{icon} {class2}" alt="{label}" labeled="{label}" title="{$tip}"></span>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="label"/>
						</xsl:otherwise>
					</xsl:choose>
				</a>
			</td>
		</xsl:when>
		<xsl:when test="type='separator'">
			<td class="separator {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width};height:{height}" id="{$prefix}{name}"></td>
		</xsl:when>
		<xsl:when test="type='label'">
			<td class="left nowrap label {class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width};height:{height}" id="{$prefix}{name}">
				<xsl:for-each select="$node/*[name()=$name]">
					<xsl:choose>
						<xsl:when test="substring(.,1,4)='tel:'">
							<a class="tellink" href="javascript:void(0)" onclick="">
								<xsl:attribute name="onclick">qrcode2('<xsl:call-template name="replace_string">
									<xsl:with-param name="find">'</xsl:with-param>
									<xsl:with-param name="replace"> </xsl:with-param>
									<xsl:with-param name="string" select="."/>
								</xsl:call-template>')</xsl:attribute>
								<xsl:value-of select="substring(.,5)"/>
							</a>
						</xsl:when>
						<xsl:when test="substring(.,1,7)='mailto:'">
							<a class="maillink" href="javascript:void(0)" onclick="">
								<xsl:attribute name="onclick">mailto('<xsl:call-template name="replace_string">
									<xsl:with-param name="find">'</xsl:with-param>
									<xsl:with-param name="replace"> </xsl:with-param>
									<xsl:with-param name="string" select="substring(.,8)"/>
								</xsl:call-template>')</xsl:attribute>
								<xsl:value-of select="substring(.,8)"/>
							</a>
						</xsl:when>
						<xsl:when test="substring(.,1,5)='href:'">
							<a class="weblink" href="javascript:void(0)" onclick="">
								<xsl:attribute name="onclick">openwin('<xsl:call-template name="replace_string">
									<xsl:with-param name="find">'</xsl:with-param>
									<xsl:with-param name="replace"> </xsl:with-param>
									<xsl:with-param name="string" select="substring(.,6)"/>
								</xsl:call-template>')</xsl:attribute>
								<xsl:value-of select="substring(.,6)"/>
							</a>
						</xsl:when>
						<xsl:when test="substring(.,1,5)='link:'">
							<a class="applink" href="javascript:void(0)" onclick="">
								<xsl:attribute name="onclick"><xsl:call-template name="replace_string">
									<xsl:with-param name="find">%3A</xsl:with-param>
									<xsl:with-param name="replace">:</xsl:with-param>
									<xsl:with-param name="string" select="substring-before(substring(.,6),':')"/>
								</xsl:call-template></xsl:attribute>
								<xsl:value-of select="substring-after(substring(.,6),':')"/>
							</a>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="."/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:for-each>
				<xsl:choose>
					<xsl:when test="icon!=''">
						<span class="{icon} {class2}" alt="{label}" title="{$label}"></span>
					</xsl:when>
					<xsl:otherwise>
						<span class="{class2}" title="{tip}"><xsl:value-of select="label"/></span>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:when>
		<xsl:when test="type='image'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class3}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width};height:{height}">
				<img class="{class}" src="" title="{tip}" id="{$prefix}{name}">
					<xsl:choose>
						<xsl:when test="class!=''"/>
						<xsl:otherwise>
							<xsl:attribute name="class">ui-state-default ui-corner-all image</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:variable name="width" select="width"/>
					<xsl:variable name="height" select="height"/>
					<xsl:choose>
						<xsl:when test="phpthumb='false'">
							<xsl:attribute name="src"><xsl:value-of select="image"/></xsl:attribute>
							<xsl:for-each select="$node/*[name()=$name]">
								<xsl:attribute name="src"><xsl:value-of select="."/></xsl:attribute>
							</xsl:for-each>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="src">?action=phpthumb&amp;src=<xsl:value-of select="image"/>&amp;w=<xsl:value-of select="$width"/>&amp;h=<xsl:value-of select="$height"/></xsl:attribute>
							<xsl:for-each select="$node/*[name()=$name]">
								<xsl:attribute name="src">?action=phpthumb&amp;src=<xsl:value-of select="."/>&amp;w=<xsl:value-of select="$width"/>&amp;h=<xsl:value-of select="$height"/></xsl:attribute>
							</xsl:for-each>
						</xsl:otherwise>
					</xsl:choose>
				</img>
			</td>
		</xsl:when>
		<xsl:when test="type='plot'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="left {class3}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width};height:{height}">
				<map name="{generate-id(.)}" id="{generate-id(.)}"></map>
				<img style="width:{width};height:{height}" class="{class}" src="?action=phplot&amp;width={width}&amp;height={height}&amp;format=png&amp;loading=1" isplot="true" id="{$prefix}{name}" usemap="#{generate-id(.)}">
					<xsl:attribute name="title3"><xsl:value-of select="title"/></xsl:attribute>
					<xsl:attribute name="legend"><xsl:value-of select="legend"/></xsl:attribute>
					<xsl:attribute name="vars"><xsl:value-of select="vars"/></xsl:attribute>
					<xsl:attribute name="colors"><xsl:value-of select="colors"/></xsl:attribute>
					<xsl:attribute name="graph"><xsl:value-of select="graph"/></xsl:attribute>
					<xsl:attribute name="ticks"><xsl:for-each select="rows/row"><xsl:value-of select="y0"/>|</xsl:for-each></xsl:attribute>
					<xsl:if test="count(rows/row/x0)>0"><xsl:attribute name="posx"><xsl:for-each select="rows/row"><xsl:value-of select="x0"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:attribute name="data1"><xsl:for-each select="rows/row"><xsl:value-of select="y1"/>|</xsl:for-each></xsl:attribute>
					<xsl:if test="count(rows/row/y2)>0"><xsl:attribute name="data2"><xsl:for-each select="rows/row"><xsl:value-of select="y2"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y3)>0"><xsl:attribute name="data3"><xsl:for-each select="rows/row"><xsl:value-of select="y3"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y4)>0"><xsl:attribute name="data4"><xsl:for-each select="rows/row"><xsl:value-of select="y4"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y5)>0"><xsl:attribute name="data5"><xsl:for-each select="rows/row"><xsl:value-of select="y5"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y6)>0"><xsl:attribute name="data6"><xsl:for-each select="rows/row"><xsl:value-of select="y6"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y7)>0"><xsl:attribute name="data7"><xsl:for-each select="rows/row"><xsl:value-of select="y7"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y8)>0"><xsl:attribute name="data8"><xsl:for-each select="rows/row"><xsl:value-of select="y8"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y9)>0"><xsl:attribute name="data9"><xsl:for-each select="rows/row"><xsl:value-of select="y9"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y10)>0"><xsl:attribute name="data10"><xsl:for-each select="rows/row"><xsl:value-of select="y10"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y11)>0"><xsl:attribute name="data11"><xsl:for-each select="rows/row"><xsl:value-of select="y11"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y12)>0"><xsl:attribute name="data12"><xsl:for-each select="rows/row"><xsl:value-of select="y12"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y13)>0"><xsl:attribute name="data13"><xsl:for-each select="rows/row"><xsl:value-of select="y13"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y14)>0"><xsl:attribute name="data14"><xsl:for-each select="rows/row"><xsl:value-of select="y14"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y15)>0"><xsl:attribute name="data15"><xsl:for-each select="rows/row"><xsl:value-of select="y15"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:if test="count(rows/row/y16)>0"><xsl:attribute name="data16"><xsl:for-each select="rows/row"><xsl:value-of select="y16"/>|</xsl:for-each></xsl:attribute></xsl:if>
					<xsl:choose>
						<xsl:when test="class!=''"/>
						<xsl:otherwise>
							<xsl:attribute name="class">ui-state-default ui-corner-all image phplot</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</img>
			</td>
		</xsl:when>
		<xsl:when test="type='menu'">
			<td colspan="{colspan}" rowspan="{rowspan}" class="{class}" style="width:{width}">
				<select name="{$prefix}{name}" id="{$prefix}{name}" style="width:{width}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" title="{tip}" class="ui-state-default ui-corner-all {class2}" ismenu="true">
					<xsl:if test="readonly='true'">
						<xsl:attribute name="disabled">true</xsl:attribute>
						<xsl:attribute name="class">ui-state-default ui-corner-all ui-state-disabled <xsl:value-of select="class2"/></xsl:attribute>
					</xsl:if>
					<xsl:for-each select="*">
						<xsl:choose>
							<xsl:when test="name()='option'">
								<option value="{onclick}" class="{class}">
									<xsl:if test="disabled='true'">
										<xsl:attribute name="class">ui-state-disabled <xsl:value-of select="class"/></xsl:attribute>
									</xsl:if>
									<xsl:value-of select="label"/>
								</option>
							</xsl:when>
							<xsl:when test="name()='group'">
								<optgroup label="{label}" class="{class}">
									<xsl:for-each select="option">
										<option value="{onclick}" class="{class}">
											<xsl:if test="disabled='true'">
												<xsl:attribute name="class">ui-state-disabled <xsl:value-of select="class"/></xsl:attribute>
											</xsl:if>
											<xsl:value-of select="label"/>
										</option>
									</xsl:for-each>
								</optgroup>
							</xsl:when>
						</xsl:choose>
					</xsl:for-each>
				</select>
			</td>
		</xsl:when>
		<xsl:when test="type='grid'">
			<xsl:if test="label!=''">
				<td class="right nowrap label {class2}" colspan="{colspan2}" rowspan="{rowspan2}" style="width:{width2}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></td>
			</xsl:if>
			<td class="{class}" colspan="{colspan}" rowspan="{rowspan}" style="width:{width};height:{height}">
				<xsl:for-each select="rows">
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<xsl:call-template name="form_by_rows">
							<xsl:with-param name="form" select="$form"/>
							<xsl:with-param name="node" select="$node"/>
							<xsl:with-param name="prefix" select="$prefix"/>
							<xsl:with-param name="iter" select="row"/>
						</xsl:call-template>
					</table>
				</xsl:for-each>
			</td>
		</xsl:when>
	</xsl:choose>
</xsl:template>

<xsl:template name="form_by_rows">
	<xsl:param name="form"/>
	<xsl:param name="node"/>
	<xsl:param name="prefix"/>
	<xsl:param name="iter"/>
	<xsl:for-each select="$iter">
		<tr id="{id}" class="{class}" style="height:{height}">
			<xsl:for-each select="field">
				<xsl:call-template name="form_field">
					<xsl:with-param name="form" select="$form"/>
					<xsl:with-param name="node" select="$node"/>
					<xsl:with-param name="prefix" select="$prefix"/>
				</xsl:call-template>
			</xsl:for-each>
		</tr>
	</xsl:for-each>
</xsl:template>

<xsl:template name="math_row">
	<xsl:param name="iter"/>
	<xsl:param name="checkbox"/>
	<xsl:for-each select="$iter">
		<xsl:if test="count(field/math)>0">
			<tr>
				<td class="separator"></td>
			</tr>
			<tr>
				<xsl:if test="$checkbox='true'">
					<td class="thead"></td>
				</xsl:if>
				<xsl:for-each select="field[count(math/ignore)=0 or math/ignore!='true']">
					<td class="thead">
						<xsl:if test="math/label!=''"><xsl:value-of select="math/label"/></xsl:if>
					</td>
				</xsl:for-each>
				<td class="thead" colspan="100"></td>
			</tr>
			<tr class="math">
				<xsl:if test="$checkbox='true'">
					<td class="tbody"></td>
				</xsl:if>
				<xsl:for-each select="field[count(math/ignore)=0 or math/ignore!='true']">
					<td class="tbody">
						<xsl:if test="math/func!=''">=<xsl:value-of select="math/func"/></xsl:if>
					</td>
				</xsl:for-each>
				<td class="tbody" colspan="100"></td>
			</tr>
		</xsl:if>
	</xsl:for-each>
</xsl:template>

<xsl:template name="form_maker">
	<form name="{name}" id="{name}" action="{action}" method="{method}" onsubmit="return false">
		<!-- <xsl:if test="method='post'"><xsl:attribute name="enctype">multipart/form-data</xsl:attribute></xsl:if> -->
		<xsl:call-template name="form_maker_1"/>
		<xsl:call-template name="form_maker_2"/>
	</form>
</xsl:template>

<xsl:template name="form_maker_1">
	<xsl:for-each select="hiddens/field[type='hidden']">
		<input type="hidden" name="{name}" id="{name}" value="{value}"/>
	</xsl:for-each>
</xsl:template>

<xsl:template name="form_maker_2">
	<xsl:variable name="form" select="name"/>
	<xsl:variable name="fields" select="fields"/>
	<xsl:variable name="rows" select="rows"/>
	<xsl:choose>
		<xsl:when test="count($fields/row)!=0">
			<xsl:for-each select="$fields">
				<div>
					<xsl:choose>
						<xsl:when test="title!=''">
							<xsl:attribute name="class">sitabs</xsl:attribute>
							<xsl:attribute name="id">tab<xsl:value-of select="generate-id(.)"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="class">notabs</xsl:attribute>
							<xsl:call-template name="brtag"/>
						</xsl:otherwise>
					</xsl:choose>
					<table class="{class}" style="width:{width}" cellpadding="0" cellspacing="0" border="0">
						<xsl:if test="quick='true'">
							<xsl:call-template name="form_quick">
								<xsl:with-param name="quick" select="../quick"/>
								<xsl:with-param name="prefix" select="null"/>
							</xsl:call-template>
							<xsl:call-template name="brtag2"/>
						</xsl:if>
						<xsl:call-template name="form_by_rows">
							<xsl:with-param name="form" select="$form"/>
							<xsl:with-param name="node" select="null"/>
							<xsl:with-param name="prefix" select="null"/>
							<xsl:with-param name="iter" select="row"/>
						</xsl:call-template>
						<xsl:if test="buttons='true'">
							<xsl:call-template name="brtag2"/>
							<xsl:call-template name="form_buttons">
								<xsl:with-param name="buttons" select="../buttons"/>
								<xsl:with-param name="prefix" select="null"/>
							</xsl:call-template>
						</xsl:if>
					</table>
				</div>
			</xsl:for-each>
		</xsl:when>
		<xsl:otherwise>
			<xsl:for-each select="$fields/*">
				<xsl:variable name="name1" select="name()"/>
				<xsl:variable name="node1" select="."/>
				<xsl:for-each select="$rows/*[name()=$name1]">
					<xsl:variable name="name2" select="name()"/>
					<xsl:variable name="node2" select="."/>
					<xsl:choose>
						<xsl:when test="count($node2/*)=0"/>
						<xsl:when test="count($node2/*[name()=$name2])=0">
							<xsl:for-each select="$node2/row">
								<xsl:variable name="node3" select="."/>
								<xsl:variable name="prefix"><xsl:value-of select="$name1"/>_<xsl:value-of select="id"/>_</xsl:variable>
								<input type="hidden" name="prefix_{$prefix}" id="prefix_{$prefix}" value="{$prefix}"/>
								<xsl:for-each select="$node1/fieldset">
									<div>
										<xsl:choose>
											<xsl:when test="title!=''">
												<xsl:attribute name="class">sitabs</xsl:attribute>
												<xsl:attribute name="id">tab<xsl:value-of select="generate-id(.)"/></xsl:attribute>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="class">notabs</xsl:attribute>
												<xsl:call-template name="brtag"/>
											</xsl:otherwise>
										</xsl:choose>
										<table class="{class}" style="width:{width}" cellpadding="0" cellspacing="0" border="0">
											<xsl:if test="quick='true'">
												<xsl:call-template name="form_quick">
													<xsl:with-param name="quick" select="../../../quick"/>
													<xsl:with-param name="prefix" select="$prefix"/>
												</xsl:call-template>
												<xsl:call-template name="brtag2"/>
											</xsl:if>
											<xsl:call-template name="form_by_rows">
												<xsl:with-param name="form" select="$form"/>
												<xsl:with-param name="node" select="$node3"/>
												<xsl:with-param name="prefix" select="$prefix"/>
												<xsl:with-param name="iter" select="row"/>
											</xsl:call-template>
											<xsl:if test="buttons='true'">
												<xsl:call-template name="brtag2"/>
												<xsl:call-template name="form_buttons">
													<xsl:with-param name="buttons" select="../../../buttons"/>
													<xsl:with-param name="prefix" select="$prefix"/>
												</xsl:call-template>
											</xsl:if>
										</table>
									</div>
								</xsl:for-each>
							</xsl:for-each>
						</xsl:when>
						<xsl:when test="count($node2/*[name()=$name2])=1">
							<xsl:for-each select="$node1/fieldset">
								<div>
									<xsl:choose>
										<xsl:when test="title!=''">
											<xsl:attribute name="class">sitabs</xsl:attribute>
											<xsl:attribute name="id">tab<xsl:value-of select="generate-id(.)"/></xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<xsl:attribute name="class">notabs</xsl:attribute>
											<xsl:call-template name="brtag"/>
										</xsl:otherwise>
									</xsl:choose>
									<table class="tabla" style="width:{width}" cellpadding="0" cellspacing="0" border="0">
										<xsl:if test="quick='true'">
											<xsl:call-template name="form_quick">
												<xsl:with-param name="quick" select="../../../quick"/>
												<xsl:with-param name="prefix" select="null"/>
											</xsl:call-template>
											<xsl:call-template name="brtag2"/>
										</xsl:if>
										<xsl:call-template name="form_by_rows">
											<xsl:with-param name="form" select="$form"/>
											<xsl:with-param name="node" select="null"/>
											<xsl:with-param name="prefix" select="null"/>
											<xsl:with-param name="iter" select="head"/>
										</xsl:call-template>
										<xsl:variable name="node3" select="."/>
										<xsl:for-each select="$node2/*/row">
											<xsl:variable name="node4" select="."/>
											<xsl:variable name="prefix"><xsl:value-of select="$name1"/>_<xsl:value-of select="id"/>_</xsl:variable>
											<input type="hidden" name="prefix_{$prefix}" id="prefix_{$prefix}" value="{$prefix}"/>
											<xsl:for-each select="$node3">
												<xsl:call-template name="form_by_rows">
													<xsl:with-param name="form" select="$form"/>
													<xsl:with-param name="node" select="$node4"/>
													<xsl:with-param name="prefix" select="$prefix"/>
													<xsl:with-param name="iter" select="row"/>
												</xsl:call-template>
											</xsl:for-each>
										</xsl:for-each>
										<xsl:call-template name="math_row">
											<xsl:with-param name="iter" select="head"/>
											<xsl:with-param name="checkbox">false</xsl:with-param>
										</xsl:call-template>
										<xsl:call-template name="form_tail">
											<xsl:with-param name="form" select="$form"/>
											<xsl:with-param name="node" select="null"/>
											<xsl:with-param name="prefix" select="null"/>
											<xsl:with-param name="iter" select="tail"/>
										</xsl:call-template>
										<xsl:if test="buttons='true'">
											<xsl:call-template name="brtag2"/>
											<xsl:call-template name="form_buttons">
												<xsl:with-param name="buttons" select="../../../buttons"/>
												<xsl:with-param name="prefix" select="null"/>
											</xsl:call-template>
										</xsl:if>
									</table>
								</div>
							</xsl:for-each>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
			</xsl:for-each>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="form">
	<xsl:if test="count(/root/form)!=0">
		<div class="tabs">
			<ul>
				<xsl:for-each select="/root/form">
					<xsl:call-template name="tabs"/>
				</xsl:for-each>
			</ul>
			<xsl:for-each select="/root/form">
				<xsl:call-template name="styles"/>
				<xsl:call-template name="javascript"/>
				<xsl:call-template name="form_maker"/>
			</xsl:for-each>
		</div>
	</xsl:if>
</xsl:template>

<xsl:template name="form_quick">
	<xsl:param name="quick"/>
	<xsl:param name="prefix"/>
	<xsl:for-each select="$quick">
		<tr>
			<td colspan="100">
				<table class="width100" cellpadding="0" cellspacing="0" border="0">
					<xsl:call-template name="form_by_rows">
						<xsl:with-param name="form" select="null"/>
						<xsl:with-param name="node" select="null"/>
						<xsl:with-param name="prefix" select="prefix"/>
						<xsl:with-param name="iter" select="row"/>
					</xsl:call-template>
				</table>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>

<xsl:template name="form_tail">
	<xsl:param name="form"/>
	<xsl:param name="node"/>
	<xsl:param name="prefix"/>
	<xsl:param name="iter"/>
	<tr>
		<td colspan="100">
			<table class="width100" cellpadding="0" cellspacing="0" border="0">
				<xsl:call-template name="form_by_rows">
					<xsl:with-param name="form" select="$form"/>
					<xsl:with-param name="node" select="$node"/>
					<xsl:with-param name="prefix" select="$prefix"/>
					<xsl:with-param name="iter" select="$iter"/>
				</xsl:call-template>
			</table>
		</td>
	</tr>
</xsl:template>

<xsl:template name="form_buttons">
	<xsl:param name="buttons"/>
	<xsl:param name="prefix"/>
	<xsl:for-each select="$buttons">
		<tr>
			<td colspan="100">
				<table class="width100" cellpadding="0" cellspacing="0" border="0">
					<xsl:call-template name="form_by_rows">
						<xsl:with-param name="form" select="null"/>
						<xsl:with-param name="node" select="null"/>
						<xsl:with-param name="prefix" select="$prefix"/>
						<xsl:with-param name="iter" select="row"/>
					</xsl:call-template>
				</table>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>

<xsl:template match="/">
	<html xmlns="http://www.w3.org/1999/xhtml" lang="{/root/info/lang}" dir="{/root/info/dir}">
		<head>
			<xsl:call-template name="head"/>
		</head>
		<body>
			<table class="width100 none {/root/info/dir}" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td valign="top" colspan="2">
						<div class="ui-layout-north">
							<xsl:call-template name="title"/>
						</div>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<div class="ui-layout-west">
							<xsl:call-template name="menu"/>
							<a href="#" class="back2top ui-widget-header ui-corner-all">
								<span class="fa fa-arrow-circle-up"></span>
							</a>
						</div>
					</td>
					<td valign="top" class="width100">
						<div class="ui-layout-center">
							<xsl:call-template name="list"/>
							<xsl:call-template name="form"/>
							<xsl:call-template name="alert"/>
							<xsl:call-template name="error"/>
						</div>
					</td>
				</tr>
			</table>
		</body>
	</html>
</xsl:template>

</xsl:stylesheet>
