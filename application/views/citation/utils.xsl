<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Citation utility templates
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
<xsl:output method="text" encoding="utf-8"/>

<xsl:template name="author">
	<xsl:param name="type" />
	
	<xsl:choose>
		<xsl:when test="name">
			<xsl:value-of select="name" /><xsl:text> </xsl:text>
		</xsl:when>
		<xsl:when test="aulast and $type = 'last'">
			<xsl:value-of select="aulast" /><xsl:text>, </xsl:text>
			<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
			<xsl:value-of select="auinit" /><xsl:text> </xsl:text>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
			<xsl:value-of select="auinit" /><xsl:text> </xsl:text>
			<xsl:value-of select="aulast" /><xsl:text> </xsl:text>		
		</xsl:otherwise>
	</xsl:choose>


</xsl:template>

<xsl:template name="fulltext">
	<xsl:param name="rewrite" />
	<xsl:param name="type" />
	<xsl:param name="id" />
	<xsl:param name="base_url" />
	<xsl:param name="folder" />
	
	<xsl:choose>
		<xsl:when test="$type = 'html'">			
			<xsl:value-of select="$base_url" />/ph/<xsl:value-of select="$id" />
		</xsl:when>
			
		<xsl:when test="$type = 'pdf'">
			<xsl:value-of select="$base_url" />/pp/<xsl:value-of select="$id" />	
		</xsl:when>
			
		<xsl:when test="$type = 'online'">
			<xsl:value-of select="$base_url" />/pf/<xsl:value-of select="$id" />
		</xsl:when>

		<xsl:when test="$type = 'construct'">
			<xsl:value-of select="$base_url" />/pc/<xsl:value-of select="$id" />
		</xsl:when>
		
		<xsl:when test="$type = 'openurl'">
			<xsl:value-of select="$base_url" />/r/<xsl:value-of select="$id" />
		</xsl:when>
	</xsl:choose>


</xsl:template>

</xsl:stylesheet>