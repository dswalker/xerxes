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

 Search home page view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../databases/subject.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="module_header">

	<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet"/>

	<style type="text/css">
		
		.list-item {
			padding: .5em;
			border: 1px solid #fff;
			max-width: 650px;
		}
		.list-highlight { 
			border: 1px solid #ccc; 
		}
		.list-item-action {
			background-color: #eee; 
			padding: 10px; 
			margin: -6px; 
			margin-bottom: 10px;
			position: relative; 
			visibility: hidden;
		}

		.list-item-action {
			margin-bottom: -20px;
		}
					
	</style>
	
</xsl:template>


<xsl:template name="module_javascript">
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"  type="text/javascript"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap-editable/js/bootstrap-editable.min.js"></script>
	<script src="{$base_include}/javascript/databases.js"  type="text/javascript"></script>
	
	<script type="text/javascript">
		$.fn.editable.defaults.mode = 'inline';	
		$(document).ready(function() {
			$('.edit').editable();
		});
	</script>	
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<a href="databases">Databases</a>
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
				
					<ul class="nav">
						<li>
							<a id="delete-category" href="#" role="button"> 
								<i class="icon-trash"></i>&nbsp; Delete Category
							</a>							
						</li>					
						<li>
							<a id="facet-more-link-{group_id}" href="#database-modal-add-subcategory" role="button" data-toggle="modal"> 
								<i class="icon-plus"></i>&nbsp; Add Subcategory
							</a>							
						</li>
					</ul>
					
				</div>
			</div>
		</div>
	</div>
	
	<div id="database-modal-add-subcategory" class="modal hide fade" tabindex="-1" role="dialog" 
		aria-labelledby="database-modal-add-subcategory-label" aria-hidden="true">
	
		<form action="{//request/controller}/add-subcategory">
			<input type="hidden" name="return" value="{//request/server/request_uri}" />
			<input type="hidden" name="category" value="{categories/normalized}" />

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
				<button class="btn" data-dismiss="modal" aria-hidden="true"><xsl:value-of select="$text_facets_close" /></button>
				<button class="btn btn-primary"><xsl:value-of select="$text_facets_submit" /></button>
			</div>
		</form>	
	
	</div>	
		
</xsl:template>

<xsl:template name="category_name">

	<a class="edit" href="#" id="category" 
		data-type="text" data-pk="{categories/id}" data-url="{//request/controller}/edit-category" data-title="Enter category name">
		<xsl:value-of select="categories/name" />
	</a>	

</xsl:template>

<xsl:template name="subcategory_name">

	<a class="edit" href="#" id="subcategory-{id}" 
		data-type="text" data-pk="{id}" data-url="{//request/controller}/edit-subcategory" data-title="Enter subcategory name">
		<xsl:value-of select="name" />
	</a>

</xsl:template>

<xsl:template name="subcategory_actions">
	
	<div class="list-item-action">
					
		<img src="{$base_url}/images/famfamfam/arrow_out.png" alt="" />
		
		<div style="position: absolute; top: 3px; right: 10px">
			<a href="{//request/controller}/delete-subcategory?subcategory={id};category={../../id}" class="btn btn-small subcategory-delete">
				<i class="icon-trash"></i> Remove
			</a>
			<xsl:text> </xsl:text>
			<a href="#" class="btn btn-small">
				<i class="icon-plus"></i> Database
			</a>
		</div>
		
	</div>
	
</xsl:template>

</xsl:stylesheet>
