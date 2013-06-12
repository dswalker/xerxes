<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--
 
 Saved Records results view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:import href="../includes.xsl" />
	<xsl:import href="../search/results.xsl" />

	<xsl:output method="html" />
	
	<xsl:template match="/*">
		<xsl:call-template name="surround">
			<xsl:with-param name="surround_template">none</xsl:with-param>
			<xsl:with-param name="sidebar">none</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template name="breadcrumb">
		<xsl:call-template name="breadcrumb_search" />
		<xsl:call-template name="page_name" />
	</xsl:template>
	
	<xsl:template name="page_name">
		<xsl:call-template name="folder_header_label" />
	</xsl:template>
	
	<xsl:template name="main">
		<xsl:call-template name="search_page">
			<xsl:with-param name="sidebar_width">2</xsl:with-param>
		</xsl:call-template>
	</xsl:template>	
	
	<xsl:template name="module_javascript">
		<script src="{$base_include}/javascript/folder.js?version={$asset_version}"  type="text/javascript"></script>
	</xsl:template>
	
	<!-- no search modules, please -->
	
	<xsl:template name="search_modules" />
		
	<!-- 
		TEMPLATE: SEARCH TOP
		hijack this and show header here instead of search box
	-->	
	
	<xsl:template name="searchbox">
	
		<h1><xsl:call-template name="folder_header_label" /></h1>
		
		<xsl:if test="request/session/role = 'local'">
			<p class="temporary_login_note"><xsl:copy-of select="$text_folder_login_temp" /></p>
		</xsl:if>	
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: FOLDER HEADER LABEL
		whether this is 'temporary' or 'my' saved records
	-->
	
	<xsl:template name="folder_header_label">
		<xsl:choose>
			<xsl:when test="$temporarySession = 'true'">
				<xsl:copy-of select="$text_folder_header_temporary" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:copy-of select="$text_header_savedrecords" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	
	
	<xsl:template name="brief_results">
	
		<form action="folder">
	
		<div class="folder-export-options">
			<select name="action">
			  <option>Export options</option>
			  <option value="email"><xsl:value-of select="$text_folder_email_pagename" /></option>
			  <option value="refworks"><xsl:value-of select="$text_folder_refworks_pagename" /></option>
			  <option value="endnote"><xsl:value-of select="$text_folder_endnote_pagename" /></option>
			  <option value="endnoteweb">Export to Endote Web</option>
  			  <option value="text"><xsl:value-of select="$text_folder_file_pagename" /></option>
			</select>
			
			<input type="submit" value="Export" />
		</div>
		
		<table id="folder-output-results">
			<thead>
				<tr>
					<td><input type="checkbox" value="true" id="folder-select-all" onclick="checkAll" /></td>
					<td>Title</td>
					<td>Author</td>
					<td>Tags</td>
					<td>Format</td>
					<td>Year</td>
				</tr>
			</thead>
			
			<xsl:for-each select="//results/records/record/xerxes_record">
				<tr>
					<td><input type="checkbox" name="record" value="{../id}" id="record-{../id}" class="folder-output-checkbox" /></td>
					<td class="title-cell">
						<label for="record-{../id}">
							<a href="{../url_full}">
								<xsl:value-of select="title_normalized" />
							</a>
						</label>
					</td>
					<td class="author-cell">
						<xsl:value-of select="authors/author/aulast" />
					</td>
					<td class="tag-cell">
						tags!
					</td> 
					<td class="format-cell">
						<xsl:call-template name="text_results_format">
							<xsl:with-param name="format" select="format/public" />
						</xsl:call-template>
					</td>
					<td class="year-cell">
						<xsl:value-of select="year" />
					</td>
				</tr>
			</xsl:for-each>
			
		</table>
		
		</form>
		
	</xsl:template>
		
</xsl:stylesheet>
