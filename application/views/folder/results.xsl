<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2010 California State University
 version: $Id$
 package: Solr
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:import href="../includes.xsl" />
	<xsl:import href="../search/results.xsl" />

	<xsl:output method="html" />
	
	<xsl:template match="/*">
		<xsl:call-template name="surround" />
	</xsl:template>
	
	<xsl:template name="breadcrumb">
		<!-- TODO: FIX THIS ?   <xsl:call-template name="breadcrumb_worldcat" /> -->
		<xsl:call-template name="page_name" />
	</xsl:template>
	
	<xsl:template name="page_name">
		Saved Records
	</xsl:template>
	
	<xsl:template name="title">
		<xsl:value-of select="//request/query" />
	</xsl:template>
	
	<xsl:template name="main">
		
		<h1>My Saved Records</h1>
		
		<xsl:call-template name="facets_applied" />
		
		<div class="saved-records-sort">
			<xsl:call-template name="sort_bar" />
		</div>
		
		<table id="folder_output_results">
			<thead>
				<tr>
					<td><input type="checkbox" value="true" id="saved-select-all" /></td>
					<td>Title</td>
					<td>Author</td>
					<td>Format</td>
					<td>Year</td>
				</tr>
			</thead>
			
			<xsl:for-each select="//results/records/record/xerxes_record">
				<tr>
					<td><input type="checkbox" name="record" value="{id}" id="record-{id}" /></td>
					<td>
						<label for="record-{id}">
							<a href="{../url_full}">
								<xsl:value-of select="title_normalized" />
							</a>
						</label>
					</td>
					<td><xsl:value-of select="primary_author" /></td> 
					<td>
						<xsl:call-template name="text_results_format">
							<xsl:with-param name="format" select="format/public" />
						</xsl:call-template>
					</td>
					<td>
						<xsl:value-of select="year" />
					</td>
				</tr>
			</xsl:for-each>
			
		</table>

		<xsl:call-template name="paging_navigation" />
		
		<xsl:call-template name="hidden_tag_layers" />
		
	</xsl:template>
		
</xsl:stylesheet>
