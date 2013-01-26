<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Search lookup view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="results.xsl" />
<xsl:import href="books.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">

	<xsl:for-each select="//xerxes_record">

		<xsl:call-template name="availability">
			<xsl:with-param name="type" select="//config/lookup_display" />
		</xsl:call-template>
		
	</xsl:for-each>
		
</xsl:template>

</xsl:stylesheet>
