<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Hits view
 author: David Walker <dwalker@calstate.edu>
 
-->
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
  <xsl:include href="../includes.xsl" />  
  
<xsl:output method="html" />

<xsl:template match="/*">
<xsl:text>(</xsl:text><xsl:value-of select="hits" /><xsl:text>)</xsl:text>
</xsl:template>
</xsl:stylesheet> 
