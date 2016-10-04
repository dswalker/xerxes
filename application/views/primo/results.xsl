<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Primo results view
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:import href="../includes.xsl" />
	<xsl:import href="../search/results.xsl" />

	<xsl:output method="html" />
	
	<xsl:template match="/*">
		<xsl:call-template name="surround">
			<xsl:with-param name="surround_template">none</xsl:with-param>
			<xsl:with-param name="sidebar">none</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template name="breadcrumb">
		<!-- TODO: FIX THIS ?   <xsl:call-template name="breadcrumb_worldcat" /> -->
		<xsl:call-template name="page_name" />
	</xsl:template>
	
	<xsl:template name="page_name">
		<xsl:value-of select="$text_search_results" />
	</xsl:template>
	
	<xsl:template name="title">
		<xsl:value-of select="//request/query" />
	</xsl:template>
	
	<xsl:template name="main">
		<xsl:call-template name="search_page" />
	</xsl:template>


	<xsl:template name="facet_narrow_results">

		<h3>Refine your search</h3>
	
		<xsl:variable name="peer">
			<xsl:if test="//request/*[@original_key = 'facet.IsPeerReviewed']">
				<xsl:text>true</xsl:text>
			</xsl:if>		
		</xsl:variable>
	
		<xsl:variable name="fulltext">
			<xsl:if test="//request/*[@original_key = 'facet.IsFullText'] = 'false'">
				<xsl:text>false</xsl:text>
			</xsl:if>
			<xsl:if test="//request/*[@original_key = 'facet.IsFullText'] = 'true'">
				<xsl:text>true</xsl:text>
			</xsl:if>
		</xsl:variable>
	
		<xsl:variable name="newspapers">
			<xsl:value-of select="//request/*[@original_key = 'facet.newspapers']" />
		</xsl:variable>
			
		<form id="form-facet-0" action="{//request/controller}/search" method="get">
			<input name="lang" type="hidden" value="{//request/lang}" />
			<xsl:call-template name="hidden_search_inputs">
				<xsl:with-param name="exclude_limit">facet.IsPeerReviewed,facet.IsFullText,facet.holdings,facet.newspapers</xsl:with-param>
			</xsl:call-template>
			
			<ul>			
		
				<li class="facet-selection">
				
					<input type="checkbox" id="facet-0-2" class="facet-selection-option facet-0" name="facet.IsPeerReviewed" value="true">
						<xsl:if test="$peer = 'true'">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:text> </xsl:text>
					<label for="facet-0-2"><xsl:copy-of select="$text_summon_facets_scholarly" /></label>
				
				</li>
				
				<xsl:choose>
					<xsl:when test="//config/limit_to_holdings = 'false'">
			
						<li class="facet-selection">
						
							<input type="checkbox" id="facet-0-3" class="facet-selection-option facet-0" name="facet.IsFullText" value="true">
								<xsl:if test="$fulltext = 'true'">
									<xsl:attribute name="checked">checked</xsl:attribute>
								</xsl:if>
							</input>
							<xsl:text> </xsl:text>
							<label for="facet-0-3"><xsl:copy-of select="$text_summon_facets_fulltext" /></label>
						
						</li>
						
					</xsl:when>
					<xsl:otherwise>
					
						<li class="facet-selection">
						
							<input type="checkbox" id="facet-0-3" class="facet-selection-option facet-0" name="facet.IsFullText" value="false">
								<xsl:if test="$fulltext = 'false'">
									<xsl:attribute name="checked">checked</xsl:attribute>
								</xsl:if>
							</input>
							<xsl:text> </xsl:text>
							<label for="facet-0-3"><xsl:copy-of select="$text_summon_facets_beyond_holdings" /></label>
						
						</li>
					
					</xsl:otherwise>
				</xsl:choose>			

				<xsl:if test="//config/newspapers_optional = 'true'">
		
					<li class="facet-selection">
					
						<input type="checkbox" id="facet-0-4" class="facet-selection-option facet-0" name="facet.newspapers" value="true">
							<xsl:if test="$newspapers = 'true'">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
						<xsl:text> </xsl:text>
						<label for="facet-0-4"><xsl:copy-of select="$text_summon_facets_newspaper_add" /></label>
					
					</li>
					
				</xsl:if>
	
				<xsl:if test="//config/newspapers_optional = 'exclude'">
		
					<li class="facet-selection">
					
						<input type="checkbox" id="facet-0-5" class="facet-selection-option facet-0" name="facet.newspapers" value="false">
							<xsl:if test="$newspapers = 'false'">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
						<xsl:text> </xsl:text>
						<label for="facet-0-5"><xsl:copy-of select="$text_summon_facets_newspaper_exclude" /></label>
					
					</li>
					
				</xsl:if>

			</ul>
			
			<xsl:call-template name="facet_noscript_submit" />
		
		</form>
	
	</xsl:template>
	
	<xsl:template name="spell_suggest">
	
		<xsl:if test="results/message = 'search.message.ui.expansion.pc'">
			<div class="alert alert-info" role="alert" style="display: inline-table; margin-bottom: 35px">
				Your initial search resulted in few or no results. The results below were found by expanding your search.
			</div>
		</xsl:if>
	
	</xsl:template>
		
</xsl:stylesheet>
