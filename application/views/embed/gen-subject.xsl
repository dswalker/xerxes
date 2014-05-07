<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet  [
	<!ENTITY nbsp   "&#160;">
	<!ENTITY copy   "&#169;">
	<!ENTITY reg    "&#174;">
	<!ENTITY times  "&#215;">
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

 Databases search page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="subject.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="page_name">
	<xsl:value-of select="$text_header_snippet_generate" />
</xsl:template>

<xsl:template name="breadcrumb" />

<xsl:template name="main">

	<h1><xsl:call-template name="page_name" />: <xsl:value-of select="category/name" /></h1>
	
	<div class="row-fluid">
	
		<div class="span4">
			
			<div class="sidebar">
			
				<div id="snippet-control">
			
					<form method="get" id="generator" action="{request/controller}/{request/action}">
						<input type="hidden" name="subject" value="{category/normalized}" />
					
						<h2><xsl:copy-of select="$text_snippet_display_options" /></h2>
							 
							<table id="snippet-display-table" summary="{$text_ada_table_for_display}">
								<tr>
								<td><label for="disp_show_title"><xsl:copy-of select="$text_snippet_show_title" /></label></td>
								<td>
									<select id="disp_show_title" name="disp_show_title">
										<option value="true">
											<xsl:if test="request/disp_show_title = 'true'">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="$text_snippet_display_yes" />
										</option>
										<option value="false">
											<xsl:if test="request/disp_show_title = 'false'">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="$text_snippet_display_no" />
										</option>
									</select>
								</td>
								</tr>
								<tr>
								<td><label for="disp_show_search"><xsl:copy-of select="$text_snippet_show_searchbox" /></label></td>
								<td>
									<select name="disp_show_search" id="disp_show_search">
										<option value="true">
											<xsl:if test="request/disp_show_search = 'true'">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="$text_snippet_display_yes" />
										</option>
										<option value="false">
											<xsl:if test="request/disp_show_search = 'false'">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="$text_snippet_display_no" />
										</option>
									</select>
								
								</td>
							</tr>
							<tr>
							<td><label for="disp_show_subcategories"><xsl:copy-of select="$text_snippet_show_databases" /></label></td>
							<td>
								<select name="disp_show_subcategories" id="disp_show_subcategories">
									<option value="true">
										<xsl:if test="request/disp_show_subcategories = 'true'">
											<xsl:attribute name="selected">selected</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="$text_snippet_display_yes" />
									</option>
									<option value="false">
										<xsl:if test="request/disp_show_subcategories = 'false'">
											<xsl:attribute name="selected">selected</xsl:attribute>
										</xsl:if>      
										<xsl:value-of select="$text_snippet_display_no" />
									</option>
								</select>
							</td>
							</tr>
							<tr>
							<td colspan="2">
								<label for="disp_only_subcategory"><xsl:copy-of select="$text_snippet_show_section" /></label>
								<div id="snippet-subcategories">
									<select name="disp_only_subcategory" id="disp_only_subcategory">
										<option value=""><xsl:value-of select="$text_snippet_display_all" /></option>
										<xsl:for-each select="//subcategory[not(sidebar) or sidebar = 0]">
											<option>
												<xsl:if test="//request/disp_only_subcategory = id or //request/disp_only_subcategory = source_id">
													<xsl:attribute name="selected">selected</xsl:attribute>
												</xsl:if>
												<xsl:attribute name="value">
													<xsl:value-of select="id" />
												</xsl:attribute>
												<xsl:value-of select="name" />
											</option>
										</xsl:for-each>
									</select>
								</div>
							</td>
							</tr>
							<tr>
							<td><label for="disp_embed_css"><xsl:copy-of select="$text_snippet_show_css" /></label></td>
							<td>
								<select id="disp_embed_css" name="disp_embed_css">
									<option value="true">
										<xsl:if test="request/disp_embed_css = 'true'">
											<xsl:attribute name="selected">selected</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="$text_snippet_display_yes" />
									</option>
									<option value="false">
										<xsl:if test="request/disp_embed_css = 'false'">
											<xsl:attribute name="selected">selected</xsl:attribute>
										</xsl:if>      
										<xsl:value-of select="$text_snippet_display_no" />
									</option>
								</select>
							</td>
							</tr>
						</table>
	
						<p class="option-info"><xsl:copy-of select="$text_snippet_show_css_explain" /></p>
						
						<p><input type="submit" value="{$text_snippet_refresh}" class="btn embed-subject-refresh{$language_suffix}" /></p>
	
						
						<div id="snippet-include">
						
							<h2><xsl:copy-of select="$text_snippet_include_options" /></h2>
							
							<h3>1. <label for="direct_url_content"><xsl:copy-of select="$text_snippet_include_server" /></label></h3>
							<p><xsl:copy-of select="$text_snippet_include_server_explain" /></p>	
					
							<textarea id="direct_url_content" readonly="yes" class="display-textbox">
								<xsl:value-of select="embed_info/server_side_url" />
							</textarea> 
					
							<h3>2. <label for="js_widget_content"><xsl:copy-of select="$text_snippet_include_javascript" /></label></h3>
							<p><xsl:copy-of select="$text_snippet_include_javascript_explain" /></p>
							
							<textarea id="js-widget-content" readonly="yes" class="display-textbox">
								<script type="text/javascript" charset="utf-8" >
									<xsl:attribute name="src"><xsl:value-of select="embed_info/javascript_url"/></xsl:attribute>
								</script>
								<noscript>
									<!-- <xsl:copy-of select="$noscript_content" /> -->
								</noscript>
							</textarea>
					
							<h3>3. <xsl:copy-of select="$text_snippet_include_html" /></h3>
							<p><xsl:copy-of select="$text_snippet_include_html_explain" /></p>
							
							<a target="_blank" id="view-source-link" href="{embed_info/server_side_url};format=text">
								<xsl:copy-of select="$text_snippet_include_html_source" />
							</a>
						</div>
		
					</form>
					
				</div>
			</div>		
		</div>
		
		<div class="span8">
		
			<h2><xsl:copy-of select="$text_snippet_example" /></h2>
			
			<xsl:call-template name="display_category" />
		
		</div>
	</div>

</xsl:template>
</xsl:stylesheet>
