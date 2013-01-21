<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2012 California State University
 version:
 package:
 link: http://xerxes.calstate.edu
 license: 
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:template name="availability">
	<xsl:call-template name="worldcat_results_availability" />
</xsl:template>	

<xsl:template name="worldcat_results_availability">

	<div class="record-action">
		<a href="{../url_open}">
		<xsl:choose>
			<xsl:when test="//config/worldcat_groups/group[@id=//request/source]/lookup/ill_text">

				<img src="{$base_url}/images/ill.gif" alt=""/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="//config/worldcat_groups/group[@id=//request/source]/lookup/ill_text" />					

			</xsl:when>
			<xsl:otherwise>
			
				<img src="{$image_sfx}" alt="" border="0" class="mini-icon link-resolver-link "/>
				<xsl:text> </xsl:text>
				<xsl:copy-of select="$text_link_resolver_check" /> 

			</xsl:otherwise>
		</xsl:choose>
		</a>
	</div>

</xsl:template>
		
</xsl:stylesheet>
