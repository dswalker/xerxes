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

<xsl:import href="../includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<a href="{//request/controller}">Databases</a>
</xsl:template>

<xsl:template name="module_header">

	<style type="text/css">
		
		.databases-list {
			margin-bottom: 2em;
		}
		.database-title {
			font-weight: bold;
			margin-bottom: .7 em;
		}
		.database-description {
			font-color: #eee;
		}
					
	</style>
	
</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="databases_list" />
				
</xsl:template>

<xsl:template name="databases_list">
	
	<h1>Databases</h1>
	
	<ul>
		
	<xsl:for-each select="databases/database">

		<li class="databases-list">
			<div class="database-title">
				<xsl:value-of select="title" />
			</div>
			<div class="database-description">
				<xsl:value-of select="description" />
			</div>
		</li>

	</xsl:for-each>
	
	</ul>
	
</xsl:template>


</xsl:stylesheet>