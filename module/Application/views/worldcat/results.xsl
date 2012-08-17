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

	<xsl:call-template name="search_page">
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
	
</xsl:template>

<xsl:template name="availability">
	<xsl:call-template name="worldcat_results_availability" />
</xsl:template>	

<xsl:template name="worldcat_results_availability">

	<div class="record-action">
		<a href="{../url_open}">
		<xsl:choose>
			<xsl:when test="//config/worldcat_groups/group[@id=//request/source]/lookup/ill_text">

				<img src="images/ill.gif" alt=""/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="//config/worldcat_groups/group[@id=//request/source]/lookup/ill_text" />					

			</xsl:when>
			<xsl:otherwise>
			
				<img src="{$image_sfx}" alt="" border="0" class="mini-icon link-resolver-link "/>
				<xsl:text> </xsl:text>
				<xsl:copy-of select="$text_link_resolver_check" /> 

			</xsl:otherwise>
		</xsl:choose>
		</a>
	</div>

</xsl:template>
		
</xsl:stylesheet>
