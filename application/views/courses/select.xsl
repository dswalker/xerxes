<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2007 California State University
 version: $Id: folder_output_refworks.xsl 1537 2010-12-03 14:37:57Z helix84@centrum.sk $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="../search/results.xsl" />

<xsl:output method="html" encoding="utf-8" indent="yes" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="sidebar">

</xsl:template>

<xsl:template name="breadcrumb">
	<!-- <xsl:call-template name="breadcrumb_folder" /> -->
	<xsl:call-template name="page_name" />
</xsl:template>

<xsl:template name="page_name">
	Reading List Import
</xsl:template>

<xsl:template name="main">

	<xsl:variable name="username" 	select="request/session/username" />
	<xsl:variable name="sort" 		select="request/sortkeys" />
	
	<div id="export">

		<form action="{$base_url}/courses/assign" name="export_form"  method="get">
		<input type="hidden" name="username" value="{$username}" />
		
		<h1><xsl:call-template name="page_name" /></h1>
				
		<input id="export_single{$language_suffix}" type="submit" name="Submit" value="Import" />
		

		<xsl:for-each select="//xerxes_record">
			<p>Yes!</p>
		</xsl:for-each>
		
		</form>

	</div>
	
</xsl:template>

</xsl:stylesheet>
