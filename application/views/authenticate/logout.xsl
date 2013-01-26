<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of the Xerxes project.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Logout view
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
<xsl:import href="../includes.xsl" />
<xsl:output method="html" />

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
	
	<form name="form1" method="post" action="authenticate/logout">
		<input name="lang" type="hidden" value="{//request/lang}" />
		<input name="return" type="hidden" value="{$return}" />
		<input name="postback" type="hidden" value="true" />
		
		<h1><xsl:call-template name="page_name" /></h1>
		<p><xsl:copy-of select="$text_authentication_logout_confirm" /></p>
		<p><input type="submit" class="submit-logout{$language_suffix}" name="Submit" value="{$text_authentication_logout_pagename}" /></p>
	</form>
	
</xsl:template>
</xsl:stylesheet>
