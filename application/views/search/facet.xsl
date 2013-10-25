<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet  [
	<!ENTITY nbsp   "&#160;">
	<!ENTITY copy   "&#169;">
	<!ENTITY reg    "&#174;">
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

 Single facet view
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

<xsl:template name="module_header">
	<link href="css/books.css?version={$asset_version}" rel="stylesheet" type="text/css" />
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

	<html>
		<head></head>
	<body>
		
		<form id="form-facet-selector" action="{$base_url}/{//request/controller}/search" method="get">	
		<input name="lang" type="hidden" value="{//request/lang}" />
		<xsl:for-each select="//facets/groups/group[param_name = //request/group]">
		
			<xsl:call-template name="hidden_search_inputs">
				<xsl:with-param name="exclude_limit" select="//request/group" />
			</xsl:call-template>
			
			<xsl:call-template name="facet_multiple_table" />
			
			<input type="submit" class="facets-submit{$language_suffix}">
				<xsl:attribute name="value"><xsl:value-of select="$text_facets_submit" /></xsl:attribute>
			</input>
		
		</xsl:for-each>
		
		</form>
	
	</body>
	</html>
	
</xsl:template>
		
</xsl:stylesheet>
