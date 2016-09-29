<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Search home page view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../search/advanced.xsl" />

<xsl:template name="boolean_select">
	<xsl:param name="position" />
	<xsl:param name="boolean" />

	<label class="ada" for="boolean{$position}"><xsl:value-of select="$text_searchbox_ada_boolean" /></label>

	<select id="boolean{$position}" name="boolean{$position}" class="advanced-boolean">
		<option value="">
			<xsl:if test="$boolean = 'AND'">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:copy-of select="$text_searchbox_boolean_and" />
		</option>
	</select>

</xsl:template>

</xsl:stylesheet>
