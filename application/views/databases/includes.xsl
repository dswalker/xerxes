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

<xsl:template name="module_nav_display">
	<xsl:param name="url" />

	<xsl:if test="//request/session/user_admin">
		
		<li id="databases-edit">
			<a href="{$url}"><img src="images/edit.gif" alt="" /> Edit page</a>
		</li>		
		
	</xsl:if>

</xsl:template>

<xsl:template name="databases_alpha_listing">

	<div class="database-alpha-letters">
	
		<xsl:for-each select="database_alpha/object">
		
			<xsl:choose>
				<xsl:when test="//request/alpha = letter">
					<strong><xsl:value-of select="letter" /></strong>
				</xsl:when>
				<xsl:otherwise>
					<a href="{//request/controller}/alphabetical?alpha={letter}">
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

	<ul class="databases-list">
		
		<xsl:for-each select="databases/database">
	
			<li>
				<xsl:call-template name="database_brief_display" />
			</li>
	
		</xsl:for-each>
	
	</ul>

</xsl:template>

<xsl:template name="database_brief_display">

	<xsl:call-template name="database_brief_title" />
	<xsl:call-template name="database_brief_description" />
	
</xsl:template>

<xsl:template name="database_brief_title">

	<div class="database-title">
		<a href="databases/proxy?id={id}"><xsl:value-of select="title" /></a>
	</div>

</xsl:template>

<xsl:template name="database_brief_description">

	<div class="database-description">
		<xsl:choose>
			<xsl:when test="string-length(description) &gt; 300">
				<xsl:value-of select="substring(description, 1, 300)" /> . . .
			</xsl:when>
			<xsl:when test="description">
				<xsl:value-of select="description" />
			</xsl:when>
		</xsl:choose>
	</div>

	<div class="database-more-info">
		<a href="{//request/controller}/database/{id}">More information</a>
	</div>

</xsl:template>

<xsl:template name="librarian_details">

	<xsl:if test="email">
		<div>
			<dt><xsl:copy-of select="$text_databases_subject_librarian_email" />:</dt>
			<dd><xsl:value-of select="email" /></dd>
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

</xsl:stylesheet>
