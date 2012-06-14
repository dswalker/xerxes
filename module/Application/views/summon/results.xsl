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

	<xsl:if test="results/database_recommendations and not(//request/start) and not(//query/limits)">
	
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
					
					<xsl:if test="description">
					 	<xsl:text> -- </xsl:text>
						<xsl:value-of select="description" />
					 </xsl:if>
				</li>
				
			</xsl:for-each>
			
			</ul>
			
		</div>
		
	</xsl:if>
	
</xsl:template>

<!--

<xsl:template name="advanced_search_option">

	<div style="margin: 1em; margin-bottom: 0">
	
		<input type="checkbox" id="holdings" name="holdings" value="true">
			<xsl:if test="//request/holdings">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
		</input>
		<xsl:text> </xsl:text>
		<label for="holdings">Full-text only</label>	
	
	</div>

</xsl:template>

-->
		
</xsl:stylesheet>
