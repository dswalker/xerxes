<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet  [
	<!ENTITY nbsp   "&#160;">
	<!ENTITY copy   "&#169;">
	<!ENTITY reg    "&#174;">
	<!ENTITY times  "&#215;">
	<!ENTITY trade  "&#8482;">
	<!ENTITY mdash  "&#8212;">
	<!ENTITY ldquo  "&#8220;">
	<!ENTITY rdquo  "&#8221;"> 
	<!ENTITY pound  "&#163;">
	<!ENTITY yen    "&#165;">
	<!ENTITY euro   "&#8364;">
]>
<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Databases search page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../search/results.xsl" />
<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="$text_databases_az_pagename" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_databases" />
</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="databases_list" />
				
</xsl:template>

<xsl:template name="databases_list">
	
	<h1><xsl:call-template name="page_name" /></h1>
	
	<xsl:call-template name="searchbox">
		<xsl:with-param name="action"><xsl:value-of select="//request/controller"/>/<xsl:value-of select="//request/action"/></xsl:with-param>
		<xsl:with-param name="search_box_placeholder" select="$text_databases_az_search" />
	</xsl:call-template>
	
	<xsl:call-template name="databases_alpha_listing" />
		
	<xsl:call-template name="database_results" />
	
	<xsl:call-template name="databases_alpha_listing" />
	
</xsl:template>

</xsl:stylesheet>