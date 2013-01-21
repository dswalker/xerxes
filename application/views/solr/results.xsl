<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2010 California State University
 version: $Id$
 package: Solr
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="../search/results.xsl" />
<xsl:import href="../search/books.xsl" />

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
	<xsl:call-template name="page_name" />
</xsl:template>

<xsl:template name="main">
	<xsl:call-template name="search_page" />
</xsl:template>

<xsl:template name="facet_narrow_results">

	<xsl:for-each select="config/search/option[@current = '1']/option">

		<div id="subtab" class="box">
		
			<h3>Expand your search</h3>
			
			<ul>
				<li><a href="{@url}"><xsl:value-of select="@public" /></a></li>
			</ul>
			
		</div>
		
	</xsl:for-each>
		
</xsl:template>

<xsl:template name="facet_dates">
	<xsl:call-template name="facet_links" />
</xsl:template>
		
</xsl:stylesheet>
