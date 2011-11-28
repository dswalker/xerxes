<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2009 California State University
 version: $Id: authenticate_logout.xsl 1537 2010-12-03 14:37:57Z helix84@centrum.sk $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
<xsl:import href="../includes.xsl" />
<xsl:output method="html" encoding="utf-8" indent="yes" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="$text_authentication_logout_pagename" />
</xsl:template>

<xsl:template name="main">

	<xsl:variable name="return"		select="request/return" />
	
	<form name="form1" method="post" action="./">
	<input name="lang" type="hidden" value="{//request/lang}" />
	<input name="base" type="hidden" value="authenticate" />
	<input name="action" type="hidden" value="logout" />
	<input name="return" type="hidden" value="{$return}" />
	<input name="postback" type="hidden" value="true" />
	
	<h1><xsl:call-template name="page_name" /></h1>
	<p><xsl:copy-of select="$text_authentication_logout_confirm" /></p>
	<p><input type="submit" class="submit_logout{$language_suffix}" name="Submit" value="{$text_authentication_logout_pagename}" /></p>
	</form>
	
</xsl:template>
</xsl:stylesheet>
