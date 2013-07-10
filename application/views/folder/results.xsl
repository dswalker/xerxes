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
		<xsl:call-template name="breadcrumb_start" />
		<xsl:call-template name="page_name" />
		<xsl:call-template name="return_to_results" />
	</xsl:template>
	
	<xsl:template name="breadcrumb_folder">
			
		<xsl:call-template name="breadcrumb_start" />
		
		<a href="folder">
			My Saved Records
		</a>
		
		<xsl:value-of select="$text_breadcrumb_separator" />
	
	</xsl:template>
	
	<xsl:template name="return_to_results">
	
		<xsl:if test="request/session/return">
		
			<span class="folder-return">
				<xsl:call-template name="img_back" />
				<a href="{request/session/return}"><xsl:copy-of select="$text_folder_return" /></a>
			</span>
		
		</xsl:if>
	
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
			<p class="temporary-login-note"><xsl:copy-of select="$text_folder_login_temp" /></p>
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

	<!-- 
		TEMPLATE: SEARCH RESULTS AREA
		laying out the basic saved records 
	-->
	
	<xsl:template name="search_results_area">
		
		<xsl:call-template name="facets_applied" />
		
		<xsl:choose>
			<xsl:when test="//results/records/record/xerxes_record">
		
				<form action="folder">
			
					<div class="folder-options">
									
						<xsl:call-template name="folder_export_options" />
						
						<xsl:choose>
							<xsl:when test="request/facet_label">
								
								<div>
									<span style="margin-right: 1em">
										<xsl:call-template name="folder_delete" />
									</span>
									<span>
										<xsl:call-template name="folder_remove_tags" />
									</span>
								</div>
								
							</xsl:when>
							<xsl:otherwise>
						
								<xsl:call-template name="folder_tag_group_assign" />
								
								<div>
									<xsl:call-template name="folder_delete" />
								</div>
								
							</xsl:otherwise>
						</xsl:choose>
									
					</div>
					
					<xsl:call-template name="sort_bar" />
					
					<xsl:call-template name="folder_records_table" />
					
					<input type="hidden" name="return" value="{//request/server/request_uri}" />
				
				</form>
				
				<xsl:call-template name="paging_navigation" />
				
			</xsl:when>
			<xsl:otherwise>
				
				<div class="no-hits error">
					Looks like you don't have any saved records.
				</div>
				
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:template>

	<!-- 
		TEMPLATE: FOLDER EXPORT OPTIONS
		the export select control and button 
	-->
	
	<xsl:template name="folder_export_options">
	
		<div class="export">
		
			Export options: <xsl:text> </xsl:text>
			
			<select name="output" class="selectpicker">
			
				<xsl:for-each select="config/export_options/option">
				
					<option value="{@id}" data-icon="{@icon}">
						<xsl:value-of select="@public" />
					</option>
				
				</xsl:for-each>
			</select>
			
			<xsl:text> </xsl:text>
			<button type="submit" class="btn btn-primary output-export" name="action" value="export">Export</button>
			
		</div>
	
	</xsl:template>

	<!-- 
		TEMPLATE: FOLDER TAG GROUP ASSIGN
		the tag input control and button 
	-->
	
	<xsl:template name="folder_tag_group_assign">
	
		<xsl:if test="//request/session/role = 'named'">
	
			<div class="assign">
				
				Add label to records: 
				
				<input type="text" name="tag" data-provide="typeahead" autocomplete="off">
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
			
		</xsl:if>
				
	</xsl:template>

	<!-- 
		TEMPLATE: FOLDER DELETE
		basically the button 
	-->
	
	<xsl:template name="folder_delete">

		<button id="folder-delete" type="submit" class="btn" name="action" value="delete">
			<i class="icon-trash"></i><xsl:text> </xsl:text>Delete
		</button>
			
	</xsl:template>

	<!-- 
		TEMPLATE: FOLDER DELETE
		basically the button 
	-->
	
	<xsl:template name="folder_remove_tags">
		
		<button type="submit" class="btn" name="action" value="label">
			<i class="icon-remove"></i><xsl:text> </xsl:text>Remove  from label
		</button>
		
		<input type="hidden" name="remove" value="true" />
		<input type="hidden" name="tag" value="{request/facet_label}" />
			
	</xsl:template>

	<!-- 
		TEMPLATE: FOLDER RECORDS TABLE
		saved records table
	-->
	
	<xsl:template name="folder_records_table">
	
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
							
							<xsl:if test="../tags/tag">
								<a href="{../url_full}" class="folder-tags" rel="tooltip" data-toggle="tooltip" data-placement="top" title="">
									<xsl:attribute name="data-original-title">
										<xsl:for-each select="../tags/tag">
											<xsl:value-of select="text()" />
											<xsl:if test="following-sibling::tag">
												<xsl:text>, </xsl:text>
											</xsl:if>
										</xsl:for-each>
									</xsl:attribute>
									<i class="icon-tags" />
								</a>
							</xsl:if>
						
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
	
	</xsl:template>

	<!-- 
		TEMPLATE: SEARCH SIDEBAR FACETS
		facets without [more options]
	-->

	<xsl:template name="search_sidebar_facets">
		
		<xsl:if test="//facets/groups">
		
			<div class="box">
							
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
			
		</xsl:if>
	
	</xsl:template>	
		
</xsl:stylesheet>