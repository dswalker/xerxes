<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2009 California State University
 version: $Id: utils.xsl 1513 2010-11-23 22:35:35Z dwalker@calstate.edu $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
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
		<xsl:when test="$rewrite = 'true'">
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
		</xsl:when>
		<xsl:otherwise>
			<xsl:choose>	
				<xsl:when test="$type = 'html'">			
					<xsl:value-of select="$base_url" />/?<xsl:value-of select="$language_param"/>&amp;base=folder&amp;action=redirect&amp;type=html&amp;id=<xsl:value-of select="$id" />
				</xsl:when>
					
				<xsl:when test="$type = 'pdf'">
					<xsl:value-of select="$base_url" />/?<xsl:value-of select="$language_param"/>&amp;base=folder&amp;action=redirect&amp;type=pdf&amp;id=<xsl:value-of select="$id" />	
				</xsl:when>	
					
				<xsl:when test="$type = 'online'">
					<xsl:value-of select="$base_url" />/?<xsl:value-of select="$language_param"/>&amp;base=folder&amp;action=redirect&amp;type=fulltext&amp;id=<xsl:value-of select="$id" />
				</xsl:when>

				<xsl:when test="$type = 'construct'">
					<xsl:value-of select="$base_url" />/?<xsl:value-of select="$language_param"/>&amp;base=folder&amp;action=redirect&amp;type=construct&amp;id=<xsl:value-of select="$id" />
				</xsl:when>
			
				<xsl:when test="$type = 'openurl'">
					<xsl:value-of select="$base_url" />/?<xsl:value-of select="$language_param"/>&amp;base=folder&amp;action=redirect&amp;type=openurl&amp;id=<xsl:value-of select="$id" />
				</xsl:when>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

</xsl:stylesheet>