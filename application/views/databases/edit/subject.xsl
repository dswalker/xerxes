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

 Edit: Databases search page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../subject.xsl" />
<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="module_header">

	<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet"/>
	
	<xsl:call-template name="databases_css" />

	<style type="text/css">
		
		.list-item {
			padding: .5em;
			border: 1px solid #fff;
			max-width: 650px;
		}
		.list-highlight { 
			border: 1px solid #ccc; 
		}
		.list-item-action-menu {
			position: relative; 
			visibility: hidden;
		}
		.list-item-action-full {
			background-color: #eee; 
			padding: 10px; 
			margin: -6px; 
			margin-bottom: 10px;
		}
		.list-item-action {
			margin-bottom: -40px;
		}
		.list-item-buttons {
			position: absolute; 
			top: 3px; 
			right: 10px;
		}
		
		.sidebar .list-item-action {
			margin-bottom: -20px;
			text-align: right;
		}
					
	</style>

	<!-- don't show any database info -->

	<xsl:if test="//request/session/display_databases = 0">
		<style type="text/css">
			.databases-list {
				display: none;
			}
		</style>
	</xsl:if>

	<!-- don't show description -->

	<xsl:if test="//request/session/display_database_descriptions = 0">
		<style type="text/css">
			.database-description, .database-more-info {
				display: none;
			}
		</style>
	</xsl:if>
	
</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="subject_databases_list" />
	
	<xsl:call-template name="databases_edit" />
				
</xsl:template>

<xsl:template name="databases_edit">
		
	<div class="navbar navbar-inverse navbar-fixed-bottom databases-edit">
		<div class="navbar-inner">
			<div class="container">
				<div class="nav-collapse collapse">
				
					<ul class="nav" style="width:100%">
						<li style="float:right">
							<a id="delete-category" class="delete-confirm" href="{//request/controller}/delete-category?id={category/id}" 
								style="background-color:#400; border: 1px solid #efefef; border-top: 0px" role="button"> 
								<i class="icon-trash"></i>&nbsp; Delete Category
							</a>							
						</li>					
						<li>
							<a id="facet-more-link-{group_id}" href="#database-modal-add-subcategory" 
								role="button" data-toggle="modal"> 
								<i class="icon-plus"></i>&nbsp; Add Subcategory
							</a>							
						</li>
						<li>
							<xsl:choose>
								<xsl:when test="//request/session/display_databases = 0">
									<a id="facet-more-link-{group_id}" href="{//request/controller}/show-database-descriptions?database=on;return={php:function('urlencode', string(//request/server/request_uri))}" role="button"> 
										<i class="icon-collapse-top"></i>&nbsp; Show databases
									</a>
								</xsl:when>
								<xsl:otherwise>
									<a id="facet-more-link-{group_id}" href="{//request/controller}/show-database-descriptions?database=off;return={php:function('urlencode', string(//request/server/request_uri))}" role="button"> 
										<i class="icon-collapse"></i>&nbsp; Hide databases
									</a>
								</xsl:otherwise>
							</xsl:choose>						
						</li>
						<li>
							<xsl:choose>
								<xsl:when test="//request/session/display_databases = 0">
									<!-- don't show this option since databases are hidden -->
								</xsl:when>
								<xsl:when test="//request/session/display_database_descriptions = 0">
									<a id="facet-more-link-{group_id}" href="{//request/controller}/show-database-descriptions?description=on;return={php:function('urlencode', string(//request/server/request_uri))}" role="button"> 
										<i class="icon-collapse-top"></i>&nbsp; Show database descriptions
									</a>
								</xsl:when>
								<xsl:otherwise>
									<a id="facet-more-link-{group_id}" href="{//request/controller}/show-database-descriptions?description=off;return={php:function('urlencode', string(//request/server/request_uri))}" role="button"> 
										<i class="icon-collapse"></i>&nbsp; Hide database descriptions
									</a>	
								</xsl:otherwise>
							</xsl:choose>						
						</li>
						<xsl:call-template name="databases_subject_local_action" />
					</ul>
					
				</div>
			</div>
		</div>
	</div>
	
	<div id="database-modal-add-subcategory" class="modal hide fade" tabindex="-1" role="dialog" 
		aria-labelledby="database-modal-add-subcategory-label" aria-hidden="true">
	
		<form action="{//request/controller}/add-subcategory">
			<input type="hidden" name="category" value="{category/id}" />

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h3 id="database-modal-add-subcategory-label">Add Subcategory</h3>
			</div>
			<div class="modal-body">
	
				<div class="reading-group">
					<label class="database-label" for="subcategory-input-name">Name</label>
					<div class="database-input">
						<input type="text" name="subcategory" id="subcategory-input-name" style="width: 400px" />
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><xsl:value-of select="$text_facets_submit" /></button>
				<button class="btn" data-dismiss="modal" aria-hidden="true"><xsl:value-of select="$text_facets_close" /></button>
			</div>
		</form>	
	
	</div>
	
	<xsl:for-each select="category/subcategories/subcategory">
	
		<div id="database-modal-asign-databases-{id}" class="modal hide fade" tabindex="-1" role="dialog" 
			aria-labelledby="database-modal-assign-databases-label-{id}" aria-hidden="true">
		
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h3 id="database-modal-assign-databases-label-{id}">Assign Databases</h3>
					<form id="live-search" action="" class="styled" method="post">
						<fieldset>
							<input type="text" class="text-input filter" value="" />
							<span id="filter-count"></span>
						</fieldset>
					</form>
				</div>
				
				<form action="{//request/controller}/assign-databases">
					<input type="hidden" name="category" value="{//category/id}" />
					<input type="hidden" name="subcategory" value="{id}" />
				
					<div class="modal-body">
			
						<xsl:call-template name="database_title_table" />
						
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary"><xsl:value-of select="$text_facets_submit" /></button>
						<button class="btn" data-dismiss="modal" aria-hidden="true"><xsl:value-of select="$text_facets_close" /></button>
					</div>
					
				</form>	
		
		</div>
		
	</xsl:for-each>
	
	<div id="database-modal-add-librarian" class="modal hide fade" tabindex="-1" role="dialog" 
		aria-labelledby="database-modal-add-librarian-label" aria-hidden="true">
	
		<form action="{//request/controller}/assign-librarian">
			<input type="hidden" name="category" value="{category/id}" />

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h3 id="database-modal-add-librarian-label">Assign Librarians</h3>
			</div>
			<div class="modal-body">
	
				<xsl:call-template name="librarian_name_table" />
				
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><xsl:value-of select="$text_facets_submit" /></button>
				<button class="btn" data-dismiss="modal" aria-hidden="true"><xsl:value-of select="$text_facets_close" /></button>
			</div>
		</form>	
	
	</div>	
		
</xsl:template>

<xsl:template name="category_name">

	<a class="edit" href="#" id="category" 
		data-type="text" data-pk="{category/id}" data-url="{//request/controller}/edit-category" data-title="Enter category name">
		<xsl:value-of select="category/name" />
	</a>	

</xsl:template>

<xsl:template name="subcategory_name">

	<a class="edit" href="#" id="subcategory-{id}" 
		data-type="text" data-pk="{id}" data-url="{//request/controller}/edit-subcategory" data-title="Enter subcategory name">
		<xsl:value-of select="name" />
	</a>

</xsl:template>

<xsl:template name="subcategory_actions">
	
	<div class="list-item-action-menu list-item-action list-item-action-full">
					
		<img src="{$base_url}/images/famfamfam/arrow_out.png" alt="" />
		
		<div class="list-item-buttons">

			<a href="#database-modal-asign-databases-{id}" class="btn btn-small" role="button" data-toggle="modal">
				<i class="icon-plus"></i> Database
			</a>
			
			<xsl:text> </xsl:text>
			
			<xsl:choose>
				<xsl:when test="sidebar = 1">

					<a href="{//request/controller}/move-to-sidebar?subcategory={id};category={../../id};move=0" class="btn btn-small">
						<i class="icon-arrow-left"></i> Remove
					</a>
				
				</xsl:when>
				<xsl:otherwise>
			
					<a href="{//request/controller}/move-to-sidebar?subcategory={id};category={../../id};move=1" class="btn btn-small">
						<i class="icon-arrow-right"></i> Move to Sidebar
					</a>
					
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:text> </xsl:text>
			
			<a href="{//request/controller}/delete-subcategory?subcategory={id};category={../../id}" 
				class="btn btn-small subcategory-delete delete-confirm" data-source="subcategory_{id}">
				<i class="icon-trash"></i> Delete
			</a>			
			
		</div>
		
	</div>
	
</xsl:template>

<xsl:template name="database_sequence_actions">
	
	<div class="list-item-action-menu">
	
		<div style="position: absolute; top: -15px; right: -15px">
			<a href="{//request/controller}/delete-database-sequence?id={../id};category={//category/id}" 
				class="btn btn-small delete-fade" data-source="database_{../id}">
				<i class="icon-trash"></i> Remove
			</a>
		</div>
		
	</div>

</xsl:template>

<xsl:template name="database_title_table">

	<table class="facet-multi-table database-choice-list">
		<tr>
			<th>Include</th>
			<th>Database</th>
		</tr>

		<xsl:for-each select="//database_titles/database_title">
			<tr>
				<td class="facet-multi-selector">
					<input type="checkbox" id="database-select-{id}" class="facet-multi-option-include" name="database" value="{id}">
						<!--
						<xsl:if test="selected and ( not(is_excluded) or is_excluded != '1')">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
						-->
					</input>
				</td>
				<td>
					<xsl:value-of select="title" />
				</td>
			</tr>
		</xsl:for-each>
	</table>	

</xsl:template>

<xsl:template name="librarian_name_table">

	<table class="facet-multi-table librarian-choice-list">
		<tr>
			<th>Assign</th>
			<th>Librarian</th>
		</tr>

		<xsl:for-each select="//librarian_names/librarian_name">
			<xsl:choose>
				<xsl:when test="id = //category/librarian_sequences/librarian_sequence/librarian/id">
					<!-- this librarian is already added, so do nothing -->
				</xsl:when>
				<xsl:otherwise>
		
					<tr>
						<td class="facet-multi-selector">
							<input type="checkbox" id="librarian-select-{id}" class="facet-multi-option-include" name="librarian" value="{id}">
								<xsl:if test="id = //category/librarian_sequences/librarian_sequence/librarian/id">
									<xsl:attribute name="checked">checked</xsl:attribute>
								</xsl:if>
							</input>
						</td>
						<td>
							<xsl:value-of select="name" />
						</td>
					</tr>
					
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</table>	

</xsl:template>

<xsl:template name="librarian_assign">
	<div style="margin-bottom: 3em">
		<a href="#database-modal-add-librarian" class="btn" role="button" data-toggle="modal">
			<i class="icon-plus"></i> Add Librarian
		</a>
	</div>
</xsl:template>

<xsl:template name="librarian_edit_actions">
	&nbsp;
	<a href="{//request/controller}/delete-librarian-sequence?id={../id};category={//category/id}" class="btn btn-small" role="button" data-toggle="modal">
		<i class="icon-trash"></i>
	</a>
	
</xsl:template>

<xsl:template name="databases_subject_local_action" />

</xsl:stylesheet>
