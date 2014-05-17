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

<xsl:template name="page_name">
	<xsl:value-of select="librarians/name" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_databases" />
</xsl:template>

<xsl:template name="breadcrumb_databases_intermediate">
	<a href="{//request/controller}/librarians">Librarians</a>
	<xsl:value-of select="$text_breadcrumb_separator" />
</xsl:template>

<xsl:template name="module_header">

	<style type="text/css">
		
		#bd dt {
			clear: none;
		}
					
	</style>
	
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
	
		<div style="clear:left">
				
			<div style="float: left; width: 180px">
				<img src="databases/librarian-image?id={id}" alt="{name}" />
			</div>
					
			<div style="margin-left: 180px">
				
				<h1><xsl:value-of select="name" /></h1>

				<dl>

					<div>
						<dt>Website:</dt>
						<dd><a href="{link}"><xsl:value-of select="link" /></a></dd>
					</div>
					
					<xsl:call-template name="librarian_details" />
				
				</dl>
				
			</div>
			
		</div>
					
	</xsl:for-each>
	
</xsl:template>

</xsl:stylesheet>