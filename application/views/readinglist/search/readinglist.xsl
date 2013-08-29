<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Override summon results
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:template name="save_record">

		<xsl:variable name="source" select="source" />
		<xsl:variable name="record_id" select="record_id" />
		
		<!-- @todo: move this to the controller? -->
		
		<xsl:variable name="is_already_saved" select="//request/session/resultssaved[@key = $record_id]" />
	
		<div id="save-record-option-{$source}-{$record_id}" class="record-action save-record-action btn btn-primary">
								
			<xsl:text> </xsl:text>	
			
			<a id="link-{$source}-{$record_id}" href="{../url_save_delete}" style="color:#fff">
				
				<xsl:attribute name="class">save-record
				
					<!-- 'saved' class used as a tag by ajaxy stuff -->
					<xsl:if test="$is_already_saved">
						<xsl:text> saved</xsl:text>
					</xsl:if>
				
				</xsl:attribute>
							
				<xsl:choose>
					<xsl:when test="$is_already_saved">
						Saved
					</xsl:when>
					<xsl:otherwise>
						Add to reading list
					</xsl:otherwise>
				</xsl:choose>
			</a>
			
		</div>
			
	</xsl:template>
			
</xsl:stylesheet>
