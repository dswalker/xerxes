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

<xsl:import href="../includes.xsl" />
<xsl:import href="results.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="module_header">
	<style type="text/css">
	
		.control-group {
			clear: left;
		}
		
		.control-label {
			float: left;
			text-align: right;
			padding-right: 15px;
			width: 300px;
		}
		
		.control-input {
			width: 400px;
		}
		
		.control-submit {
			padding-left: 315px;
		}
		
	</style>
</xsl:template>


<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<xsl:value-of select="$text_search_module" />
</xsl:template>

<xsl:template name="main">

		<h1>Advanced Search</h1>
		
		<form action="{//controller}/search">
		
			<input type="hidden" name="advanced" value="true" />
			
			<xsl:for-each select="//query/terms/term">
			
				<xsl:call-template name="advanced_search_pair" />
				
			</xsl:for-each>
			
			
			<xsl:for-each select="config/advanced_search_fields/limit">
			
				<xsl:variable name="id" select="@id" />

				<div class="control-group">
					<label class="control-label" for="limit-{@id}"><xsl:value-of select="@public" />:</label>
						
						<div class="controls">
						
							<xsl:choose>
								<xsl:when test="@type = 'date'">
								
									<input type="text" name="facet.{//config/facet_fields/facet[@type='date']/@internal}.start" id="" value="" size="4" />
									&#8212;
									<input type="text" name="facet.{//config/facet_fields/facet[@type='date']/@internal}.end" id="" value="" size="4" />
									
								</xsl:when>
								<xsl:when test="@id">
								
									<xsl:for-each select="//limits/groups/group[name = $id]">
								
										<select name="facet.{name}">
											<option value=""><xsl:value-of select="$text_facets_multiple_any" /></option>
											<xsl:for-each select="facets/facet">
												<option value="{name}"><xsl:value-of select="name" /></option>
											</xsl:for-each>
										</select>
										
									</xsl:for-each>
									
								</xsl:when>					
								<xsl:otherwise>
									
								</xsl:otherwise>
							</xsl:choose>
						
						</div>		
				</div>
				
			</xsl:for-each>
			
			<div class="control-submit">
				<input type="submit" class="facets-submit{$language_suffix}">
					<xsl:attribute name="value"><xsl:value-of select="$text_facets_submit" /></xsl:attribute>
				</input>
			</div>
		
		</form>
	
</xsl:template>

</xsl:stylesheet>
