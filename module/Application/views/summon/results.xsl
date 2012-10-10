<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2012 California State University
 version:
 package: Xerxes
 link: http://xerxes.calstate.edu
 license:
 
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

	<xsl:if test="results/database_recommendations and not(//request/start) and //config/show_database_recommendations = 'true'">
	
		<div class="results-database-recommendations">
		
			<h2>
				<xsl:text>We found a </xsl:text>
			
				<xsl:choose>
					<xsl:when test="count(results/database_recommendations/database_recommendation) &gt; 1">
						couple of specialized databases
					</xsl:when>
					<xsl:otherwise>
						specialized database
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:text> that might help you.</xsl:text>
				</h2>
	
			<ul>
		
			<xsl:for-each select="results/database_recommendations/database_recommendation">
				
				<li>
					<a href="{link}"><xsl:value-of select="title" /></a>
					
					<!--
					<xsl:if test="description">
					 	
						<xsl:value-of select="description" />
					 </xsl:if>
					 -->
				</li>
				
			</xsl:for-each>
			
			</ul>
			
		</div>
		
	</xsl:if>
	
</xsl:template>

<xsl:template name="facet_narrow_results">

	<h3>Refine your search</h3>

	<xsl:variable name="scholarly">
		<xsl:if test="//request/*[@original_key = 'facet.IsScholarly']">
			<xsl:text>true</xsl:text>
		</xsl:if>		
	</xsl:variable>

	<xsl:variable name="fulltext">
		<xsl:if test="//request/*[@original_key = 'facet.IsFullText']">
			<xsl:text>true</xsl:text>
		</xsl:if>
	</xsl:variable>

	<xsl:variable name="showall">
		<xsl:value-of select="$scholarly" /><xsl:value-of select="$fulltext" />
	</xsl:variable>
	
	<xsl:variable name="holdings">
		<xsl:if test="//request/*[@original_key = 'facet.holdings']">
			<xsl:text>true</xsl:text>
		</xsl:if>
	</xsl:variable>

	<form id="form-facet-0" action="{//request/controller}/search" method="get">

		<xsl:call-template name="hidden_search_inputs">
			<xsl:with-param name="exclude_limit">facet.IsScholarly,facet.IsFullText,facet.holdings</xsl:with-param>
		</xsl:call-template>
		
		<ul>
		
			<xsl:if test="not(//config/limit_to_holdings) or //config/limit_to_holdings = 'false'">
		
				<li class="facet-selection">
				
					<input type="checkbox" class="facet-selection-clear" id="facet-0">
						<xsl:if test="$showall = ''">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:text> </xsl:text>
					<label for="facet-0">All results</label>
					
				</li>		
				
			</xsl:if>		
			
			<li class="facet-selection">
			
				<input type="checkbox" id="facet-0-1" class="facet-selection-option facet-0" name="facet.IsScholarly" value="true">
					<xsl:if test="$scholarly = 'true'">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
				<xsl:text> </xsl:text>
				<label for="facet-0-1">Scholarly only</label>
			
			</li>
			
			<xsl:if test="not(//config/show_fulltext_limit) or //config/show_fulltext_limit = 'true'">
		
				<li class="facet-selection">
				
					<input type="checkbox" id="facet-0-2" class="facet-selection-option facet-0" name="facet.IsFullText" value="true">
						<xsl:if test="$fulltext = 'true'">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:text> </xsl:text>
					<label for="facet-0-2">Full-text only</label>
				
				</li>
			
			</xsl:if>
	
			<xsl:if test="//config/limit_to_holdings = 'true'">
	
				<li class="facet-selection">
				
					<input type="checkbox" id="facet-0-3" class="facet-selection-option facet-0" name="facet.holdings" value="false">
						<xsl:if test="$holdings = 'true'">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:text> </xsl:text>
					<label for="facet-0-2">Add results beyond the library's collection</label>
				
				</li>
				
			</xsl:if>
		</ul>
	
	</form>

</xsl:template>
			
</xsl:stylesheet>
