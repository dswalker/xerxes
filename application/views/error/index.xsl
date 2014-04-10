<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Error display
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="main">

		<h1>
			<xsl:choose>
				<xsl:when test="error/type = 'access_denied'">
					<xsl:value-of select="$text_error_access_denied" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$text_error" />
				</xsl:otherwise>
			</xsl:choose>
		</h1>
		
		<p><xsl:value-of select="error/message" /></p>
		
		<xsl:if test="error/trace">
			<pre style="margin: 2em; padding: 2em; border: 1px solid #900; background-color: #FFC">
				<xsl:value-of select="error/trace" />
			</pre>
		</xsl:if>
	
</xsl:template>

</xsl:stylesheet>
