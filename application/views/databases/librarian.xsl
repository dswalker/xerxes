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
	<a href="{//request/controller}/librarians">Librarians</a>
	<xsl:value-of select="$text_breadcrumb_separator" />
	<xsl:text>Librarian</xsl:text>
</xsl:template>

<xsl:template name="module_nav">

	<xsl:call-template name="module_nav_display">
		<xsl:with-param name="url">databases-edit/librarian/<xsl:value-of select="librarians/id" /></xsl:with-param>
	</xsl:call-template>

</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="librarian_full" />
				
</xsl:template>

<xsl:template name="librarian_full">
	
	<xsl:for-each select="librarians">
	
		<h1><xsl:value-of select="name" /></h1>
				
		<div class="database-details">
			
			<xsl:if test="email">
				<div>
					<dt><xsl:copy-of select="$text_databases_subject_librarian_email" />:</dt>
					<dd><xsl:value-of select="email" /></dd>
				</div>
			</xsl:if>

			<xsl:if test="phone">
				<div>
					<dt><xsl:copy-of select="$text_databases_subject_librarian_telephone" />:</dt>
					<dd><xsl:value-of select="phone" /></dd>
				</div>
			</xsl:if>

			<xsl:if test="office">
				<div>
					<dt><xsl:copy-of select="$text_databases_subject_librarian_office" />:</dt>
					<dd><xsl:value-of select="office" /></dd>
				</div>
			</xsl:if>

			<xsl:if test="office_hours">
				<div>
					<dt><xsl:copy-of select="$text_databases_subject_librarian_office_hours" />:</dt>
					<dd><xsl:value-of select="office_hours" /></dd>
				</div>
			</xsl:if>
			
		</div>
				
	</xsl:for-each>
	
</xsl:template>

</xsl:stylesheet>