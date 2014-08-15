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
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="database/title" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_databases" />
</xsl:template>

<xsl:template name="breadcrumb_databases_intermediate">
	<a href="{//request/controller}/alphabetical"><xsl:value-of select="$text_databases_az_pagename" /></a>
	<xsl:value-of select="$text_breadcrumb_separator" />
</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="databases_full" />
				
</xsl:template>

<xsl:template name="databases_full">
	
	<h1><xsl:call-template name="page_name" /></h1>
	
	<xsl:for-each select="database">
	
		<div class="database-record-description">
			<xsl:value-of select="description" />
		</div>
		
		<div class="database-details">
		
			<dl>
			
				<div>
					<dt><xsl:copy-of select="$text_database_link" />:</dt>
					<dd><a href="{//request/controller}/proxy?id={id}"><xsl:value-of select="$text_database_go_to_database" /></a>
					</dd>
				</div>

				<xsl:if test="type">
					<div>
						<dt><xsl:copy-of select="$text_database_type" />:</dt>
						<dd><xsl:value-of select="type" /></dd>
					</div>
				</xsl:if>

				<xsl:if test="keywords">
					<div>
						<dt><xsl:copy-of select="$text_database_keywords" />:</dt>
						<dd>
							<xsl:for-each select="keywords/keyword">
								<xsl:value-of select="text()" />
								<xsl:if test="following-sibling::keyword">
									<xsl:text>,</xsl:text>
								</xsl:if>
							</xsl:for-each>
						</dd>
					</div>
				</xsl:if>
					
				<xsl:if test="coverage">
					<div>
						<dt><xsl:copy-of select="$text_database_coverage" />:</dt>
						<dd><xsl:value-of select="coverage" /></dd>
					</div>
				</xsl:if>
				
				<xsl:if test="creator">
					<div>
						<dt><xsl:copy-of select="$text_database_creator" />:</dt>
						<dd><xsl:value-of select="creator" /></dd>
					</div>
				</xsl:if>
	
				<xsl:if test="publisher">
					<div>
						<dt><xsl:copy-of select="$text_database_publisher" />:</dt>
						<dd><xsl:value-of select="publisher" /></dd>
					</div>
				</xsl:if>
				
				<xsl:if test="search_hints">
					<div>
						<dt><xsl:copy-of select="$text_database_search_hints" />:</dt>
						<dd><xsl:value-of select="search_hints" /></dd>
					</div>
				</xsl:if>
				
				<xsl:if test="link_guide">
					<div>
						<dt><xsl:copy-of select="$text_database_guide" />:</dt>
						<dd>
							<a href="{link_guide}">
								<xsl:value-of select="$text_database_guide_help" />
							</a>
						</dd>
					</div>
				</xsl:if>
			
			</dl>
			
		</div>
				
	</xsl:for-each>
	
</xsl:template>


</xsl:stylesheet>