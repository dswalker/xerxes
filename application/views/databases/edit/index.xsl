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

 Edit: Databases home page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../index.xsl" />
<xsl:import href="includes.xsl" />

<xsl:template name="module_nav">

	<xsl:call-template name="module_nav_display">
		<xsl:with-param name="url">databases</xsl:with-param>
	</xsl:call-template>

</xsl:template>

<xsl:template name="category_link">

	<a href="{//request/controller}/subject?id={id}"><xsl:value-of select="name" /></a>

</xsl:template>

<xsl:template name="databases_edit">

	<div class="navbar navbar-inverse navbar-fixed-bottom databases-edit">
		<div class="navbar-inner">
			<div class="container">
				<div class="nav-collapse collapse">
					<ul class="nav">
						<li>
							<a id="facet-more-link-{group_id}" href="#database-modal-add-category" role="button" data-toggle="modal"> 
								<i class="icon-plus"></i>
								&nbsp;<xsl:text> Add Category</xsl:text>
							</a>							
						</li>
						<li>
							<a id="facet-more-link-{group_id}" href="{//request/controller}/edit-database" role="button" data-toggle="modal"> 
								<i class="icon-plus"></i>&nbsp; Add Database
							</a>							
						</li>
						<li>
							<a id="facet-more-link-{group_id}" href="{//request/controller}/edit-librarian" role="button" data-toggle="modal"> 
								<i class="icon-plus"></i>&nbsp; Add Librarian
							</a>							
						</li>
						<li>
							<a id="facet-more-link-{group_id}" href="{//request/controller}/librarians" role="button" data-toggle="modal"> 
								<i class="icon-user"></i>&nbsp; Show all Librarians
							</a>							
						</li>
					</ul>
				</div>		  
				
			</div>
		</div>
	</div>

	<div id="database-modal-add-category" class="modal hide fade" tabindex="-1" role="dialog" 
		aria-labelledby="database-modal-add-category-label" aria-hidden="true">
	
		<form action="{//request/controller}/add-category">
			<input type="hidden" name="return" value="{//request/server/request_uri}" />

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h3 id="database-modal-add-category-label">Add Category</h3>
			</div>
			<div class="modal-body">
	
				<div class="reading-group">
					<label class="database-label" for="database-input-title">Name</label>
					<div class="database-input">
						<input type="text" name="name" id="database-input-title" style="width: 400px" />
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><xsl:value-of select="$text_facets_submit" /></button>
				<button class="btn" data-dismiss="modal" aria-hidden="true"><xsl:value-of select="$text_facets_close" /></button>
			</div>
		</form>	
	
	</div>


</xsl:template>

</xsl:stylesheet>
