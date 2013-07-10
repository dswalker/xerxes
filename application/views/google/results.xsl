<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Ebsco results view
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
	<xsl:call-template name="breadcrumb_search" />
	<xsl:value-of select="$text_search_results" />
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="//request/query" />
</xsl:template>

<xsl:template name="title">
	<xsl:value-of select="//request/query" />
</xsl:template>

<xsl:template name="main">
	<xsl:call-template name="search_page" />
</xsl:template>

<!-- 
	TEMPLATE: BRIEF RESULT
	display of results geared toward articles (or really any non-book display)
-->

<xsl:template name="brief_result_info">

	<div style="color: #666; max-width: 600px; margin-top: .5em">
		<span style="color: green"><xsl:value-of select="../url_full" /></span><br />
		<xsl:value-of select="snippet" />
	</div>

</xsl:template>

		
</xsl:stylesheet>
