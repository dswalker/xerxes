<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: Jonathan Rochkind
 copyright: 2009 Johns Hopkins University
 version: $Id: metasearch_save-delete_ajax.xsl 976 2009-11-02 14:22:56Z dwalker@calstate.edu $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
-->
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:output method="html" />
	
	<xsl:template match="/*">
	{
	<xsl:choose>
		<xsl:when test="delete = '1'">
			"deleted": true,
			"inserted": false
		</xsl:when>
		<xsl:otherwise>
			"deleted": false,
			"inserted": true,
			"savedRecordID": <xsl:value-of select="savedRecordID" />	
		</xsl:otherwise>
	</xsl:choose>
	}	
	</xsl:template>
</xsl:stylesheet> 
