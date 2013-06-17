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
			<xsl:with-param name="sidebar">right</xsl:with-param>
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
	
	<xsl:template name="search_results_area">
		
		<xsl:call-template name="facets_applied" />
	
		<form action="folder">
	
		<div class="folder-options">
			
			<!--

			<div class="folder-records-selected">
			
				Records: <xsl:text> </xsl:text>
	
				<input type="radio" name="records-selected" value="selected" /> <label>Selected</label>
				<xsl:text> </xsl:text>
				<input type="radio" name="records-selected" value="page" /> <label>This page</label>
				<xsl:text> </xsl:text>
				<input type="radio" name="records-selected" value="all"/> <label>All records</label>
				
			</div>
			
			-->
						
			<div class="export">
			
				Export options: <xsl:text> </xsl:text>
				
				<select name="output" class="selectpicker">
					<option value="email" data-icon="icon-envelope">
						<xsl:value-of select="$text_folder_email_pagename" />
					</option>
					<option value="refworks" data-icon="icon-share">
						<xsl:value-of select="$text_folder_refworks_pagename" />
					</option>
					<option value="endnoteweb" data-icon="icon-share">
						Export to Endote Web
					</option>						
					<option value="endnote" data-icon="icon-download">
						<xsl:value-of select="$text_folder_endnote_pagename" />
					</option>
					<option value="text" data-icon="icon-download-alt">
						<xsl:value-of select="$text_folder_file_pagename" />
					</option>
				</select>
				
				<xsl:text> </xsl:text>
				<button type="submit" class="btn btn-primary output-export" name="action" value="export">Export</button>
				
			</div>
			<div class="assign">
				
				Add label to records: 
				
				<input type="text" name="label" data-provide="typeahead">
					<xsl:attribute name="data-source">				
						<xsl:text>[</xsl:text>
						<xsl:for-each select="//facets/groups/group[name='label']/facets/facet">
							<xsl:text>"</xsl:text><xsl:value-of select="name"  /><xsl:text>"</xsl:text>
							<xsl:if test="following-sibling::facet">
								<xsl:text>,</xsl:text>
							</xsl:if>
						</xsl:for-each>
						<xsl:text>]</xsl:text>
					</xsl:attribute>
				</input>
				
				<button type="submit" class="btn btn-primary output-export" name="action" value="label">Add</button>
				
			</div>

			<div>
				<button type="submit" class="btn" name="action" value="delete">
					<i class="icon-trash"></i><xsl:text> </xsl:text>Delete
				</button>
			</div>
			
		</div>
		
		<xsl:call-template name="sort_bar" />
		
		<table id="folder-output-results">
			<thead>
				<tr>
					<td><input type="checkbox" value="true" id="folder-select-all" /></td>
					<td>Title</td>
					<td>Author</td>
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
		
		<input type="hidden" name="return" value="{//request/server/request_uri}" />
		
		</form>
		
		<xsl:call-template name="paging_navigation" />
		
	</xsl:template>

	<xsl:template name="search_sidebar_facets">
			
		<div class="box">
			
			<h2>Limit your saved records</h2>
		
			<xsl:call-template name="facet_narrow_results" />
			
			<xsl:for-each select="//facets/groups/group[not(display)]">

				<h3><xsl:value-of select="public" /></h3>
					
				<ul>
				<xsl:for-each select="facets/facet">
					<xsl:call-template name="facet_option" />
				</xsl:for-each>
				</ul>

			</xsl:for-each>
						
		</div>
	
	</xsl:template>	
		
</xsl:stylesheet>
