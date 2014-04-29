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

 Databases search page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<a href="{//request/controller}">Databases</a>
	<xsl:value-of select="$text_breadcrumb_separator" />
	<xsl:value-of select="categories/name" />
</xsl:template>

<xsl:template name="module_nav">

	<xsl:call-template name="module_nav_display">
		<xsl:with-param name="url">databases-edit/subject?id=<xsl:value-of select="categories/id" /></xsl:with-param>
	</xsl:call-template>

</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="subject_databases_list" />
				
</xsl:template>

<xsl:template name="subject_databases_list">
	<xsl:param name="show_only_subcategory" select="false()" />
	
	<h1>
		<xsl:call-template name="category_name" />
	</h1>

	<div id="subject-list">
		
		<xsl:variable name="category" select="categories/normalized" />
		
		<ul data-target="databases-edit/reorder-subcategories" data-category="{$category}">
		
		<xsl:for-each select="categories/subcategories/subcategory">
	
			<li id="subcategory_{id}" class="subcategory list-item">
			
				<xsl:call-template name="subcategory_actions" />
			
				<h2>
					<xsl:call-template name="subcategory_name" />
				</h2>
				
				<ul class="databases-list" data-target="databases-edit/reorder-databases" data-category="{$category}" data-subcategory="{id}">
				
					<xsl:for-each select="database_sequences/database_sequence/database">
					
						<!-- sequence id -->
					
						<li id="database_{../id}" class="list-item"> 
							<xsl:call-template name="database_sequence_actions" />
							<xsl:call-template name="database_brief_display" />
						</li>
						
					</xsl:for-each>
					
				</ul>
				
			</li>
	
		</xsl:for-each>
		
		</ul>
	
	</div>
	
</xsl:template>

<xsl:template name="sidebar">

	<xsl:if test="categories/librarians">
		<h2>Subject Specialist</h2>
	</xsl:if>
	
	<xsl:call-template name="librarian_assign" />

</xsl:template>

<xsl:template name="category_name">

	<xsl:value-of select="categories/name" />
	
</xsl:template>

<xsl:template name="subcategory_name">

	<xsl:value-of select="name" />

</xsl:template>

<xsl:template name="subcategory_actions" />
<xsl:template name="database_sequence_actions" />
<xsl:template name="librarian_assign" />

</xsl:stylesheet>
