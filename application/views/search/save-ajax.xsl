<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of the Xerxes project.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Save ajax view
 author: Jonathan Rochkind
 
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
