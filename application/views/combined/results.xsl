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

<xsl:template name="breadcrumb">

	<xsl:call-template name="breadcrumb_start" />
	
	<a href="{request/controller}">
		<xsl:value-of select="$text_search_module" />
	</a>
	
	<xsl:value-of select="$text_breadcrumb_separator" />
	
	<xsl:value-of select="$text_search_results" />
	
</xsl:template>

<xsl:template name="module_header">

	<style type="text/css">
		.combined-results-box {
			border-left : 1px solid #ccc;
			padding-left: 1em;
			font-size: 12px;
		}
		
		.combined-results-box h2, .combined-results-box h3 {
			font-weight: bold;
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
	
	<xsl:call-template name="spell_suggest" />
	
	<xsl:variable name="column_width" select="12 div count(//config/search/column)" />

	<div class="row-fluid">
	
		<xsl:for-each select="//config/search/column">
	
			<div class="span{$column_width}">
				<div>
					<xsl:attribute name="class">
						<xsl:text>combined-results-box</xsl:text>
						<xsl:if test="position() = 1">
							<xsl:text> first-box</xsl:text>
						</xsl:if>
					</xsl:attribute>
					
					<xsl:for-each select="option">
					
						<div class="combined-results-section">

							<h2><xsl:value-of select="@public" /></h2>
							
							<xsl:choose>
								<xsl:when test="//results and @id = //combined_engine">
								
									<xsl:call-template name="short_results" />
								
								</xsl:when>
								<xsl:otherwise>
							
									<xsl:choose>
										<xsl:when test="$is_ada = 0">
											<div id="{@id}" class="combined-engine" data-source="combined/partial?query={$query};engine={@id}">
												<img src="images/ajax-loader.gif" alt="" />
											</div>
											<noscript>
												<a href="{@url}"><xsl:value-of select="more/@public" /></a>
											</noscript>
										</xsl:when>
										<xsl:otherwise>
											<a href="{@url}"><xsl:value-of select="more/@public" /></a>
										</xsl:otherwise>
									</xsl:choose>
									
								</xsl:otherwise>
							</xsl:choose>
							
							<xsl:for-each select="additional">
							
								<div class="combined-results-other">
							
									<!-- @todo: i18n (remove @public from configuration, look up label by id instead) -->
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
												<a href="{@url}">
													<xsl:value-of select="@public" />
												</a>
												<xsl:text> </xsl:text>
												<span class="tabs-hit-number" id="{$engine_id}"></span>
											</li>
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
	
		<xsl:for-each select="//results/records/record/xerxes_record">
		
			<li>
				<a class="record-title" href="{../url_full}">
					<xsl:choose>
						<xsl:when test="string-length(title_normalized) &gt; 150">
							<xsl:value-of select="substring(title_normalized,1,150)" />
							<xsl:text>...</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="title_normalized" />
						</xsl:otherwise>
					</xsl:choose>
				</a>
				
				<div class="record-info">
					
					<xsl:if test="format/public">
						<xsl:value-of select="format/public" /><br />
					</xsl:if>
					
					<xsl:if test="primary_author">
						<xsl:value-of select="$text_combined_record_author" /><xsl:value-of select="primary_author" /><br />
					</xsl:if>
					
					<xsl:choose>
						<xsl:when test="//combined_engine = 'google'">
							<xsl:value-of select="snippet" />
						</xsl:when>
						<xsl:when test="journal">
							<xsl:value-of select="journal" />
						</xsl:when>
						<xsl:when test="year">
							<xsl:value-of select="$text_combined_record_published" /><xsl:value-of select="year" />
						</xsl:when>
					</xsl:choose>
				</div>
			</li>
			
		</xsl:for-each>
		
	</ul>
	
	<xsl:choose>
		<xsl:when test="//results/total &gt; 1">
		
			<xsl:variable name="current_engine" select="//combined_engine" />
		
			<div class="more-results">
				<a href="{//url_more}">
					<xsl:text>All </xsl:text>
					<xsl:value-of select="//summary/total" /><xsl:text> </xsl:text>
					<xsl:value-of select="//option[@id = $current_engine]/more/@public" /><xsl:text> </xsl:text>
					<i class="icon-arrow-right" />
				</a>
			</div>
		
		</xsl:when>
		<xsl:when test="/*/login_message">
			<p class="no-results">
				<a href="{/*/url_more}"><xsl:value-of select="/*/login_message" /></a>
			</p>
		</xsl:when>
		<xsl:when test="not(//results/total)">
			<p class="no-results"><xsl:value-of select="$text_combined_record_no_matches" /></p>
		</xsl:when>
	</xsl:choose>	

</xsl:template>

<xsl:template name="searchbox_full">

	<xsl:call-template name="simple_search_nofield" />

</xsl:template>
	
</xsl:stylesheet>
