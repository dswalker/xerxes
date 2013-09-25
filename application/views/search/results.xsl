<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet  [
	<!ENTITY nbsp   "&#160;">
	<!ENTITY copy   "&#169;">
	<!ENTITY reg    "&#174;">
	<!ENTITY trade  "&#8482;">
	<!ENTITY mdash  "&#8212;">
	<!ENTITY ldquo  "&#8220;">
	<!ENTITY rdquo  "&#8221;"> 
	<!ENTITY pound  "&#163;">
	<!ENTITY yen    "&#165;">
	<!ENTITY euro   "&#8364;">
]>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Search results view
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
		
	<!--
		TEMPLATE: SEARCH BREADCRUMB
	-->
	
	<xsl:template name="breadcrumb_search">

		<xsl:call-template name="breadcrumb_start" />
		
		<xsl:choose>
			<xsl:when test="config/search/combined_url">
			
				<a href="{config/search/combined_url}">
					<xsl:value-of select="$text_search_combined" />
				</a>
				
				<xsl:value-of select="$text_breadcrumb_separator" />
				
				<a href="{request/controller}">
					<xsl:value-of select="//option[@current=1]/@id" />
				</a>
			
			</xsl:when>
			<xsl:otherwise>
	
				<a href="{request/controller}">
					<xsl:value-of select="$text_search_module" />
				</a>
				
			</xsl:otherwise>
		</xsl:choose>
		
		<xsl:value-of select="$text_breadcrumb_separator" />
		
	</xsl:template>
	
	<!--
		TEMPLATE: SEARCH PAGE
	-->

	<xsl:template name="search_page">

		<xsl:param name="sidebar">
			<xsl:choose>
				<xsl:when test="//config/search_sidebar = 'right'">
					<xsl:text>right</xsl:text>
				</xsl:when>
				<xsl:when test="//config/search_sidebar = 'none'">
					<xsl:text>none</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>left</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:param>
		
		<xsl:param name="sidebar_width">3</xsl:param>
		
		<xsl:variable name="results_width" select="floor(12 - $sidebar_width)" />
	
		<!-- search box area -->
		
		<xsl:call-template name="searchbox" />
		
		<div style="clear:both"></div>
		
		<div>
			<xsl:if test="config/use_tabs = 'true'">
				<xsl:attribute name="class">tabs</xsl:attribute>
			</xsl:if>
		
			<!-- tabs -->
			
			<xsl:if test="config/use_tabs = 'true'">
			
				<div class="tab-{$sidebar}">				
					<xsl:call-template name="search_modules" />
				</div>
				
			</xsl:if>
			
			<!-- results -->
			
			<div class="row-fluid">
			
				<xsl:choose>
				
					<xsl:when test="$sidebar = 'none' or $is_mobile = 1">
					
						<xsl:call-template name="search_results_area" />
						
					</xsl:when>				
				
					<xsl:when test="$sidebar = 'right'">
						
						<div class="span{$results_width}">
							<xsl:call-template name="search_results_area" />
						</div>
						<div class="span{$sidebar_width}">
							<xsl:call-template name="search_sidebar_area">
								<xsl:with-param name="sidebar" select="$sidebar" />
							</xsl:call-template>
						</div>
						
					</xsl:when>
					<xsl:when test="$sidebar = 'left'">
					
						<div class="span{$sidebar_width}">
							<xsl:call-template name="search_sidebar_area">
								<xsl:with-param name="sidebar" select="$sidebar" />
							</xsl:call-template>
						</div>
						<div class="span{$results_width}">
							<xsl:call-template name="search_results_area" />
						</div>
											
					</xsl:when>

				</xsl:choose>		

			</div>
		</div>
		
		<xsl:call-template name="results_loader" />
		
	</xsl:template>

	<!--
		TEMPLATE: SEARCH RESULTS AREA
	-->	
	
	<xsl:template name="search_results_area">
		
		<xsl:call-template name="sort_bar" />
		
		<xsl:call-template name="facets_applied" />
											
		<xsl:call-template name="spell_suggest" />
		
		<xsl:call-template name="no_hits" />
		
		<xsl:call-template name="search_login_reminder" />
		
		<xsl:call-template name="search_recommendations" />

		<xsl:call-template name="brief_results" />

		<xsl:call-template name="paging_navigation" />


	</xsl:template>

	<!--
		TEMPLATE: SEARCH RESULTS SIDEBAR AREA
	-->	
	
	<xsl:template name="search_sidebar_area">
		<xsl:param name="sidebar" />
			
		<div id="search-sidebar" class="sidebar {$sidebar}">	
					
			<!-- modules -->
			
			<xsl:if test="not(config/use_tabs = 'true')">
				<xsl:call-template name="search_modules" />
			</xsl:if>
							
			<!-- facets -->
			
			<xsl:call-template name="search_sidebar_facets" />
			<xsl:call-template name="search_sidebar_additional" />
			
		</div>
			
	</xsl:template>
	
	<!--
		TEMPLATE: RESULTS LOADER
	-->	
	
	<xsl:template name="results_loader">
	
		<div id="fullscreen" style="display:none">
		</div>
		
		<div id="loading" style="display:none">
			<img src="{$base_url}/images/ajax-loader.gif" alt="" /><xsl:text> </xsl:text><xsl:value-of select="$text_search_loading" />
		</div>
	
	</xsl:template>

	<!--
		TEMPLATE: SORT BAR
	-->

	<xsl:template name="sort_bar">
	
		<div id="sort">
		
			<xsl:if test="results/total and not(results/total = '0')">
		
				<div class="row-fluid">
					<div class="span5">
						<xsl:copy-of select="$text_metasearch_results_summary" />
					</div>
					
					<div class="span7">
						<xsl:choose>
							<xsl:when test="//sort_display and $is_mobile = '0'">
								<div id="sort-options" data-role="controlgroup" data-type="horizontal" data-mini="true">
									<xsl:copy-of select="$text_results_sort_by" /><xsl:text>: </xsl:text>
									<xsl:for-each select="//sort_display/option">
										<xsl:choose>
											<xsl:when test="@active = 'true'">
												<strong data-role="button" data-theme="b">
													<xsl:call-template name="text_results_sort_by">
														<xsl:with-param name="option" select="text()" />
													</xsl:call-template>
												</strong>
											</xsl:when>
											<xsl:otherwise>
												<a href="{@link}" data-role="button">
													<xsl:call-template name="text_results_sort_by">
														<xsl:with-param name="option" select="text()" />
													</xsl:call-template>
												</a>
											</xsl:otherwise>
										</xsl:choose>
										<xsl:if test="following-sibling::option and $is_mobile = 0">
											<xsl:text> | </xsl:text>
										</xsl:if>
									</xsl:for-each>
								</div>
							</xsl:when>
							<xsl:otherwise>&nbsp;</xsl:otherwise>
						</xsl:choose>
					</div>
				</div>
				<div style="clear:both"></div>
				
			</xsl:if>
		</div>

	
	</xsl:template>

	<!--
		TEMPLATE: NO HITS
	-->
	
	<xsl:template name="no_hits">
	
		<xsl:if test="not(results/total) or results/total = '0'">
	
			<div class="no-hits error"><xsl:value-of select="$text_metasearch_hits_no_match" /></div>
		
		</xsl:if>
	
	</xsl:template>
	
	<!--
		TEMPLATE: SEARCH BOX
	-->
	
	<xsl:template name="searchbox">
	
		<xsl:param name="action"><xsl:value-of select="$base_url" />/<xsl:value-of select="//request/controller" />/search</xsl:param>

		<xsl:call-template name="search_promo" />
	
		<form id="form-main-search" action="{$action}" method="get">	
	
			<xsl:if test="//request/lang">
				<input type="hidden" name="lang" value="{//request/lang}" />
			</xsl:if>
			
			<xsl:call-template name="searchbox_hidden_fields_module" />
			<xsl:call-template name="searchbox_hidden_fields_local" />
	
			<xsl:if test="request/sort">
				<input type="hidden" name="sort" value="{request/sort}" />
			</xsl:if>
	
			<xsl:choose>
				<xsl:when test="$is_mobile = '1'">
					<xsl:call-template name="searchbox_mobile" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:call-template name="searchbox_full" />
				</xsl:otherwise>
			</xsl:choose>
			
		</form>
		
	</xsl:template>

	<!--
		TEMPLATE: SEARCH BOX MOBILE
	-->	
	
	<xsl:template name="searchbox_mobile">
	
		<xsl:variable name="search_query" select="//request/query" />
		<xsl:call-template name="mobile_search_box">
			<xsl:with-param name="query" select="$search_query" />
		</xsl:call-template>
			
	</xsl:template>

	<!-- 
		TEMPLATE: MOBILE SEARCH BOX
		Just the search box and go itself, suited for mobile
	-->
	
	<xsl:template name="mobile_search_box">
		<xsl:param name="query" />
		
		<xsl:if test="not(//request/action)">
		
			<div class="searchbox-mobile">
				<input type="text" name="query" value="{$query}" />
				<xsl:text> </xsl:text>
				<input class="submit_searchbox{$language_suffix}" type="submit" value="{$text_searchbox_go}" />
			</div>
			
		</xsl:if>
		
	</xsl:template>


	<!--
		TEMPLATE: SEARCH BOX FULL
	-->
	
	<xsl:template name="searchbox_full">
	
		<xsl:choose>
			<xsl:when test="request/advanced or request/advancedfull">
				<xsl:call-template name="advanced_search" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="config/basic_search_fields/field">
						<xsl:call-template name="simple_search" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="simple_search_nofield" />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>

	<!--
		TEMPLATE: SIMPLE SEARCH
	-->
	
	<xsl:template name="simple_search">
	
		<xsl:variable name="query"	select="request/query" />
		
		<div class="raised-box search-box">
	
			<div class="search-row">

				<label for="field"><xsl:value-of select="$text_searchbox_search" /></label><xsl:text> </xsl:text>
				
				<select id="field" name="field">
					
					<xsl:for-each select="config/basic_search_fields/field">
					
						<xsl:variable name="internal">
							<xsl:choose>
								<xsl:when test="@id"><xsl:value-of select="@id" /></xsl:when>
								<xsl:otherwise><xsl:value-of select="@internal" /></xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
					
						<option value="{$internal}">
							<xsl:if test="//request/field = $internal">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:call-template name="text_search_fields">
								<xsl:with-param name="option" select="$internal" />
							</xsl:call-template>
						</option>
						
					</xsl:for-each>
				</select>
				
				<xsl:text> </xsl:text><label for="query"><xsl:value-of select="$text_searchbox_for" /></label><xsl:text> </xsl:text>
				
				<input id="query" name="query" type="text" size="32" value="{$query}" /><xsl:text> </xsl:text>
				
				<input type="submit" name="Submit" value="{$text_searchbox_go}" class="btn submit-searchbox{$language_suffix}" />
			
			</div>
			
			<xsl:call-template name="search_refinement" />
			
			<xsl:call-template name="advanced_search_option" />
			
		</div>
	
	</xsl:template>
	
	<!--
		TEMPLATE: SIMPLE SEARCH NO FIELD
	-->
	
	<xsl:template name="simple_search_nofield">
	
		<xsl:variable name="query"	select="request/query" />
		
		<div class="raised-box search-box">
	
	
			<div class="search-row">
				
				<input id="query" name="query" type="text" size="52" value="{$query}" title="enter search terms" /><xsl:text> </xsl:text>
				
				<input type="submit" name="Submit" value="Search" class="btn submit-searchbox{$language_suffix}" />
			
			</div>
						
			<xsl:call-template name="advanced_search_option" />
			
		</div>
	
	</xsl:template>	

	<!-- 	
		TEMPLATE: SEARCH REFINEMENT
	-->
	
	<xsl:template name="search_refinement">
	
		<xsl:if test="config/facet_multiple = 'true' and results/facets">
		
			<div class="search-refine">
				<input id="results-clear-facets-false" type="radio" name="clear-facets" value="false">
					<xsl:if test="//request/session/clear_facets = 'false'">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
				<xsl:text> </xsl:text>
				<label for="results-clear-facets-false"><xsl:value-of select="$text_results_clear_facets_false" /></label>
				<xsl:text> </xsl:text>
				<input id="results-clear-facets-true" type="radio" name="clear-facets" value="true">
					<xsl:if test="not(//request/session/clear_facets) or //request/session/clear_facets != 'false'">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>				
				</input>
				<xsl:text> </xsl:text>
				<label for="results-clear-facets-true"><xsl:value-of select="$text_results_clear_facets_false" /></label>
			</div>
			
			<xsl:call-template name="hidden_search_inputs">
				<xsl:with-param name="exclude_search_params">true</xsl:with-param>
			</xsl:call-template>
			
		</xsl:if>	
	
	</xsl:template>

	<!-- 	
		TEMPLATE: SPELL SUGGEST
	-->
	
	<xsl:template name="spell_suggest">
	
		<xsl:if test="spelling/url">
			<p class="spell-suggest error">
				<xsl:value-of select="$text_searchbox_spelling_error" /><xsl:text> </xsl:text>
				<a href="{spelling/url}"><xsl:value-of select="spelling/query" /></a>
			</p>
		</xsl:if>	
	
	</xsl:template>
	
	<!-- 	
		TEMPLATE: SEARCH MODULES
		displays search engines being searched
	-->
	
	<xsl:template name="search_modules">
	
		<xsl:choose>
			<xsl:when test="config/search and config/search/column/option[@tab = 'true' and @current = '1']">
			
				<div>
					<xsl:choose>
						<xsl:when test="config/use_tabs = 'true'">
							<xsl:attribute name="id">tabnav</xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="id">search-modules</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				
					<!-- <h2>Search Options</h2> -->
					
					<xsl:for-each select="config/search">
						<ul>
							<xsl:call-template name="search_module" />
						</ul>
						<div style="clear:both"></div>
					</xsl:for-each>
					
				</div>
			
			</xsl:when>
			<xsl:when test="config/search">
				
				<h2><xsl:value-of select="//option[@current = '1']/@public" /></h2>
				
			</xsl:when>	
			
		</xsl:choose>
		
	</xsl:template>
	

	<!-- 
		TEMPLATE: SEARCH MODULE
		each tab
	-->
	
	<xsl:template name="search_module">
	
		<xsl:for-each select="column/option[@tab = 'true']">
			
			<li id="tab-{@id}">
			
				<xsl:if test="@current = 1">
					<xsl:attribute name="class">here</xsl:attribute>
				</xsl:if>
				
				<a href="{@url}">				
					<xsl:value-of select="@public" />
					<xsl:text> </xsl:text>
					<xsl:call-template name="tab_hit" />
				</a>
			</li>
		</xsl:for-each>
		
	</xsl:template>

	<!-- 
		TEMPLATE: TAB HIT
	-->
	
	<xsl:template name="tab_hit">
	
		<span class="tabs-hit">
			<xsl:choose>
				<xsl:when test="@hits">
					(<xsl:value-of select="@hits" />)
				</xsl:when>
				<xsl:otherwise>
					<span class="tabs-hit-number" id="tab-{@id}-{@source}"></span>
				</xsl:otherwise>
			</xsl:choose>								
		</span>
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: PAGING NAVIGATION
		Provides the visual display for moving through a set of results
	-->
	
	<xsl:template name="paging_navigation">
	
		<xsl:if test="//pager/page">
		
			<xsl:choose>
				
				<xsl:when test="$is_mobile = 1 and //pager/page[@type='next']">
				
					<a href="{//pager/page[@type='next']/@link}" data-role="button">
						<xsl:copy-of select="$text_results_next" />
					</a>
				
				</xsl:when>
					
				<xsl:otherwise>	
					<div class="results-pager">
			
						<ul class="results-pager-list">
						<xsl:for-each select="//pager/page">
							<li>
							<xsl:variable name="link" select="@link" />
							<xsl:choose>
								<xsl:when test="@here = 'true'">
									<strong><xsl:value-of select="text()" /></strong>
								</xsl:when>
								<xsl:otherwise>
									<a href="{$link}">
										<xsl:choose>
											<xsl:when test="@type = 'next'">
												<xsl:attribute name="class">results-pager-next</xsl:attribute>
												<xsl:copy-of select="$text_results_next" />
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="class">results-pager-link</xsl:attribute>
											</xsl:otherwise>
										</xsl:choose>
										<xsl:value-of select="text()" />
									</a>
								</xsl:otherwise>
							</xsl:choose>
							</li>
						</xsl:for-each>
						</ul>
					</div>
					
				</xsl:otherwise>
				
			</xsl:choose>
			
		</xsl:if>
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: SEARCH SIDEBAR FACETS
		the sidebar within the search results
	-->
	
	<xsl:template name="search_sidebar_facets">
			
			<div class="box">
			
			
				<xsl:call-template name="facet_narrow_results" />
				
				<xsl:if test="//facets/groups[not(display)]">
				
					<xsl:for-each select="//facets/groups/group[not(display)]">

						<!-- only show the facets if there is more than one -->
	
						<xsl:if test="count(facets/facet) &gt; 1 or //config/facet_multiple = 'true'">
								
							<h3>
								<xsl:call-template name="text_facet_fields">
									<xsl:with-param name="option" select="name" />
								</xsl:call-template>
							</h3>
							
							<xsl:choose>
								<xsl:when test="facets/facet/is_date">
									<xsl:call-template name="facet_dates" />
								</xsl:when>
								<xsl:when test="//config/facet_multiple = 'true'">
									<xsl:call-template name="facet_multiple" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:call-template name="facet_links" />
								</xsl:otherwise>
							</xsl:choose>
							
						</xsl:if>
		
					</xsl:for-each>
				
				</xsl:if>
			
			</div>
	
	</xsl:template>

	<!-- 
		TEMPLATE: FACET EXPAND RESULTS
		Additional search options in the sidebar area
	-->
	
	<xsl:template name="facet_expand_results">
	
		<xsl:for-each select="config/search/column/option[@current = '1']/additional">
		
			<div class="facet-expand box">
				
			<h3 class="facet-expand-header">
				<xsl:call-template name="text_facet_fields">
					<xsl:with-param name="option" select="@internal" />
				</xsl:call-template>
AAA
<!--
				<xsl:value-of select="@public" />
-->
			</h3>
			<ul>
				<xsl:for-each select="option">
					<li>
						<a href="{@url}">
							<xsl:call-template name="text_facet_fields">
								<xsl:with-param name="option" select="@internal" />
							</xsl:call-template>
BBB
<!--
							<xsl:value-of select="@public" />
-->
						</a>
						<xsl:text> </xsl:text>
						<xsl:call-template name="tab_hit" />
					</li>
				</xsl:for-each>
			</ul>
			</div>
			
		</xsl:for-each>
			
	</xsl:template>

	<!-- 
		TEMPLATE: FACET DATES
	-->
		
	<xsl:template name="facet_dates">
	
		<div class="facet-date">
	
			<form id="form-{group_id}" action="{//request/controller}/search" method="get">
				<input name="lang" type="hidden" value="{//request/lang}" />
				
				<xsl:if test="not(//config/show_date_graph) or //config/show_date_graph = 'true'">
			
					<div id="placeholder" style="width: 210px; height: 120px">
						<xsl:attribute name="data-source">
							<xsl:for-each select="facets/facet">
								<xsl:if test="timestamp">
									<xsl:value-of select="timestamp"/>,<xsl:value-of select="count" />
									<xsl:if test="following-sibling::facet">
										<xsl:text>;</xsl:text>
									</xsl:if>
								</xsl:if>
							</xsl:for-each>
						</xsl:attribute>
					</div>
					
				</xsl:if>
			
				<xsl:variable name="start_date" select="concat(param_name,'.start')" />
				<xsl:variable name="end_date" select="concat(param_name,'.end')" />
	
				<xsl:call-template name="hidden_search_inputs">
					<xsl:with-param name="exclude_limit" select="param_name" />
				</xsl:call-template>
				
				<div class="facet-date-selector">
		
					<div>
						<label for="facet-date-start"><xsl:value-of select="$text_facets_from"/> </label>
						<input type="text" name="{$start_date}" id="facet-date-start" value="{//request/*[@original_key = $start_date]}" 
							maxlength="4" size="4" />
					</div>
					
					<div>					
						<label for="facet-date-end"><xsl:value-of select="$text_facets_to"/> </label>
						<input type="text" name="{$end_date}" id="facet-date-end" value="{//request/*[@original_key = $end_date]}" 
							maxlength="4" size="4" />
					</div>
				
				</div>
				
				<input type="submit" class="btn">
					<xsl:attribute name="value"><xsl:value-of select="$text_facets_update"/></xsl:attribute>
				</input>
				
			</form>
			
		</div>
		
	</xsl:template>	
	
	<!-- 
		TEMPLATE: FACET LINKS 
	-->
	
	<xsl:template name="facet_links">
	
		<!-- only show first 10, unless there is 12 or fewer, in which case show all 12 -->
		
		<ul>
		<xsl:for-each select="facets/facet[position() &lt;= 10 or count(../facet) &lt;= 12]">
			<xsl:call-template name="facet_option" />
		</xsl:for-each>
		</ul>
		
		<xsl:if test="count(facets/facet) &gt; 12">
			
			<p id="facet-more-{name}" class="facet-option-more"> 
				[ <a id="facet-more-link-{name}" href="#" class="facet-more-option"> 
					<xsl:value-of select="count(facets/facet[position() &gt; 10])" /> more
				</a> ] 
			</p>
			
			<ul id="facet-list-{name}" class="facet-list-more">
				<xsl:for-each select="facets/facet[position() &gt; 10]">
					<xsl:call-template name="facet_option" />
				</xsl:for-each>
			</ul>
			
			<p id="facet-less-{name}" class="facet-option-less"> 
				[ <a id="facet-less-link-{name}" href="#" class="facet-less-option"> 
					show less
				</a> ] 
			</p>
	
		</xsl:if>	
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: FACET OPTION 
	-->
	
	<xsl:template name="facet_option">
	
		<li>
			<xsl:choose>
				<xsl:when test="url">
					<a href="{url}">
						<xsl:call-template name="facet_name">
							<xsl:with-param name="name" select="name" />
						</xsl:call-template>
					</a>
				</xsl:when>
				<xsl:otherwise>
					<xsl:call-template name="facet_name">
						<xsl:with-param name="name" select="name" />
					</xsl:call-template>
				</xsl:otherwise>
			</xsl:choose>
						
			<xsl:if test="count">			
				&nbsp;(<xsl:value-of select="count_display" />)
			</xsl:if>
		</li>
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: FACET MULTIPLE 
	-->	
	
	<xsl:template name="facet_multiple">
		
		<form id="form-{group_id}" action="{//request/controller}/search" method="get">
		<input name="lang" type="hidden" value="{//request/lang}" />
		<xsl:call-template name="hidden_search_inputs">
			<xsl:with-param name="exclude_limit" select="param_name" />
		</xsl:call-template>
		
		<ul>
			<li class="facet-selection">
				<input type="checkbox" class="facet-selection-clear" id="{group_id}">
					<xsl:if test="not(facets/facet/selected)">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
				<xsl:text> </xsl:text>
				<label for="{group_id}"><xsl:value-of select="$text_facets_multiple_any" /></label>
			</li>
			
			<xsl:for-each select="facets/facet[(position() &lt;= 7 or selected or count(../facet) &lt;= 9) and not(is_excluded)]">
				<xsl:call-template name="facet_selection" />
			</xsl:for-each>
			
			<xsl:call-template name="facet_excluded" />
			
		</ul>
				
		<p id="facet-more-{group_id}" class="facet-option-more"> 
			<a id="facet-more-link-{group_id}" href="{url}" class="btn btn-small facet-more-launch"> 
				<xsl:value-of select="$text_searchbox_options_more" />
			</a>
		</p>
		
		<xsl:call-template name="facet_noscript_submit" />
		
		</form>
	
	</xsl:template>

	<!-- 
		TEMPLATE: FACET NAME
	-->
	
	<xsl:template name="facet_name">
		<xsl:param name="name" />
		<xsl:choose>
			<xsl:when test="../../name = 'ContentType'">
				<xsl:call-template name="text_results_format">
					<xsl:with-param name="format" select="name" />
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="../../name = 'SubjectTerms'">
				<xsl:call-template name="text_facet_subject">
					<xsl:with-param name="option" select="name" />
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="../../name = 'Discipline'">
				<xsl:call-template name="text_facet_discipline">
					<xsl:with-param name="option" select="name" />
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="../../name = 'Language'">
				<!-- @todo -->
				<xsl:value-of select="name" />
			</xsl:when>
			<xsl:when test="../../name = 'format'">
				<xsl:call-template name="text_results_format">
					<xsl:with-param name="format" select="name" />
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="name" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- 
		TEMPLATE: FACET SELECTION 
	-->
	
	<xsl:template name="facet_selection">
		
		<li class="facet-selection">
		
			<input type="checkbox" id="{input_id}" class="facet-selection-option {../../group_id}" name="{param_name}" value="{name}">
				<xsl:if test="selected">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			</input>
			
			<xsl:text> </xsl:text>	
			
			<label for="{input_id}">
				<xsl:call-template name="facet_name">
					<xsl:with-param name="name" select="name" />
				</xsl:call-template>
			</label>
			
			<xsl:if test="count">
				&nbsp;(<xsl:value-of select="count_display" />)
			</xsl:if>
			
		</li>
	
	</xsl:template>

	<!-- 
		TEMPLATE: FACET EXCLUDED 
	-->
	
	<xsl:template name="facet_excluded">
	
		<xsl:for-each select="facets/facet[is_excluded]">
			<li class="facet-selection facet-excluded">
				<a href="{url}">
					<img src="{$base_url}/images/famfamfam/delete.png" alt="remove exlcuded facet" />
					<xsl:text> </xsl:text>
					<span class="facet-excluded-text"><xsl:value-of select="name" /></span>
				</a>
			</li>
		</xsl:for-each>	
	
	</xsl:template>

	<!-- 
		TEMPLATE: FACETS APPLIED
		A bar across the top of the results showing a limit has been applied
	-->
	
	<xsl:template name="facets_applied">
		
		<xsl:if test="not(//config/facet_multiple = 'true') and query/limits">
			<div class="results-facets-applied">
				<ul>
					<xsl:for-each select="query/limits/limit">
						<li>
							<div class="remove">
								<a href="{remove_url}">
									<xsl:call-template name="img_facet_remove">
										<xsl:with-param name="alt"><xsl:value-of select="$text_results_hint_remove_limit" /></xsl:with-param>
										<xsl:with-param name="title"><xsl:value-of select="$text_results_hint_remove_limit" /></xsl:with-param>
									</xsl:call-template>
								</a>
							</div> 
							<xsl:value-of select="$text_folder_tags_limit" /><xsl:text> </xsl:text><xsl:value-of select="value" /> 
						</li>
					</xsl:for-each>
				</ul>
			</div>
		</xsl:if>
		
	</xsl:template>

	<!-- 
		TEMPLATE: FACET NOSCRIPT SUBMIT
	-->
	
	<xsl:template name="facet_noscript_submit">
	
		<xsl:choose>
			<xsl:when test="$is_ada = '1'">
				<input type="submit" class="btn">
					<xsl:attribute name="value"><xsl:value-of select="$text_records_tags_update" /></xsl:attribute>
				</input>
			</xsl:when>
			<xsl:otherwise>
				<noscript>
					<input type="submit" class="btn">
						<xsl:attribute name="value"><xsl:value-of select="$text_records_tags_update" /></xsl:attribute>
					</input>
				</noscript>
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>

	<!-- 
		TEMPLATE: BRIEF RESULTS
	-->
	
	<xsl:template name="brief_results">
	
		<ul id="results" data-role="listview" data-inset="true">
		
		<xsl:for-each select="//records/record/xerxes_record">

			<xsl:call-template name="brief_result" />

		</xsl:for-each>
		
		</ul>
		
	</xsl:template>
	
	<!-- 
		TEMPLATE: BRIEF RESULT
		display of results geared toward articles (or really any non-book display)
	-->
	
	<xsl:template name="brief_result">

		<li class="result">
					
			<xsl:variable name="title">
				<xsl:choose>
					<xsl:when test="title_normalized != ''">
						<xsl:value-of select="title_normalized" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:copy-of select="$text_results_no_title" />
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<xsl:choose>
				<xsl:when test="$is_mobile = 1">
				
					<a href="{../url_full}">
						<xsl:value-of select="$title" />
						<xsl:call-template name="brief_result_info" />
					</a>
					
				</xsl:when>
				<xsl:otherwise>
			
					<a class="results-title" href="{../url_full}"><xsl:value-of select="$title" /></a>
					<xsl:call-template name="brief_result_info" />
					
				</xsl:otherwise>
			</xsl:choose>
			
		</li>
	
	</xsl:template>

	<!-- 
		TEMPLATE: BRIEF RESULT INFO
	-->
	
	<xsl:template name="brief_result_info">
		
		<div class="results-info">
		
			<xsl:call-template name="brief_result_info-type" />
			<xsl:call-template name="brief_result_info-abstract" />
			<xsl:call-template name="brief_result_info-primary_author" />
			<xsl:call-template name="brief_result_info-publication_year" />
			<xsl:call-template name="brief_result_info-journal_info" />
			
			<!-- custom area for local implementation to add junk -->
			
			<xsl:call-template name="additional_brief_record_data" />
			
			<xsl:if test="$is_mobile = 0">
			
				<div class="record-actions">
					
					<!-- full text -->
					
					<xsl:call-template name="full_text_options" />
					
					<!-- custom area for additional links -->
					
					<xsl:call-template name="additional_record_links" />
					
					<!-- save record -->
					
					<xsl:call-template name="save_record" />
								
				</div>
				
			</xsl:if>
			
		</div>
	
	</xsl:template>
	
	<xsl:template name="brief_result_info-type">
		<div class="results-type">
		
			<!-- format -->
		
			<xsl:call-template name="text_results_format">
				<xsl:with-param name="format" select="format/public" />
			</xsl:call-template>
			
			<!-- language note -->
			
			<xsl:call-template name="text_results_language" />
			
			<!-- peer reviewed -->
			
			<xsl:if test="refereed">
				<xsl:text> </xsl:text><xsl:call-template name="img_refereed" />
				<xsl:text> </xsl:text><xsl:copy-of select="$text_results_refereed" />
			</xsl:if>
		</div>
	</xsl:template>
	
	<xsl:template name="brief_result_info-abstract">
		<div class="results-abstract">
		
			<xsl:choose>
				<xsl:when test="summary_type = 'toc'">
					<xsl:value-of select="$text_record_summary_toc" /><xsl:text>: </xsl:text>
				</xsl:when>
				<xsl:when test="summary_type = 'subjects'">
					<xsl:value-of select="$text_record_summary_subjects" /><xsl:text>: </xsl:text>
				</xsl:when>					
			</xsl:choose>
		
			<xsl:choose>
				<xsl:when test="string-length(summary) &gt; 300">
					<xsl:value-of select="substring(summary, 1, 300)" /> . . .
				</xsl:when>
				<xsl:when test="summary">
					<xsl:value-of select="summary" />
				</xsl:when>
				
			</xsl:choose>
		</div>
	</xsl:template>
	
	<xsl:template name="brief_result_info-primary_author">
		<xsl:if test="primary_author">
			<span class="results-author">
				<strong><xsl:copy-of select="$text_results_author" />: </strong><xsl:value-of select="primary_author" />
			</span>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="brief_result_info-publication_year">
		<xsl:if test="year">
			<span class="results-year">
				<strong><xsl:copy-of select="$text_results_year" />: </strong>
				<xsl:value-of select="year" />
			</span>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="brief_result_info-journal_info">
			<xsl:if test="journal or journal_title">
				<span class="results-publishing">
					<strong><xsl:copy-of select="$text_results_published_in" />: </strong>
					<xsl:choose>
						<xsl:when test="journal_title">
							<xsl:value-of select="journal_title" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="journal" />
						</xsl:otherwise>
					</xsl:choose>
				</span>
			</xsl:if>
	</xsl:template>
	
	<!-- 
		TEMPLATE: FULL TEXT OPTIONS
		Logic for determining which full-text links to show
	-->
	
	<xsl:template name="full_text_options">
		<xsl:param name="show_full_text_and_link_resolver">false</xsl:param> 
					
		<xsl:variable name="link_resolver_allowed">
			<xsl:choose>
				<xsl:when test="../dont_show_link_resolver or (full_text_bool and $show_full_text_and_link_resolver = 'false')">
					<xsl:text>false</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>true</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
				
		<!-- native full-text -->
	
		<xsl:if test="full_text_bool">
			
			<xsl:call-template name="full_text_links"/>							
				
		</xsl:if>
		
		<!-- link resolver -->
		
		<xsl:if test="../url_open">
		
			<xsl:choose>
				
				<!-- link resolver, full-text predetermined -->
				
				<xsl:when test="$link_resolver_allowed = 'true' and subscription = 1 and not(links/link[@type = 'full'])">
						<a href="{../url_open}" target="{$link_target}" class="record-action link-resolver-link" data-role="button">
							<xsl:call-template name="img_format_html">
								<xsl:with-param name="class">mini-icon link-resolver-link</xsl:with-param>
							</xsl:call-template>
							<xsl:text> </xsl:text>
							<xsl:copy-of select="$text_link_resolver_available" />
						</a>
				</xsl:when>
				
				<!-- link resolver, no full-text predetermined -->
				
				<xsl:when test="$link_resolver_allowed = 'true'">
						<a href="{../url_open}" target="{$link_target}" class="record-action link-resover-link" data-role="button">
							<img src="{$image_sfx}" alt="" class="mini-icon link-resover-link "/>
							<xsl:text> </xsl:text>
							<xsl:copy-of select="$text_link_resolver_check" />
						</a>
				</xsl:when>
				
				<!-- if no direct link or link resolver, do we have an original record link? -->
				
				<xsl:when test="links/link[@type='original_record'] and ../show_original_record_link">
					<xsl:call-template name="record_link">
						<xsl:with-param name="type">original_record</xsl:with-param>
						<xsl:with-param name="text" select="$text_link_original_record"/>
						<xsl:with-param name="img_src" select="$img_src_chain"/>
					</xsl:call-template>
				</xsl:when>
				
			</xsl:choose>
			
		</xsl:if>
		
	</xsl:template>

	<!-- 
		TEMPLATE: SAVE RECORD
		Display for saving (and also deleting) a record
	-->

	<xsl:template name="save_record">

		<xsl:variable name="source" select="source" />
		<xsl:variable name="record_id" select="record_id" />
		
		<!-- @todo: move this to the controller? -->
		
		<xsl:variable name="is_already_saved" select="//request/session/resultssaved[@key = $record_id]" />
		
		<div id="save-record-option-{$source}-{$record_id}" class="record-action save-record-action">
			
			<xsl:call-template name="img_save_record">
				<xsl:with-param name="id" select="concat('folder-', $source, '-', $record_id)" />
				<xsl:with-param name="class">mini-icon save-record-link</xsl:with-param>
				<xsl:with-param name="test" select="$is_already_saved" />
			</xsl:call-template>
						
			<xsl:text> </xsl:text>	
			
			<a id="link-{$source}-{$record_id}" href="{../url_save_delete}">
				
				<xsl:attribute name="class">save-record
				
					<!-- 'saved' class used as a tag by ajaxy stuff -->
					<xsl:if test="$is_already_saved">
						<xsl:text> saved</xsl:text>
					</xsl:if>
				
				</xsl:attribute>
							
				<xsl:choose>
					<xsl:when test="$is_already_saved">
						<xsl:choose>
							<xsl:when test="//session/role = 'named'">
								<xsl:copy-of select="$text_results_record_saved" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:copy-of select="$text_results_record_saved_temp" />
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise><xsl:copy-of select="$text_results_record_save_it" /></xsl:otherwise>
				</xsl:choose>
			</a>
			
			<!-- temporary save note -->
			
			<xsl:if test="$is_already_saved and //request/session/role != 'named'"> 
				<span class="temporary-login-note">
					(<xsl:text> </xsl:text><a href="{//navbar/login_link}">
						<xsl:copy-of select="$text_results_record_saved_perm" />
					</a><xsl:text> </xsl:text>)
				</span>
			</xsl:if>
		</div>
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: HIDDEN TAG LAYERS
		These are used in the metasearch results (but not folder results because it already has some of these) 
		and record pages for the auto-complete tag input
	-->
	
	<xsl:template name="hidden_tag_layers">
		
		<div id="tag-suggestions" class="autocomplete" style="display:none;"></div>
	
		<div id="template-tag-input" class="results-label" style="display:none;">
			<xsl:call-template name="tag_input">
				<xsl:with-param name="id">template</xsl:with-param>
			</xsl:call-template> 
		</div>
	
		<div id="labels-master" class="folder-output" style="display: none">
			<xsl:call-template name="tags_display" />
		</div>
		
	</xsl:template>

	<!-- 
		TEMPLATE: TAGS DISPLAY
		used by a couple of pages in the folder area for displaying tags
	-->
	
	<xsl:template name="tags_display">
		
		<h2><xsl:copy-of select="$text_folder_options_tags" /></h2>
		<ul>
		<xsl:for-each select="tags/tag">
			<li>
			<xsl:choose>
				<xsl:when test="@label = //request/label">
					<strong><xsl:value-of select="@label" /></strong> ( <xsl:value-of select="@total" /> )
				</xsl:when>
				<xsl:otherwise>
					<a href="{@url}"><span class="label-list-item"><xsl:value-of select="@label" /></span></a> ( <xsl:value-of select="@total" /> )
				</xsl:otherwise>
			</xsl:choose>
			</li>
		</xsl:for-each>
		</ul>
		
	</xsl:template>


	<!--
		TEMPLATE: TAG INPUT
		tab/label input form used to enter labels/tags for saved record
	-->
	
	<xsl:template name="tag_input">
		<xsl:param name="record" select="." />
		<xsl:param name="id" select="$record/id" /> 
		<xsl:param name="context">the saved records page</xsl:param>
	
		<div class="folder-labels record-action" id="tag-input-div-{$id}">
			<form action="folder/tags_edit" method="get" class="tags">
			
				<!-- note that if this event is fired with ajax, the javascript changes
				the action element here to 'tags_edit_ajax' so the server knows to display a 
				different view, which the javascript captures and uses to update the totals above. -->
				
				<input type="hidden" name="lang" value="{//request/lang}" />
				<input type="hidden" name="record" value="{$id}" />
				<input type="hidden" name="context" value="{$context}" />
				
				<xsl:variable name="tag_list">
					<xsl:for-each select="$record/tag">
						<xsl:value-of select="text()" />
						<xsl:if test="following-sibling::tag">
							<xsl:text>, </xsl:text>
						</xsl:if>
					</xsl:for-each>
				</xsl:variable>
				
				<input type="hidden" name="tags-shaddow" id="shadow-{$id}" value="{$tag_list}" />
				
				<label for="tags-{$id}"><xsl:copy-of select="$text_records_tags" /></label>
				
				<input type="text" name="tags" id="tags-{$id}" class="tags-input" value="{$tag_list}" />			
				<xsl:text> </xsl:text>
				<input id="submit-{$id}" type="submit" name="submit-button" value="Update" class="tags-submit{$language_suffix}" />
			</form>
		</div>
		
	</xsl:template>

	<!--
		TEMPLATE: FULL TEXT LINKS
	-->
	
	<xsl:template name="full_text_links">
				
		<xsl:for-each select="links/link[@type = 'full']">
			
			<div class="record-action {@type}">
								
				<a href="{url}" target="{$link_target}" data-role="button">
				
					<xsl:choose>
						<xsl:when test="@format = 'pdf'">
							<xsl:call-template name="img_format_pdf">
								<xsl:with-param name="class">mini-icon full-text-link pdf</xsl:with-param>
							</xsl:call-template>
							<xsl:text> </xsl:text>
							<xsl:choose>
								<xsl:when test="display != ''">
									<xsl:value-of select="display" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy-of select="$text_records_fulltext_pdf" />
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:when test="@format = 'html'">
							<xsl:call-template name="img_format_html">
								<xsl:with-param name="class">mini-icon full-text-link html</xsl:with-param>
							</xsl:call-template>
							<xsl:text> </xsl:text>
							<xsl:choose>
								<xsl:when test="display != ''">
									<xsl:value-of select="display" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy-of select="$text_records_fulltext_html" />
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="img_format_unknown">
								<xsl:with-param name="class">mini-icon full-text-link unknown</xsl:with-param>
							</xsl:call-template>
							<xsl:text> </xsl:text>
							<xsl:choose>
								<xsl:when test="display != ''">
									<xsl:value-of select="display" />
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy-of select="$text_records_fulltext_available" />
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</a>
			
			</div>
			
		</xsl:for-each>
		
	</xsl:template>

	<!--
		TEMPLATE: HIDDEN SEARCH INPUTS
	-->
	
	<xsl:template name="hidden_search_inputs">
		<xsl:param name="exclude_search_params" />
		<xsl:param name="exclude_limit" />
		
		<xsl:if test="not($exclude_search_params)">
		
			<xsl:for-each select="//query/terms/term">
			
				<input type="hidden" name="boolean" value="{boolean}" />
				<input type="hidden" name="field" value="{field}" />
				<input type="hidden" name="relation" value="{relation}" />
				<input type="hidden" name="query" value="{query}" />
				
			</xsl:for-each>
			
			<input type="hidden" name="sort" value="{//request/sort}" />
			
		</xsl:if>
		
		<xsl:for-each select="//query/limits/limit">
			
			<xsl:if test="php:function('Application\View\Helper\Search::shouldIncludeLimit', string(field), string($exclude_limit))">
				<xsl:call-template name="hidden_search_limit" />
			</xsl:if>
			
		</xsl:for-each>					
	
	</xsl:template>
	
	<xsl:template name="hidden_search_limit">
	
		<xsl:choose>
			<xsl:when test="value/*">
				<xsl:for-each select="value/*">
					<input type="hidden" name="{../../param}" value="{text()}" />
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<input type="hidden" name="{param}" value="{value}" />
			</xsl:otherwise>
		</xsl:choose>	
	
	</xsl:template>

	<!-- search box fields overriden in templates -->
	
	<xsl:template name="advanced_search_option" />
	<xsl:template name="advanced_search" />
	<xsl:template name="searchbox_hidden_fields_module" />
	<xsl:template name="searchbox_hidden_fields_local" />
	
	<!-- additional record data overriden in templates -->
	
	<xsl:template name="additional_record_links" />
	<xsl:template name="additional_brief_record_data" />
	
	<!-- search results templates -->
	
	<xsl:template name="search_recommendations" />
	<xsl:template name="facet_narrow_results" />
	<xsl:template name="search_promo" />
	<xsl:template name="search_login_reminder" />
	<xsl:template name="search_sidebar_additional" />
	
</xsl:stylesheet>
