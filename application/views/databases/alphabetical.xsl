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
<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<a href="{//request/controller}">Databases</a>
	<xsl:value-of select="$text_breadcrumb_separator" />
	<xsl:text>Alphabetical</xsl:text>
</xsl:template>

<xsl:template name="module_header">

	<style type="text/css">
		
		.databases-list {
			margin-bottom: 2em;
			max-width: 600px;
		}
		.database-title {
			font-weight: bold;
			margin-bottom: .5em;
		}
		.database-description {
			color: #666;
		}
		.database-more-info {
			margin-top: .3em;
		}		
		.database-more-info a, .database-more-info a:visited {
			color: green;
		}
					
	</style>
	
</xsl:template>

<xsl:template name="module_nav">

	<xsl:call-template name="module_nav_display">
		<xsl:with-param name="url">databases-edit/alphabetical</xsl:with-param>
	</xsl:call-template>

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
				<a href="{link}"><xsl:value-of select="title" /></a>
			</div>
			<div class="database-description">
				<xsl:choose>
					<xsl:when test="string-length(description) &gt; 300">
						<xsl:value-of select="substring(description, 1, 300)" /> . . .
					</xsl:when>
					<xsl:when test="description">
						<xsl:value-of select="description" />
					</xsl:when>
				</xsl:choose>
			</div>
			<div class="database-more-info">
				<a href="{//request/controller}/database/{id}">More information</a>
			</div>
		</li>

	</xsl:for-each>
	
	</ul>
	
</xsl:template>


</xsl:stylesheet>