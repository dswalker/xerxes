<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2009 California State University
 version: $Id: metasearch_save-delete.xsl 976 2009-11-02 14:22:56Z dwalker@calstate.edu $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:include href="../includes.xsl" />

<xsl:output method="html" encoding="utf-8" indent="yes" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="main">
	
	<xsl:variable name="back" select="request/server/http_referer" />
	
	<h1>
	<xsl:choose>
		<xsl:when test="delete = '1'">Record successfully removed from saved records</xsl:when>
		<xsl:otherwise>Record successfully added to saved records</xsl:otherwise>
	</xsl:choose>
	</h1>
	
	<p>Return to <a href="{$back}">results page</a></p>	
	
</xsl:template>
</xsl:stylesheet>
