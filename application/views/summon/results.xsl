<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Summon results view
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
	<xsl:call-template name="breadcrumb_search" />
	<xsl:value-of select="$text_search_results" />
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="//request/query" />
</xsl:template>

<xsl:template name="title">
	<xsl:call-template name="page_name" />
</xsl:template>

<xsl:template name="main">
	<xsl:call-template name="search_page" />
</xsl:template>

<xsl:template name="search_recommendations">

	<xsl:if test="not(request/start)">
	
		<xsl:if test="results/query_expansion">
		
			<div class="results-query-expansion">
			
				<xsl:value-of select="$text_summon_query_expansion_inlcude" />
				<xsl:text> "</xsl:text>
				<strong><xsl:value-of select="results/query_expansion/object" /></strong>
				<xsl:text>." </xsl:text>
				<xsl:value-of select="$text_summon_query_expansion_only_show" />
				<xsl:text> "</xsl:text>
				<a href="{query/url_dont_expand_query}"><xsl:value-of select="query/terms/term/query" /></a>."
			
			</div>
		
		</xsl:if>
	
		<xsl:if test="results/database_recommendations  and //config/show_database_recommendations = 'true'">
	
			<div class="results-database-recommendations">
			
				<h2>
					<xsl:copy-of select="$text_summon_recommendation" />
				</h2>
		
				<ul>
			
				<xsl:for-each select="results/database_recommendations/database_recommendation">
					
					<li>
						<a href="{link}"><xsl:value-of select="title" /></a>
						
						<xsl:if test="description">
							
							<p><xsl:value-of select="description" /></p>
						
						 </xsl:if>
	
					</li>
					
				</xsl:for-each>	
				
				</ul>
				
			</div>
			
		</xsl:if>
		
		<xsl:if test="results/best_bets and not(//config/best_bets = 'false')">
		
			<div class="results-database-recommendations">
			
				<xsl:for-each select="results/best_bets/best_bet">
					
					<div class="results-bestbet">
					
						<h2><a href="{link}"><xsl:value-of select="title" /></a></h2>
						
						<div class="description">
						
							<xsl:if test="description">
								<xsl:value-of disable-output-escaping="yes" select="description" />
							</xsl:if>
							
						</div>
					
					</div>
					
				</xsl:for-each>
				
			</div>
			
		</xsl:if>
		
	</xsl:if>
	
</xsl:template>

<xsl:template name="brief_result_info_cover">

	<xsl:if test="//config[@source='summon']/client_id != ''">

		<div class="cover" style="float:right; margin-left:1em">
		
			<xsl:variable name="cover-size">medium</xsl:variable>
			<xsl:choose>
				<xsl:when test="standard_numbers/isbn">
					<img class="cover">
						<xsl:attribute name="src">
							<xsl:value-of select="concat('http://summon.serialssolutions.com/2.0.0/image/isbn/', //config[@source='summon']/client_id, '/', standard_numbers/isbn, '/', $cover-size)" />
						</xsl:attribute>
					</img>
				</xsl:when>
				<xsl:when test="standard_numbers/issn">
					<img class="cover">
						<xsl:attribute name="src">
							<xsl:value-of select="concat('http://summon.serialssolutions.com/2.0.0/image/issn/', //config[@source='summon']/client_id, '/', substring(standard_numbers/issn, 1, 4), '-', substring(standard_numbers/issn, 5, 4), '/', $cover-size)" />
						</xsl:attribute>
					</img>
				</xsl:when>
			</xsl:choose>
			
		</div>
		
	</xsl:if>
	
</xsl:template>

</xsl:stylesheet>
