<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Save html view
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	<xsl:include href="../includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="main">
		
	<h1>
	<xsl:choose>
		<xsl:when test="delete = '1'"><xsl:value-of select="$text_folder_record_removed" /></xsl:when>
		<xsl:otherwise><xsl:value-of select="$text_folder_record_added" /></xsl:otherwise>
	</xsl:choose>
	</h1>
	
	<p><xsl:value-of select="$text_folder_tags_edit_return" /><a href="{return_url}"><xsl:value-of select="$text_folder_return_to_results" /></a></p>
	
</xsl:template>
</xsl:stylesheet>
