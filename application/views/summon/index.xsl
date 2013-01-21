<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2012 California State University
 version:
 package: Worldcat
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
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
