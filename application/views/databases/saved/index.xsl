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

 My Saved Databases home page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../index.xsl" />

<xsl:template name="module_nav" />

<xsl:template name="breadcrumb">

	<xsl:call-template name="breadcrumb_start" />
	
	<a href="{//navbar/databases_link}"><xsl:value-of select="$text_databases_category_pagename" /></a>
	
	<xsl:value-of select="$text_breadcrumb_separator" />
	
	<xsl:call-template name="page_name" />

</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="$text_header_collections" />
</xsl:template>

<xsl:template name="main">

	<h1><xsl:value-of select="$text_header_collections" /></h1>
	
	<div class="databases-categories-list">
		<xsl:call-template name="loop_columns" />
	</div>
	
	<div style="margin: 2em">
		<a href="{//request/controller}/create-new" class="btn">Create new list</a>
	</div>
	
</xsl:template>

<xsl:template name="category_link">

	<a href="{//request/controller}/subject?id={id}"><xsl:value-of select="name" /></a>

</xsl:template>

</xsl:stylesheet>
