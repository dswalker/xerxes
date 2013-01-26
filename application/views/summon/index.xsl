<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Summon search home page view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../search/index.xsl" />


<xsl:template name="searchbox_hidden_fields_module">

	<xsl:for-each select="//config/preselected_facets/facet">
		<input type="hidden" name="{@name}" value="{@value}" />
	</xsl:for-each>

</xsl:template>

</xsl:stylesheet>
