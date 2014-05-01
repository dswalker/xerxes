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

<xsl:template name="database_brief_display">

	<xsl:call-template name="database_brief_title" />
	<xsl:call-template name="database_brief_description" />
	
</xsl:template>

<xsl:template name="database_brief_title">

	<div class="database-title">
		<a href="{link}"><xsl:value-of select="title" /></a>
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

</xsl:stylesheet>
