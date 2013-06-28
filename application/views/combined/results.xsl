<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Combined results view
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

<xsl:template name="module_header">

	<style type="text/css">
		.combined-results-box {
			border-left : 1px solid #ccc;
			padding-left: 1em;
		}
		
		.combined-results-section {
			margin-bottom: 3em;
		}
		
		.combined-results-box h2 {
			margin-bottom: 1em;
			line-height: normal;
		}
		
		.first-box {
			border-left: none;
			padding-left: 0;
		}
		
		.combined-results-box p.no-results {
			font-style: italic;
		}		
		
		ul.combined-results li {
			margin-bottom: 1.3em;
			line-height: 140%;
		}
		
		ul.combined-results .record-info {
			margin-top: 4px;
			color: #666;
		}

		ul.combined-results .record-title {
			font-weight: bold;
			font-size: 110%;
		}
		
		.more-results {
			margin: 2em;
		}
		
		.more-results a, .more-results a:visited {
			font-size: 110%;
			color: #090;
		}
		
		.combined-results-other ul {
			margin-left: 2em;
		}
		
		.combined-results-other li {
			margin-bottom: 1em;
			list-style: disc;
		}
	</style>

</xsl:template>

<xsl:template name="module_javascript">

	<script type="application/javascript">
		$(document).ready( function(){
			
			$(".combined-engine").each( function() {
				var url = $(this).attr('data-source');
				var id = '#' + $(this).attr('id');				
				$(id).load(url);
			});
		});
	</script>
	
</xsl:template>

<!--
	TEMPLATE: SEARCH PAGE
-->

<xsl:template name="main">

	<xsl:variable name="query" select="php:function('urlencode', string(//request/query))" />

	<xsl:call-template name="searchbox">
		<xsl:with-param name="action">combined/results</xsl:with-param>
	</xsl:call-template>
	
	<xsl:variable name="column_width" select="12 div count(//config/search_combined/column)" />

	<div class="row-fluid">
	
		<xsl:for-each select="//config/search_combined/column">
	
			<div class="span{$column_width}">
				<div>
					<xsl:attribute name="class">
						<xsl:text>combined-results-box</xsl:text>
						<xsl:if test="position() = 1">
							<xsl:text> first-box</xsl:text>
						</xsl:if>
					</xsl:attribute>
					
					<xsl:for-each select="section">
					
						<div class="combined-results-section">

							<h2><xsl:value-of select="@public" /></h2>
							<div id="{option/@id}" class="combined-engine" data-source="combined/partial?query={$query};engine={option/@id}">
								<img src="images/ajax-loader.gif" alt="" />
							</div>
							
							<xsl:for-each select="additional">
							
								<div class="combined-results-other">
							
									<h3><xsl:value-of select="@public" /></h3>
			
									<ul>
										<xsl:for-each select="option">
											
											<xsl:variable name="id" select="@id" />
											<xsl:variable name="source" select="@source" />
											<xsl:variable name="engine_id">
												<xsl:text>tab-</xsl:text>
												<xsl:value-of select="@id" />
												<xsl:if test="@source">
													<xsl:text>-</xsl:text><xsl:value-of select="@source" />
												</xsl:if>
											</xsl:variable>
											
											<li>
												<a href="{//config/search/option[@id = $id and ( not(@source) or @source = $source)]/@url}"><xsl:value-of select="@public" /></a>
												<xsl:text> </xsl:text><span class="tabs-hit-number" id="{$engine_id}"></span></li>
										</xsl:for-each>
									</ul>
									
								</div>
							
							</xsl:for-each>
							
						</div>
							
					</xsl:for-each>
				
				</div>
			</div>
			
		</xsl:for-each>
		
	</div>
			
</xsl:template>

<!--
	TEMPLATE: SHORT RESULTS
	Very brief results display
-->

<xsl:template name="short_results">

	<ul class="combined-results">
	<xsl:for-each select="results/records/record/xerxes_record">
		<li>
			<a class="record-title" href="{../url_full}"><xsl:value-of select="title_normalized" /></a>
			<div class="record-info">
				<xsl:value-of select="format/public" /><br />
				<xsl:if test="primary_author">
					Author: <xsl:value-of select="primary_author" /><br />
				</xsl:if>
				<xsl:choose>
					<xsl:when test="journal">
						<xsl:value-of select="journal" />
					</xsl:when>
					<xsl:when test="year">
						Published: <xsl:value-of select="year" />
					</xsl:when>
				</xsl:choose>
			</div>
		</li>
	</xsl:for-each>
	</ul>
	
	
	
	<xsl:choose>
		<xsl:when test="//results/total &gt; 1">
		
			<xsl:variable name="current_engine">
				<xsl:choose>
					<xsl:when test="results/combined_engine">
						<xsl:value-of select="results/combined_engine" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="//request/engine" />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
		
			<div class="more-results">
				<a href="{//url_more}">
					<xsl:text>All </xsl:text>
					<xsl:value-of select="//summary/total" /><xsl:text> </xsl:text>
					<xsl:value-of select="//option[@id = $current_engine]/more/@public" /><xsl:text> </xsl:text>
					<i class="icon-arrow-right" />
				</a>
			</div>
		
		</xsl:when>
		<xsl:when test="not(//results/total)">
			<p class="no-results">No results found.  Please try another search option.</p>
		</xsl:when>
	</xsl:choose>	

</xsl:template>

<!--
	TEMPLATE: WEBSITE RESULTS
-->

<xsl:template name="website_results">

	<ul class="combined-results">
	<xsl:for-each select="results/result">
		<li>
			<xsl:if test="mime_type">
				<xsl:text>[ </xsl:text><xsl:value-of select="mime_type" /><xsl:text> ] </xsl:text>
			</xsl:if>
			<a class="record-title" href="{url}"><xsl:value-of select="title" /></a>
			<div class="record-info">
				<xsl:value-of select="snippet" />
			</div>
		</li>
	</xsl:for-each>
	</ul>

</xsl:template>

<!--
	TEMPLATE: SIMPLE SEARCH
-->

<xsl:template name="simple_search">

	<xsl:variable name="query"	select="request/query" />
	
	<div class="raised-box search-box">


		<div class="search-row">
			
			<input id="query" name="query" type="text" size="52" value="{$query}" /><xsl:text> </xsl:text>
			
			<input type="submit" name="Submit" value="Search" class="btn submit-searchbox{$language_suffix}" />
		
		</div>
		
		<xsl:call-template name="search_refinement" />
		
		<xsl:call-template name="advanced_search_option" />
		
	</div>

</xsl:template>

<!--

	<div class="row-fluid">
		<div class="span4">
			<div class="combined-results-box first-box">
			<h2>Articles</h2>
				<div id="summon" class="combined-engine" data-source="combined/partial?query={$query};engine=summon">
					<img src="images/ajax-loader.gif" alt="" />
				</div>
			</div>
		</div>
		<div class="span4">
			<div class="combined-results-box">
			
				<h2>Books &amp; Media</h2>
				
				<div id="solr" class="combined-engine" data-source="combined/partial?query={$query};engine=solr">
					<img src="images/ajax-loader.gif" alt="" />
				</div>
				
				<div class="combined-results-other">
					<h3>Other Book &amp; Media options</h3>
					
					<ul>
						<li><a href="#">Link+</a></li>
						<li><a href="#">Other CSU Libraries</a><xsl:text> </xsl:text><span class="tabs-hit-number" id="tab-worldcat-regional"></span></li>
						<li><a href="#">Worldcat</a><xsl:text> </xsl:text><span class="tabs-hit-number" id="tab-worldcat"></span></li>
					</ul>
				</div>
				
			</div>
		</div>
		<div class="span4">
			<div class="combined-results-box">
				<h2>Library Website</h2>
				<xsl:call-template name="website_results" />
			</div>
		</div>
	</div>	
	
	-->
	
</xsl:stylesheet>