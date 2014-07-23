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

<xsl:variable name="category" select="//category/normalized" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="category/name" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_databases" />
</xsl:template>

<xsl:template name="main">
	
	<xsl:call-template name="subject_databases_list" />
				
</xsl:template>

<xsl:template name="subject_databases_list">
	<xsl:param name="show_title">true</xsl:param>
	<xsl:param name="show_subcategories">true</xsl:param>
	<xsl:param name="show_database_description">true</xsl:param>
	<xsl:param name="show_individual_subcategory" />
	
	<xsl:if test="$show_title = 'true'">
		<h1>
			<xsl:call-template name="category_name" />
		</h1>
	</xsl:if>

	<xsl:if test="$show_subcategories = 'true'">

		<div class="subject-list">
			
			<ul data-target="{//request/controller}/reorder-subcategories" data-category="{$category}">
			
				<xsl:choose>
					<xsl:when test="$show_individual_subcategory != ''">

						<xsl:for-each select="category/subcategories/subcategory[id = $show_individual_subcategory or source_id = $show_individual_subcategory]">
						
							<xsl:call-template name="subject_subcategory">
								<xsl:with-param name="show_description" select="$show_database_description" />
							</xsl:call-template>
						
						</xsl:for-each>
					
					</xsl:when>
					<xsl:otherwise>
		
						<xsl:for-each select="category/subcategories/subcategory[not(sidebar) or sidebar = 0]">
						
							<xsl:call-template name="subject_subcategory">
								<xsl:with-param name="show_description" select="$show_database_description" />
							</xsl:call-template>
						
						</xsl:for-each>
					
					</xsl:otherwise>
				</xsl:choose>
			
			</ul>
		
		</div>
	
	</xsl:if>
	
</xsl:template>

<xsl:template name="subject_subcategory">
	<xsl:param name="show_description">true</xsl:param>
	
	<li id="subcategory_{id}" class="subcategory list-item">
	
		<xsl:call-template name="subcategory_actions" />
	
		<h2>
			<xsl:call-template name="subcategory_name" />
		</h2>
		
		<ul class="databases-list" data-target="{//request/controller}/reorder-databases" data-category="{$category}" data-subcategory="{id}"
			data-role="listview" data-inset="true">
		
			<xsl:for-each select="database_sequences/database_sequence/database">
			
				<!-- sequence id -->
			
				<li id="database_{../id}" class="list-item"> 
				
					<xsl:choose>
						<xsl:when test="$is_mobile = 1">
							<xsl:call-template name="database_mobile_display" />
						</xsl:when>
						<xsl:otherwise>
				
							<xsl:call-template name="database_sequence_actions" />
							
							<xsl:choose>
								<xsl:when test="$show_description = 'true'">
									<xsl:call-template name="database_brief_display" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:call-template name="database_brief_title" />
								</xsl:otherwise>
							</xsl:choose>
							
						</xsl:otherwise>
						
					</xsl:choose>
					
				</li>
				
			</xsl:for-each>
			
		</ul>
		
	</li>

</xsl:template>

<xsl:template name="sidebar">

	<xsl:if test="category/librarian_sequences">
	
		<div class="database-box">
	
			<h2>Subject Specialist</h2>
			
			<xsl:for-each select="category/librarian_sequences/librarian_sequence/librarian">
			
				<div class="librarian">
					
					<xsl:if test="image">
						<div class="librarian-image">
							<img src="{//request/controller}/librarian-image?id={id}" alt="{name}" />
						</div>
					</xsl:if>
					
					<h3>
						<xsl:choose>
							<xsl:when test="link">
								<a href="{link}"><xsl:value-of select="name" /></a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="name" />
							</xsl:otherwise>
						</xsl:choose>					
						<xsl:call-template name="librarian_edit_actions" />
					</h3>
					
					<dl>
						<xsl:call-template name="librarian_details" />
					</dl>
				</div>
				
			</xsl:for-each>
		
		</div>
		
	</xsl:if>
	
	<xsl:call-template name="librarian_assign" />
	
	<xsl:if test="category/subcategories/subcategory[sidebar = 1]">
	
		<div class="database-box subject-list">
	
			<ul data-target="{//request/controller}/reorder-subcategories" data-category="{$category}">
	
				<xsl:for-each select="category/subcategories/subcategory[sidebar = 1]">
					<xsl:call-template name="subject_subcategory">
						<xsl:with-param name="show_description">false</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
	
			</ul>
			
		</div>
		
	</xsl:if>
	
	<xsl:call-template name="subject_embed" />

</xsl:template>

<xsl:template name="subject_embed">

	<div class="database-box">
	
		<h2><xsl:value-of select="$text_header_embed" /></h2>
		<ul class="embed-list">
			<li><a href="{//url_embed}">Embed this page in an external website</a></li>
		</ul>
		
	</div>

</xsl:template>

<xsl:template name="category_name">

	<xsl:value-of select="category/name" />
	
</xsl:template>

<xsl:template name="subcategory_name">

	<xsl:value-of select="name" />

</xsl:template>

<xsl:template name="subcategory_actions" />
<xsl:template name="database_sequence_actions" />
<xsl:template name="librarian_assign" />
<xsl:template name="librarian_edit_actions" />

</xsl:stylesheet>
