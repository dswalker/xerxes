<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2007 California State University
 version: $Id: authenticate_login.xsl 1537 2010-12-03 14:37:57Z helix84@centrum.sk $
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
	<xsl:value-of select="$text_authentication_login_pagename" />
</xsl:template>

<xsl:template name="main">

	<xsl:variable name="return" 	select="request/return" />
	<xsl:variable name="local" 	select="request/local" />
	<xsl:variable name="authentication_source" select="request/authentication_source" />

	<xsl:variable name="username">
		<xsl:if test="not(contains(request/session/username,'local@')) and not(contains(request/session/username,'guest@'))">
			<xsl:value-of select="request/session/username" />
		</xsl:if>
	</xsl:variable>
	
	<div id="authentication">
	
		<h1><xsl:call-template name="page_name" /></h1>
		<p><xsl:copy-of select="$text_authentication_login_explain" /></p>
		
		<xsl:if test="error = 'authentication'">
			<p class="error"><xsl:copy-of select="$text_authentication_login_failed" /></p>
		</xsl:if>
		
		<div class="box">
				
			<form name="login" method="post" action="./">
				<input name="lang" type="hidden" value="{//request/lang}" />
				<input name="base" type="hidden" value="authenticate" />
				<input name="action" type="hidden" value="login" />
				<input name="return" type="hidden" value="{$return}" />
				<input name="local" type="hidden" value="{$local}" />
				<input name="authentication_source" type="hidden" value="{$authentication_source}"/>
				<input name="postback" type="hidden" value="true" />  
				
				<p>
				<label for="username"><xsl:copy-of select="$text_authentication_login_username" /></label>
				<input name="username" type="text" id="username" value="{$username}" />
				</p>
				
				<p>
				<label for="password"><xsl:copy-of select="$text_authentication_login_password" /></label>
				<input name="password" type="password" id="password" />
				</p>
				
				<input type="submit" class="login_submit{$language_suffix}" name="Submit" value="{$text_authentication_login_pagename}" />
				
			</form>
			
		</div>
		
	</div>
	
</xsl:template>
</xsl:stylesheet>
