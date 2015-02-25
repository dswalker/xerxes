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

 Databases home page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:template name="module_nav">

	<xsl:if test="//request/session/user_admin">
		
		<li id="databases-edit">
			<a href="{//edit_link}"><img src="images/edit.gif" alt="" /> Edit page</a>
		</li>		
		
	</xsl:if>

</xsl:template>

<xsl:template name="module_header">

	<xsl:call-template name="databases_css" />

</xsl:template>

<xsl:template name="databases_css">

	<link href="{$base_include}/css/databases.css?version={$asset_version}" rel="stylesheet" type="text/css" />

</xsl:template>

<xsl:template name="databases_alpha_listing">

	<div class="database-alpha-letters">
	
		<xsl:for-each select="database_alpha/object">
		
			<xsl:choose>
				<xsl:when test="//request/alpha = letter">
					<strong><xsl:value-of select="letter" /></strong>
				</xsl:when>
				<xsl:otherwise>
					<a href="{url}">
						<xsl:value-of select="letter" />
					</a>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="following-sibling::object">
				<span class="database-letter-seperator"><xsl:copy-of select="$text_databases_az_letter_separator" /></span>	
			</xsl:if>	
			
		</xsl:for-each>
	
	</div>

</xsl:template>

<xsl:template name="database_results">

	<xsl:choose>
		<xsl:when test="not(databases/database)">
		
			<p class="error"><xsl:value-of select="$text_databases_no_match" /></p>
		
		</xsl:when>
		<xsl:otherwise>
			
			<ul class="databases-list" data-role="listview" data-inset="true">
				
				<xsl:for-each select="databases/database">
					
					<li>
						<xsl:choose>
							<xsl:when test="$is_mobile = 1">
								<xsl:call-template name="database_mobile_display" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:call-template name="database_brief_display" />
							</xsl:otherwise>
						</xsl:choose>
						
					</li>
				</xsl:for-each>
			
			</ul>
			
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template name="database_brief_display">

	<xsl:call-template name="database_brief_title" />
	<xsl:call-template name="database_brief_description" />
	
</xsl:template>

<xsl:template name="database_mobile_display">

	<a href="{url_proxy}" data-ajax="false">
		<xsl:value-of select="title" />	
		<br />
		<xsl:call-template name="database_abstract" />							
	</a>

</xsl:template>

<xsl:template name="database_brief_title">

	<div class="database-title">
		<a href="{url_proxy}"><xsl:value-of select="title" /></a>
		<xsl:call-template name="database_brief_new" />
	</div>

</xsl:template>

<xsl:template name="database_brief_new">

	<xsl:if test="is_new">
		[ new database ]
	</xsl:if>

</xsl:template>

<xsl:template name="database_brief_description">

	<div class="database-description">
		<xsl:call-template name="database_abstract" />
	</div>

	<div class="database-more-info">
		<a href="{url}">More information <span class="ada">about <xsl:value-of select="title" /></span></a>
	</div>

</xsl:template>

<xsl:template name="database_abstract">
	
	<xsl:choose>
		<xsl:when test="string-length(description) &gt; 300">
			<xsl:value-of select="substring(description, 1, 300)" /> . . .
		</xsl:when>
		<xsl:when test="description">
			<xsl:value-of select="description" />
		</xsl:when>
	</xsl:choose>

</xsl:template>

<xsl:template name="librarian_details">

	<xsl:if test="email">
		<div>
			<dt><xsl:copy-of select="$text_databases_subject_librarian_email" />:</dt>
			<dd><a href="mailto:{email}"><xsl:value-of select="email" /></a></dd>
		</div>
	</xsl:if>
	
	<xsl:if test="phone">
		<div>
			<dt><xsl:copy-of select="$text_databases_subject_librarian_telephone" />:</dt>
			<dd><xsl:value-of select="phone" /></dd>
		</div>
	</xsl:if>
	
	<xsl:if test="office">
		<div>
			<dt><xsl:copy-of select="$text_databases_subject_librarian_office" />:</dt>
			<dd><xsl:value-of select="office" /></dd>
		</div>
	</xsl:if>
	
	<xsl:if test="office_hours">
		<div>
			<dt><xsl:copy-of select="$text_databases_subject_librarian_office_hours" />:</dt>
			<dd><xsl:value-of select="office_hours" /></dd>
		</div>
	</xsl:if>

</xsl:template>

<xsl:template name="breadcrumb_databases">

	<xsl:call-template name="breadcrumb_start" />

	<a href="{//request/controller}"><xsl:value-of select="$text_databases_category_pagename" /></a>

	<xsl:value-of select="$text_breadcrumb_separator" />
	
	<xsl:call-template name="breadcrumb_databases_intermediate" />

	<xsl:call-template name="page_name" />

</xsl:template>

<xsl:template name="breadcrumb_databases_intermediate" />


</xsl:stylesheet>
