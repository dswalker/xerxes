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

 Databases home page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<xsl:call-template name="page_name" />
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="$text_databases_category_pagename" />
</xsl:template>

<xsl:template name="module_header">
	<style type="text/css">
	
	.databases-categories-list li {
		padding: 5px;
	}
	
	.databases-categories-list a {
		position: relative;
		padding-left: 20px;
	}

	.databases-categories-list a:before { 
		content: "\203A"; 
		color: #999; 
		position: absolute; 
		top: -1px; 
		left: 3px; 
	}
	
	</style>
	
</xsl:template>

<xsl:template name="main">

	<h1><xsl:call-template name="page_name" /></h1>
	
	<h2><xsl:value-of select="$text_databases_az_pagename" /></h2>
	<xsl:call-template name="databases_alpha_listing" />
	
	<h2><xsl:copy-of select="$text_databases_category_subject" /></h2>
	<p><xsl:copy-of select="$text_databases_category_subject_desc" /></p>
		
	<div class="databases-categories-list">
		<xsl:call-template name="loop_columns" />
	</div>
	
	<xsl:call-template name="databases_edit" />
	
</xsl:template>

<!-- 
	TEMPLATE: LOOP_COLUMNS
	
	A recursively called looping template for dynamically determined number of columns.
	produces the following logic 
-->

<xsl:template name="loop_columns">
	<xsl:param name="num_columns">
		<xsl:choose>
			<xsl:when test="count(//categories/category) &lt;= 10">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:when test="//config/num_columns">
				<xsl:value-of select="//config/num_columns" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>2</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:param>
	<xsl:param name="iteration_value">1</xsl:param>
	
	<xsl:variable name="width" select="floor(12 div $num_columns)" />
	<xsl:variable name="total" select="count(categories/category)" />
	<xsl:variable name="numRows" select="ceiling($total div $num_columns)"/>

	<xsl:if test="$iteration_value &lt;= $num_columns">
		
		<div class="span{$width}">
			
			<ul>
			<xsl:for-each select="categories/category[position() &gt; ($numRows * ($iteration_value -1)) and 
				position() &lt;= ( $numRows * $iteration_value )]">
				
				<xsl:variable name="normalized" select="normalized" />
				<li>
					<xsl:call-template name="category_link" />
				</li>
			</xsl:for-each>
			</ul>
			
		</div>
		
		<xsl:call-template name="loop_columns">
			<xsl:with-param name="num_columns" select="$num_columns"/>
			<xsl:with-param name="iteration_value"  select="$iteration_value+1"/>
		</xsl:call-template>
	
	</xsl:if>
	
</xsl:template>

<xsl:template name="category_link">

	<a href="{//request/controller}/subject/{normalized}"><xsl:value-of select="name" /></a>

</xsl:template>

<xsl:template name="databases_edit" />

</xsl:stylesheet>
