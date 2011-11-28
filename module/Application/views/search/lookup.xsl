<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2010 California State University
 version: $Id: worldcat_lookup.xsl 1460 2010-10-26 21:00:20Z dwalker@calstate.edu $
 package: Worldcat
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="books.xsl" />

<xsl:output method="html" encoding="utf-8" indent="yes" doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>

<xsl:template match="/*">

	<xsl:for-each select="//xerxes_record">

		<xsl:call-template name="availability">
			<xsl:with-param name="type" select="//config/lookup_display" />
		</xsl:call-template>
		
	</xsl:for-each>
		
</xsl:template>

</xsl:stylesheet>
