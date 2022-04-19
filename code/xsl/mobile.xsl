<?xml version="1.0" encoding="UTF-8" ?>
<!--
 ____        _ _    ___  ____
/ ___|  __ _| | |_ / _ \/ ___|
\___ \ / _` | | __| | | \___ \
 ___) | (_| | | |_| |_| |___) |
|____/ \__,_|_|\__|\___/|____/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2022 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" encoding="UTF-8" indent="no"/>

<xsl:template name="head">
    <xsl:for-each select="/root">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta name="theme-color" content="{info/color}"/>
        <link href="{info/favicon}" rel="icon"></link>
        <link href="{info/favicon}" rel="shortcut icon"></link>
        <link href="{info/favicon}" rel="apple-touch-icon"/>
        <link href="{info/favicon}" rel="apple-touch-icon-precomposed"/>
        <title><xsl:call-template name="title_2"/></title>
        <xsl:call-template name="styles"/>
        <xsl:call-template name="javascript"/>
    </xsl:for-each>
</xsl:template>

<xsl:template name="title">
    <xsl:for-each select="/root">
        <div data-role="header">
            <h3><xsl:call-template name="title_2"/></h3>
        </div>
    </xsl:for-each>
</xsl:template>

<xsl:template name="title_2">
    <xsl:value-of select="info/title"/>
    <xsl:text> - </xsl:text>
    <xsl:value-of select="info/name"/>
    <xsl:text> v</xsl:text>
    <xsl:value-of select="info/version"/>
    <xsl:text> r</xsl:text>
    <xsl:value-of select="info/revision"/>
</xsl:template>

<xsl:template name="javascript">
    <xsl:for-each select="javascript/*">
        <xsl:choose>
            <xsl:when test="name()='function'">
                <script type="text/javascript">function <xsl:value-of select="."/></script>
            </xsl:when>
            <xsl:when test="name()='include'">
                <script type="text/javascript" src="{.}?r={/root/info/revision}"></script>
            </xsl:when>
            <xsl:when test="name()='inline'">
                <script type="text/javascript"><xsl:value-of select="."/></script>
            </xsl:when>
            <xsl:when test="name()='cache'">
                <xsl:if test="count(include)>0">
                    <script type="text/javascript" src="">
                        <xsl:attribute name="src">?action=cache&amp;files=<xsl:for-each select="include"><xsl:value-of select="."/><xsl:if test="not(position()=last())">,</xsl:if></xsl:for-each>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
                    </script>
                </xsl:if>
            </xsl:when>
        </xsl:choose>
    </xsl:for-each>
</xsl:template>

<xsl:template name="styles">
    <xsl:for-each select="styles/*">
        <xsl:choose>
            <xsl:when test="name()='include'">
                <link href="{.}?r={/root/info/revision}" rel="stylesheet" type="text/css"></link>
            </xsl:when>
            <xsl:when test="name()='inline'">
                <style type="text/css"><xsl:value-of select="."/></style>
            </xsl:when>
            <xsl:when test="name()='cache'">
                <xsl:if test="count(include)>0">
                    <link href="" rel="stylesheet" type="text/css">
                        <xsl:attribute name="href">?action=cache&amp;files=<xsl:for-each select="include"><xsl:value-of select="."/><xsl:if test="not(position()=last())">,</xsl:if></xsl:for-each>&amp;r=<xsl:value-of select="/root/info/revision"/></xsl:attribute>
                    </link>
                </xsl:if>
            </xsl:when>
        </xsl:choose>
    </xsl:for-each>
</xsl:template>

<xsl:template name="menu">
    <xsl:for-each select="/root/menu">
        <ul data-role="listview">
            <li><select class="menu" ismenu="true" data-mini="true">
                <xsl:for-each select="group">
                    <optgroup label="{label}">
                        <xsl:for-each select="option">
                            <option value="{onclick}">
                                <xsl:if test="selected='true'">
                                    <xsl:attribute name="selected">true</xsl:attribute>
                                </xsl:if>
                                <xsl:value-of select="label"/>
                            </option>
                        </xsl:for-each>
                    </optgroup>
                </xsl:for-each>
            </select></li>
        </ul>
    </xsl:for-each>
</xsl:template>

<xsl:template name="alert">
    <xsl:for-each select="/root/alerts/alert">
        <script type="text/javascript">$(function() { if(typeof(parent.notice)=="function") parent.notice(lang_alert(),"<xsl:value-of select="."/>");else if(typeof(notice)=="function") notice(lang_alert(),"<xsl:value-of select="."/>",false,""); });</script>
    </xsl:for-each>
</xsl:template>

<xsl:template name="error">
    <xsl:for-each select="/root/errors/error">
        <script type="text/javascript">$(function() { if(typeof(parent.notice)=="function") parent.notice(lang_error(),"<xsl:value-of select="."/>");else if(typeof(notice)=="function") notice(lang_error(),"<xsl:value-of select="."/>",false,""); });</script>
    </xsl:for-each>
</xsl:template>

<xsl:template name="print_string_length">
    <xsl:param name="text"/>
    <xsl:param name="size"/>
    <xsl:choose>
        <xsl:when test="$size!=''">
            <xsl:choose>
                <xsl:when test="string-length($text)>$size">
                    <xsl:value-of select="substring($text,1,$size)"/><xsl:text>...</xsl:text>
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
        <xsl:when test="contains($string,$find)">
            <xsl:value-of select="substring-before($string,$find)"/>
            <xsl:value-of select="$replace"/>
            <xsl:call-template name="replace_string">
                <xsl:with-param name="find" select="$find"/>
                <xsl:with-param name="replace" select="$replace"/>
                <xsl:with-param name="string" select="substring-after($string,$find)"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$string"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="list_quick">
    <xsl:for-each select="quick">
        <ul data-role="listview"><li>
            <xsl:call-template name="form_by_rows">
                <xsl:with-param name="form" select="null"/>
                <xsl:with-param name="node" select="null"/>
                <xsl:with-param name="prefix" select="null"/>
                <xsl:with-param name="iter" select="row"/>
            </xsl:call-template>
        </li></ul>
    </xsl:for-each>
</xsl:template>

<xsl:template name="list_pager">
    <xsl:for-each select="pager">
        <ul data-role="listview"><li>
            <xsl:call-template name="form_by_rows">
                <xsl:with-param name="form" select="null"/>
                <xsl:with-param name="node" select="null"/>
                <xsl:with-param name="prefix" select="null"/>
                <xsl:with-param name="iter" select="row"/>
            </xsl:call-template>
        </li></ul>
    </xsl:for-each>
</xsl:template>

<xsl:template name="list">
    <xsl:for-each select="/root/list">
        <xsl:call-template name="styles"/>
        <xsl:call-template name="javascript"/>
        <div data-role="header">
            <h3><xsl:value-of select="title"/></h3>
        </div>
        <xsl:call-template name="list_quick"/>
        <xsl:choose>
            <xsl:when test="count(rows/row)=0">
                <ul data-role="listview">
                    <li><xsl:value-of select="nodata/label"/></li>
                </ul>
            </xsl:when>
            <xsl:otherwise>
                <ul data-role="listview">
                    <li>
                        <div class="ui-checkbox ui-mini">
                            <label for="master" class="ui-btn ui-btn-inherit ui-btn-icon-left ui-checkbox-off ui-corner-all"></label>
                            <input type="checkbox" class="master" name="master" id="master" value="1" data-enhanced="true"/>
                        </div>
                    </li>
                    <xsl:for-each select="rows/row">
                        <li>
                            <div class="ui-checkbox ui-mini">
                                <label for="slave_{id}" class="ui-btn ui-btn-inherit ui-btn-icon-left ui-checkbox-off ui-corner-all"></label>
                                <input type="checkbox" class="slave id_{id}" name="slave_{id}" id="slave_{id}" value="1" data-enhanced="true"/>
                            </div>
                            <xsl:variable name="id" select="action_id"/>
                            <xsl:variable name="style" select="action_style"/>
                            <xsl:variable name="row" select="*"/>
                            <xsl:for-each select="../../fields/field">
                                <xsl:variable name="name" select="name"/>
                                <xsl:variable name="size" select="size"/>
                                <xsl:variable name="class" select="class"/>
                                <xsl:variable name="label" select="label"/>
                                <xsl:for-each select="$row[name()=$name]">
                                    <div>
                                        <xsl:choose>
                                            <xsl:when test="node()=''">
                                                <xsl:value-of select="$label"/>:
                                                <xsl:text>-</xsl:text>
                                            </xsl:when>
                                            <xsl:when test="substring(node(),1,4)='tel:'">
                                                <xsl:value-of select="$label"/>:
                                                <a class="tellink" href="javascript:void(0)" onclick="">
                                                    <xsl:attribute name="onclick">qrcode2('<xsl:call-template name="replace_string">
                                                        <xsl:with-param name="find">'</xsl:with-param>
                                                        <xsl:with-param name="replace"> </xsl:with-param>
                                                        <xsl:with-param name="string" select="node()"/>
                                                    </xsl:call-template>')</xsl:attribute>
                                                    <xsl:call-template name="print_string_length">
                                                        <xsl:with-param name="text" select="substring(node(),5)"/>
                                                        <xsl:with-param name="size" select="$size"/>
                                                    </xsl:call-template>
                                                </a>
                                            </xsl:when>
                                            <xsl:when test="substring(node(),1,4)='fax:'">
                                                <xsl:value-of select="$label"/>:
                                                <a class="faxlink" href="javascript:void(0)" onclick="">
                                                    <xsl:attribute name="onclick">qrcode2('<xsl:call-template name="replace_string">
                                                        <xsl:with-param name="find">'</xsl:with-param>
                                                        <xsl:with-param name="replace"> </xsl:with-param>
                                                        <xsl:with-param name="string" select="node()"/>
                                                    </xsl:call-template>')</xsl:attribute>
                                                    <xsl:call-template name="print_string_length">
                                                        <xsl:with-param name="text" select="substring(node(),5)"/>
                                                        <xsl:with-param name="size" select="$size"/>
                                                    </xsl:call-template>
                                                </a>
                                            </xsl:when>
                                            <xsl:when test="substring(node(),1,7)='mailto:'">
                                                <xsl:value-of select="$label"/>:
                                                <a class="maillink" href="javascript:void(0)" onclick="">
                                                    <xsl:attribute name="onclick">mailto('<xsl:call-template name="replace_string">
                                                        <xsl:with-param name="find">'</xsl:with-param>
                                                        <xsl:with-param name="replace"> </xsl:with-param>
                                                        <xsl:with-param name="string" select="substring(node(),8)"/>
                                                    </xsl:call-template>')</xsl:attribute>
                                                    <xsl:call-template name="print_string_length">
                                                        <xsl:with-param name="text" select="substring(node(),8)"/>
                                                        <xsl:with-param name="size" select="$size"/>
                                                    </xsl:call-template>
                                                </a>
                                            </xsl:when>
                                            <xsl:when test="substring(node(),1,5)='href:'">
                                                <xsl:value-of select="$label"/>:
                                                <a class="weblink" href="javascript:void(0)" onclick="">
                                                    <xsl:attribute name="onclick">openwin('<xsl:call-template name="replace_string">
                                                        <xsl:with-param name="find">'</xsl:with-param>
                                                        <xsl:with-param name="replace"> </xsl:with-param>
                                                        <xsl:with-param name="string" select="substring(node(),6)"/>
                                                    </xsl:call-template>')</xsl:attribute>
                                                    <xsl:call-template name="print_string_length">
                                                        <xsl:with-param name="text" select="substring(node(),6)"/>
                                                        <xsl:with-param name="size" select="$size"/>
                                                    </xsl:call-template>
                                                </a>
                                            </xsl:when>
                                            <xsl:when test="substring(node(),1,5)='link:'">
                                                <xsl:value-of select="$label"/>:
                                                <a class="applink" href="javascript:void(0)" onclick="">
                                                    <xsl:attribute name="onclick"><xsl:call-template name="replace_string">
                                                        <xsl:with-param name="find">%3A</xsl:with-param>
                                                        <xsl:with-param name="replace">:</xsl:with-param>
                                                        <xsl:with-param name="string" select="substring-before(substring(node(),6),':')"/>
                                                    </xsl:call-template></xsl:attribute>
                                                    <xsl:call-template name="print_string_length">
                                                        <xsl:with-param name="text" select="substring-after(substring(node(),6),':')"/>
                                                        <xsl:with-param name="size" select="$size"/>
                                                    </xsl:call-template>
                                                </a>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <xsl:value-of select="$label"/>:
                                                <xsl:call-template name="print_string_length">
                                                    <xsl:with-param name="text" select="node()"/>
                                                    <xsl:with-param name="size" select="$size"/>
                                                </xsl:call-template>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </div>
                                </xsl:for-each>
                            </xsl:for-each>
                            <select class="actions" ismenu="true" data-mini="true">
                                <option></option>
                                <xsl:for-each select="*[substring(name(),1,7)='action_']">
                                    <xsl:variable name="name" select="substring(name(),8)"/>
                                    <xsl:variable name="value" select="."/>
                                    <xsl:for-each select="../../../actions/*[name()=$name][$value='true']">
                                        <option>
                                            <xsl:attribute name="value">
                                                <xsl:call-template name="replace_string">
                                                    <xsl:with-param name="find">ID</xsl:with-param>
                                                    <xsl:with-param name="replace" select="$id"/>
                                                    <xsl:with-param name="string" select="onclick"/>
                                                </xsl:call-template>
                                            </xsl:attribute>
                                            <xsl:value-of select="label"/>
                                        </option>
                                    </xsl:for-each>
                                </xsl:for-each>
                            </select>
                        </li>
                    </xsl:for-each>
                </ul>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:call-template name="list_pager"/>
        <xsl:for-each select="form">
            <xsl:call-template name="form_maker"/>
        </xsl:for-each>
    </xsl:for-each>
</xsl:template>

<xsl:template name="form_field">
    <xsl:param name="form"/>
    <xsl:param name="node"/>
    <xsl:param name="prefix"/>
    <xsl:variable name="name" select="name"/>
    <xsl:choose>
        <xsl:when test="type='hidden'">
            <input type="hidden" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}">
                <xsl:for-each select="$node/*[name()=$name]">
                    <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                </xsl:for-each>
            </input>
        </xsl:when>
        <xsl:when test="type='text'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:if test="speech='true'">
                                <xsl:attribute name="x-webkit-speech">true</xsl:attribute>
                                <xsl:attribute name="onwebkitspeechchange">this.value=ucfirst(this.value)</xsl:attribute>
                            </xsl:if>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='integer'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="isinteger">true</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='float'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="isfloat">true</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='color'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="iscolor">true</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='date'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="isdate">true</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='time'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="istime">true</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='datetime'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="text" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:choose>
                        <xsl:when test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                            <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:attribute name="isdatetime">true</xsl:attribute>
                        </xsl:otherwise>
                    </xsl:choose>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='textarea'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <textarea name="{$prefix}{name}" id="{$prefix}{name}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="textarea {class3}" data-mini="true" ckeditor="{ckeditor}" codemirror="{codemirror}">
                    <xsl:if test="readonly='true'">
                        <xsl:attribute name="readonly">true</xsl:attribute>
                        <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="value"/>
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:value-of select="."/>
                    </xsl:for-each>
                </textarea>
            </div>
        </xsl:when>
        <xsl:when test="type='iframe'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:value-of select="label"/></label>
                </xsl:if>
                <div class="preiframe" data-mini="true">
                    <xsl:if test="readonly='true'">
                        <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                    </xsl:if>
                    <iframe src="" url="" name="{$prefix}{name}" id="{$prefix}{name}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" frameborder="0" class="{class3}">
                        <xsl:if test="readonly='true'">
                            <xsl:attribute name="readonly">true</xsl:attribute>
                        </xsl:if>
                        <xsl:attribute name="url"><xsl:value-of select="value"/></xsl:attribute>
                        <xsl:for-each select="$node/*[name()=$name]">
                            <xsl:attribute name="url"><xsl:value-of select="."/></xsl:attribute>
                        </xsl:for-each>
                    </iframe>
                </div>
            </div>
        </xsl:when>
        <xsl:when test="type='select'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <select name="{$prefix}{name}" id="{$prefix}{name}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" original="{value}" data-mini="true">
                    <xsl:if test="readonly='true'">
                        <xsl:attribute name="disabled">true</xsl:attribute>
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
            </div>
        </xsl:when>
        <xsl:when test="type='checkbox'">
            <div class="ui-checkbox ui-mini">
                <xsl:if test="readonly='true'">
                    <xsl:attribute name="class">ui-checkbox ui-mini ui-state-disabled</xsl:attribute>
                </xsl:if>
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}" class="ui-btn ui-btn-inherit ui-btn-icon-left ui-checkbox-off ui-corner-all">
                        <xsl:if test="checked='true'">
                            <xsl:attribute name="class">ui-btn ui-btn-inherit ui-btn-icon-left ui-checkbox-on ui-corner-all</xsl:attribute>
                        </xsl:if>
                        <xsl:variable name="value" select="value"/>
                        <xsl:for-each select="$node/*[name()=$name][.=$value]">
                            <xsl:attribute name="class">ui-btn ui-btn-inherit ui-btn-icon-left ui-checkbox-on ui-corner-all</xsl:attribute>
                        </xsl:for-each>
                        <xsl:value-of select="label"/>
                    </label>
                </xsl:if>
                <input type="{type}" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" labeled="{label}{label2}" data-enhanced="true">
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
            </div>
        </xsl:when>
        <xsl:when test="type='button'">
            <span>
                <a href="javascript:void(0)" onclick="{onclick}" focused="{focus}" labeled="{label}" class="{class2}" id="{$prefix}{name}" data-role="button" data-mini="true" data-inline="true">
                    <xsl:if test="disabled='true'">
                        <xsl:attribute name="onclick"></xsl:attribute>
                        <xsl:attribute name="class">ui-disabled <xsl:value-of select="class2"/></xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="value"/>
                </a>
            </span>
        </xsl:when>
        <xsl:when test="type='password'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:if test="required='true'"><xsl:text>(*) </xsl:text></xsl:if><xsl:value-of select="label"/></label>
                </xsl:if>
                <input type="{type}" name="{$prefix}{name}" id="{$prefix}{name}" value="{value}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}{label2}" class="{class3}" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="value"><xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:if test="readonly='true'">
                        <xsl:attribute name="readonly">true</xsl:attribute>
                        <xsl:attribute name="class">ui-disabled <xsl:value-of select="class3"/></xsl:attribute>
                    </xsl:if>
                </input>
            </div>
        </xsl:when>
        <xsl:when test="type='file'">
            <!-- NOTHING TO DO -->
        </xsl:when>
        <xsl:when test="type='link'">
            <div class="ui-field-contain">
                <xsl:if test="label2!=''">
                    <label for="{$prefix}{name}"><xsl:value-of select="label2"/></label>
                </xsl:if>
                <a href="javascript:void(0)" onclick="{onclick}" focused="{focus}" labeled="{label}" id="{$prefix}{name}" data-role="button" data-mini="true">
                    <xsl:for-each select="$node/*[name()=$name]">
                        <xsl:attribute name="onclick">javascript:<xsl:value-of select="."/></xsl:attribute>
                    </xsl:for-each>
                    <xsl:value-of select="label"/>
                </a>
            </div>
        </xsl:when>
        <xsl:when test="type='separator'">
            <!-- NOTHING TO DO -->
        </xsl:when>
        <xsl:when test="type='label'">
            <div class="ui-field-contain">
                <label id="{$prefix}{name}">
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
                            <xsl:when test="substring(.,1,4)='fax:'">
                                <a class="faxlink" href="javascript:void(0)" onclick="">
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
                    <span class="{class2}"><xsl:value-of select="label"/></span>
                </label>
            </div>
        </xsl:when>
        <xsl:when test="type='image'">
            <div class="ui-field-contain">
                <xsl:if test="label!=''">
                    <label for="{$prefix}{name}"><xsl:value-of select="label"/></label>
                </xsl:if>
                <img class="{class}" src="" id="{$prefix}{name}">
                    <xsl:choose>
                        <xsl:when test="class!=''"/>
                        <xsl:otherwise>
                            <xsl:attribute name="class">image</xsl:attribute>
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
            </div>
        </xsl:when>
        <xsl:when test="type='menu'">
            <div class="ui-field-contain">
                <select name="{$prefix}{name}" id="{$prefix}{name}" onchange="{onchange}" onkeydown="{onkey}" focused="{focus}" isrequired="{required}" labeled="{label}" class="{class2}" ismenu="true" data-mini="true">
                    <xsl:if test="readonly='true'">
                        <xsl:attribute name="disabled">true</xsl:attribute>
                    </xsl:if>
                    <xsl:for-each select="*">
                        <xsl:choose>
                            <xsl:when test="name()='option'">
                                <option value="{onclick}" class="{class}">
                                    <xsl:if test="disabled='true'">
                                        <xsl:attribute name="disabled">true</xsl:attribute>
                                    </xsl:if>
                                    <xsl:value-of select="label"/>
                                </option>
                            </xsl:when>
                            <xsl:when test="name()='group'">
                                <optgroup label="{label}" class="{class}">
                                    <xsl:for-each select="option">
                                        <option value="{onclick}" class="{class}">
                                            <xsl:if test="disabled='true'">
                                                <xsl:attribute name="disabled">true</xsl:attribute>
                                            </xsl:if>
                                            <xsl:value-of select="label"/>
                                        </option>
                                    </xsl:for-each>
                                </optgroup>
                            </xsl:when>
                        </xsl:choose>
                    </xsl:for-each>
                </select>
            </div>
        </xsl:when>
        <xsl:when test="type='grid'">
            <xsl:for-each select="rows">
                <xsl:call-template name="form_by_rows">
                    <xsl:with-param name="form" select="null"/>
                    <xsl:with-param name="node" select="null"/>
                    <xsl:with-param name="prefix" select="null"/>
                    <xsl:with-param name="iter" select="row"/>
                </xsl:call-template>
            </xsl:for-each>
        </xsl:when>
    </xsl:choose>
</xsl:template>

<xsl:template name="form_by_rows">
    <xsl:param name="form"/>
    <xsl:param name="node"/>
    <xsl:param name="prefix"/>
    <xsl:param name="iter"/>
    <xsl:for-each select="$iter">
        <xsl:for-each select="field">
            <xsl:call-template name="form_field">
                <xsl:with-param name="form" select="$form"/>
                <xsl:with-param name="node" select="$node"/>
                <xsl:with-param name="prefix" select="$prefix"/>
            </xsl:call-template>
        </xsl:for-each>
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
        <input type="hidden" name="{name}" value="{value}"/>
    </xsl:for-each>
</xsl:template>

<xsl:template name="form_maker_2">
    <xsl:variable name="form" select="name"/>
    <xsl:variable name="fields" select="fields"/>
    <xsl:variable name="rows" select="rows"/>
    <xsl:choose>
        <xsl:when test="count($fields/row)!=0">
            <xsl:for-each select="$fields">
                <xsl:if test="title!=''">
                    <div data-role="header">
                        <h3><xsl:value-of select="title"/></h3>
                    </div>
                </xsl:if>
                <ul data-role="listview"><li>
                    <xsl:if test="quick='true'">
                        <xsl:call-template name="form_quick">
                            <xsl:with-param name="quick" select="../quick"/>
                            <xsl:with-param name="prefix" select="null"/>
                        </xsl:call-template>
                    </xsl:if>
                    <xsl:call-template name="form_by_rows">
                        <xsl:with-param name="form" select="$form"/>
                        <xsl:with-param name="node" select="null"/>
                        <xsl:with-param name="prefix" select="null"/>
                        <xsl:with-param name="iter" select="row"/>
                    </xsl:call-template>
                    <xsl:if test="buttons='true'">
                        <xsl:call-template name="form_buttons">
                            <xsl:with-param name="buttons" select="../buttons"/>
                            <xsl:with-param name="prefix" select="null"/>
                        </xsl:call-template>
                    </xsl:if>
                </li></ul>
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
                                <input type="hidden" name="prefix_{$prefix}" value="{$prefix}"/>
                                <xsl:for-each select="$node1/fieldset">
                                    <xsl:if test="title!=''">
                                        <div data-role="header">
                                            <h3><xsl:value-of select="title"/></h3>
                                        </div>
                                    </xsl:if>
                                    <xsl:if test="quick='true'">
                                        <ul data-role="listview"><li>
                                            <xsl:call-template name="form_quick">
                                                <xsl:with-param name="quick" select="../../../quick"/>
                                                <xsl:with-param name="prefix" select="$prefix"/>
                                            </xsl:call-template>
                                        </li></ul>
                                    </xsl:if>
                                    <ul data-role="listview"><li>
                                        <xsl:call-template name="form_by_rows">
                                            <xsl:with-param name="form" select="$form"/>
                                            <xsl:with-param name="node" select="$node3"/>
                                            <xsl:with-param name="prefix" select="$prefix"/>
                                            <xsl:with-param name="iter" select="row"/>
                                        </xsl:call-template>
                                    </li></ul>
                                    <xsl:if test="buttons='true'">
                                        <ul data-role="listview"><li>
                                            <xsl:call-template name="form_buttons">
                                                <xsl:with-param name="buttons" select="../../../buttons"/>
                                                <xsl:with-param name="prefix" select="$prefix"/>
                                            </xsl:call-template>
                                        </li></ul>
                                    </xsl:if>
                                </xsl:for-each>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:when test="count($node2/*[name()=$name2])=1">
                            <xsl:for-each select="$node1/fieldset">
                                <xsl:if test="title!=''">
                                    <div data-role="header">
                                        <h3><xsl:value-of select="title"/></h3>
                                    </div>
                                </xsl:if>
                                <xsl:if test="quick='true'">
                                    <ul data-role="listview"><li>
                                        <xsl:call-template name="form_quick">
                                            <xsl:with-param name="quick" select="../../../quick"/>
                                            <xsl:with-param name="prefix" select="null"/>
                                        </xsl:call-template>
                                    </li></ul>
                                </xsl:if>
                                <xsl:variable name="node3" select="."/>
                                <xsl:for-each select="$node2/*/row">
                                    <xsl:variable name="node4" select="."/>
                                    <xsl:variable name="prefix"><xsl:value-of select="$name1"/>_<xsl:value-of select="id"/>_</xsl:variable>
                                    <input type="hidden" name="prefix_{$prefix}" value="{$prefix}"/>
                                    <xsl:for-each select="$node3">
                                        <ul data-role="listview"><li>
                                            <div class="todofixfull">
                                            </div>
                                            <div class="todofixhead">
                                                <xsl:call-template name="form_by_rows">
                                                    <xsl:with-param name="form" select="$form"/>
                                                    <xsl:with-param name="node" select="null"/>
                                                    <xsl:with-param name="prefix" select="null"/>
                                                    <xsl:with-param name="iter" select="head"/>
                                                </xsl:call-template>
                                            </div>
                                            <div class="todofixbody">
                                                <xsl:call-template name="form_by_rows">
                                                    <xsl:with-param name="form" select="$form"/>
                                                    <xsl:with-param name="node" select="$node4"/>
                                                    <xsl:with-param name="prefix" select="$prefix"/>
                                                    <xsl:with-param name="iter" select="row"/>
                                                </xsl:call-template>
                                            </div>
                                        </li></ul>
                                    </xsl:for-each>
                                </xsl:for-each>
                                <xsl:if test="buttons='true'">
                                    <ul data-role="listview"><li>
                                        <xsl:call-template name="form_buttons">
                                            <xsl:with-param name="buttons" select="../../../buttons"/>
                                            <xsl:with-param name="prefix" select="null"/>
                                        </xsl:call-template>
                                    </li></ul>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                    </xsl:choose>
                </xsl:for-each>
            </xsl:for-each>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="form">
    <xsl:for-each select="/root/form">
        <xsl:call-template name="styles"/>
        <xsl:call-template name="javascript"/>
        <xsl:call-template name="form_maker"/>
    </xsl:for-each>
</xsl:template>

<xsl:template name="form_buttons">
    <xsl:param name="buttons"/>
    <xsl:param name="prefix"/>
    <xsl:for-each select="$buttons">
        <xsl:call-template name="form_by_rows">
            <xsl:with-param name="form" select="null"/>
            <xsl:with-param name="node" select="null"/>
            <xsl:with-param name="prefix" select="$prefix"/>
            <xsl:with-param name="iter" select="row"/>
        </xsl:call-template>
    </xsl:for-each>
</xsl:template>

<xsl:template name="form_quick">
    <xsl:param name="quick"/>
    <xsl:param name="prefix"/>
    <xsl:for-each select="$quick">
        <xsl:call-template name="form_by_rows">
            <xsl:with-param name="form" select="null"/>
            <xsl:with-param name="node" select="null"/>
            <xsl:with-param name="prefix" select="prefix"/>
            <xsl:with-param name="iter" select="row"/>
        </xsl:call-template>
    </xsl:for-each>
</xsl:template>

<xsl:template match='/'>
    <html lang="{/root/info/lang}" dir="{/root/info/dir}">
        <head>
            <xsl:call-template name="head"/>
        </head>
        <body>
            <div data-role="page" id="page" class="{/root/info/dir}" data-theme="a">
                <div class="ui-layout-north">
                    <xsl:call-template name="title"/>
                </div>
                <div class="ui-layout-west">
                    <xsl:call-template name="menu"/>
                </div>
                <div class="ui-layout-center">
                    <xsl:call-template name="list"/>
                    <xsl:call-template name="form"/>
                    <xsl:call-template name="alert"/>
                    <xsl:call-template name="error"/>
                </div>
                <div data-role="footer" data-position="fixed" data-tap-toggle="false" id="jGrowl" data-hide-during-focus=""></div>
            </div>
        </body>
    </html>
</xsl:template>

</xsl:stylesheet>
