<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:include href="../includes.xsl" />

<xsl:output method="html" />

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
