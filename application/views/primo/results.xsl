<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of the Xerxes project.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Primo results view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:import href="../includes.xsl" />
	<xsl:import href="../search/results.xsl" />

	<xsl:output method="html" />
	
	<xsl:template match="/*">
		<xsl:call-template name="surround">
			<xsl:with-param name="surround_template">none</xsl:with-param>
			<xsl:with-param name="sidebar">none</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template name="breadcrumb">
		<!-- TODO: FIX THIS ?   <xsl:call-template name="breadcrumb_worldcat" /> -->
		<xsl:call-template name="page_name" />
	</xsl:template>
	
	<xsl:template name="page_name">
		Search Results
	</xsl:template>
	
	<xsl:template name="title">
		<xsl:value-of select="//request/query" />
	</xsl:template>
	
	<xsl:template name="main">
		<xsl:call-template name="search_page" />
	</xsl:template>
		
</xsl:stylesheet>
