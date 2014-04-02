<?xml version="1.0" encoding="UTF-8"?>

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

<xsl:import href="../includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	Databases!
</xsl:template>

<xsl:template name="main">

		<a id="facet-more-link-{group_id}" href="#database-modal-add-category" role="button" class="btn btn-small facet-more-launch" data-toggle="modal"> 
			<i class="icon-pencil"></i> Edit
		</a>

		<h1>Databases</h1>
			
		<div class="databases-categories-list">
		
			<ul>
				<xsl:for-each select="categories/category">
					<li><xsl:value-of select="name"	/></li>
				</xsl:for-each>
			</ul>
		
		</div>
		
		<xsl:call-template name="database_category_add" />
		
	
</xsl:template>

<xsl:template name="database_category_add">

	<div id="database-modal-add-category" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="database-modal-add-category-label" aria-hidden="true">
	
		<form action="{//request/controller}/addcategory">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h3 id="database-modal-add-category-label">Add Category</h3>
			</div>
			<div class="modal-body">
	
				<div class="reading-group">
					<label class="database-label" for="database-input-title">Category</label>
					<div class="database-input">
						<textarea name="name" id="database-input-title" style="width: 500px">
						</textarea>
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

</xsl:stylesheet>
