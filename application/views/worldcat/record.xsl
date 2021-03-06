<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Worldcat record view
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	
<xsl:import href="../includes.xsl" />
<xsl:import href="../search/results.xsl" />
<xsl:import href="../search/record.xsl" />
<xsl:import href="../search/books.xsl" />
<xsl:import href="worldcat.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_search" />
	<xsl:value-of select="$text_search_record" />
</xsl:template>

<xsl:template name="page_name">
	<xsl:for-each select="/*/results/records/record/xerxes_record">
		<xsl:call-template name="record_title" />
	</xsl:for-each>
</xsl:template>

<xsl:template name="main">
		
	<xsl:call-template name="record" />
	
</xsl:template>


<xsl:template name="availability">

	<xsl:if test="../holdings/holding">

		<table class="holdings-table">
	
			<tr>
				<th><xsl:value-of select="$text_worldcat_institution" /></th>
				<th><xsl:value-of select="$text_worldcat_availability" /></th>
			</tr>
	
			<xsl:for-each select="../holdings/holding">
			
				<tr>
					<td><span id="institution-{oclc}"><xsl:value-of select="institution" /></span></td>
					<td><a href="{url}" aria-describedby="institution-{oclc}"><xsl:value-of select="$text_worldcat_check_availability" /></a></td>
				</tr>
			
			</xsl:for-each>
	
		</table>
		
	</xsl:if>
	
	<xsl:call-template name="worldcat_results_availability" />

</xsl:template>	

</xsl:stylesheet>
