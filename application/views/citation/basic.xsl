<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Xerxes Record to basic text
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
<xsl:include href="utils.xsl" />
<xsl:include href="../includes.xsl" />
<xsl:output method="text" encoding="utf-8"/>

<xsl:template match="/*">

	<xsl:for-each select="//xerxes_record">
    <xsl:variable name="metalib_db_id" select="metalib_id" />

	
		<xsl:if test="title_normalized">
			<xsl:text>Title: </xsl:text>
			<xsl:value-of select="title_normalized" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="format">
			<xsl:text>Format: </xsl:text>
			<xsl:choose>
				<xsl:when test="format/public">
					<xsl:value-of select="format/public" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="format" />
				</xsl:otherwise>
			</xsl:choose>
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="authors/author">
			<xsl:text>Authors: </xsl:text>
			<xsl:for-each select="authors/author">
				<xsl:call-template name="author"><xsl:with-param name="type" value="last" /></xsl:call-template>
				<xsl:if test="following-sibling::author">
					<xsl:text>; </xsl:text>
				</xsl:if>
			</xsl:for-each>
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="journal">
			<xsl:text>Original Citation: </xsl:text>
			<xsl:value-of select="journal" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="journal_title">
			<xsl:text>Journal Title: </xsl:text>
			<xsl:value-of select="journal_title" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="volume">
			<xsl:text>Volume: </xsl:text>
			<xsl:value-of select="volume" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="issue">
			<xsl:text>Issue: </xsl:text>
			<xsl:value-of select="issue" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="start_page">
			<xsl:text>Start Page: </xsl:text>
			<xsl:value-of select="start_page" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="end_page">
			<xsl:text>End Page: </xsl:text>
			<xsl:value-of select="end_page" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="place">
			<xsl:text>Place: </xsl:text>
			<xsl:value-of select="place" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="publisher">
			<xsl:text>Publisher: </xsl:text>
			<xsl:value-of select="publisher" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="year">
			<xsl:text>Year: </xsl:text>
			<xsl:value-of select="year" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="abstract">
			<xsl:text>Summary: </xsl:text>
			<xsl:value-of select="abstract" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="subjects/subject">
			<xsl:text>Subjects: </xsl:text>
			<xsl:for-each select="subjects/subject">
				<xsl:value-of select="display|text()" />
				<xsl:if test="following-sibling::subject">
					<xsl:text>; </xsl:text>
				</xsl:if>
			</xsl:for-each>
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>	
		<xsl:if test="language">
			<xsl:text>Language: </xsl:text>
			<xsl:value-of select="language" />
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		<xsl:if test="note">
			<xsl:text>Notes: </xsl:text>
			<xsl:for-each select="note">
				<xsl:value-of select="text()" />
				<xsl:if test="following-sibling::subject">
					<xsl:text>; </xsl:text>
				</xsl:if>
			</xsl:for-each>
			<xsl:text>&#013;&#010;</xsl:text>
		</xsl:if>
		
		<xsl:if test="items/item">
			<xsl:text>Items: </xsl:text>
			<xsl:text>&#013;&#010;</xsl:text>
			
			<xsl:for-each select="items/item">
				<xsl:text>&#032;&#032;* </xsl:text> 
				<xsl:value-of select="location" />: <xsl:value-of select="callnumber" />
				<xsl:text>&#013;&#010;</xsl:text>
			</xsl:for-each>
		</xsl:if>

		<xsl:text>Link: </xsl:text><xsl:value-of select="$base_url" />/folder/record?id=<xsl:value-of select="../id" />
		
		<!-- full-text -->
		
		<xsl:text>&#013;&#010;</xsl:text>
		<xsl:text>&#013;&#010;</xsl:text>
		<xsl:text>&#013;&#010;</xsl:text>
		
	</xsl:for-each>
	
</xsl:template>

</xsl:stylesheet>