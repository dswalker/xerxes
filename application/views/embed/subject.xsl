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
<xsl:import href="../databases/subject.xsl" />

<xsl:output method="html" encoding="utf-8" />

<xsl:template match="/*">

	<xsl:if test="request/disp_embed_css = 'true'">
		<xsl:call-template name="disp_embed_css" />
	</xsl:if>
	
	<xsl:call-template name="display_category" />	

</xsl:template>

<xsl:template name="display_category">

	<!-- show title -->
	
	<xsl:variable name="show_title">
		<xsl:choose>
			<xsl:when test="//request/disp_show_title = 'false'">
				<xsl:text>false</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>true</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<!-- show search box -->
	
	<xsl:variable name="show_searchbox">
		<xsl:choose>
			<xsl:when test="//request/disp_show_search = 'false'">
				<xsl:text>false</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>true</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<!-- show subcategories -->
	
	<xsl:variable name="show_subcategories">
		<xsl:choose>
			<xsl:when test="//request/disp_show_subcategories = 'false'">
				<xsl:text>false</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>true</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<xsl:if test="$show_title = 'true'">
		<h1><xsl:value-of select="category/name" /></h1>
	</xsl:if>
	
	<xsl:if test="$show_searchbox = 'true'">
		<xsl:call-template name="searchbox" />
	</xsl:if>
	
	<xsl:call-template name="subject_databases_list">
		<xsl:with-param name="show_title">false</xsl:with-param>
		<xsl:with-param name="show_subcategories" select="$show_subcategories" />
		<xsl:with-param name="show_database_description">false</xsl:with-param>
		<xsl:with-param name="show_individual_subcategory" select="request/disp_only_subcategory" />
	</xsl:call-template>

</xsl:template>

<xsl:template name="disp_embed_css">

	<style type="text/css">
		@import url(<xsl:value-of select="base_url"/>/css/xerxes-embeddable.css);
	</style>

</xsl:template>

</xsl:stylesheet>
