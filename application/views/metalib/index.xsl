<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2010 California State University
 version: $Id$
 package: Worldcat
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="../search/results.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="sidebar">

</xsl:template>

<xsl:template name="main">

		<h1>Testing</h1>
		
		<xsl:call-template name="searchbox" />
	
</xsl:template>

<xsl:template name="advanced_search_option">
	<input name="subject" value="history" />
</xsl:template>

</xsl:stylesheet>
