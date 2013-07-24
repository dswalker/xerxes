<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Courses home page view
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	
<xsl:import href="../includes.xsl" />
<xsl:output method="html"/>

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="module_header">

	<xsl:choose>
		<xsl:when test="//lti/instructor = '1'">
			
			<style type="text/css">
				
				.reading-list-header {
					margin-bottom: 2em; 
					margin-top: 2em; 
					padding: 1em; 
					background-color: #efefef; 
					border: 1px solid #ccc;
				}
				
				.reading-list-item {
					padding: .5em;
					border: 1px solid #fff; 
				}
				
				.reading-list-highlight { 
					border: 1px solid #ccc; 
				}
				
				.reading-list-item-action {
					background-color: #eee; 
					padding: 10px; 
					margin: -6px; 
					margin-bottom: 10px;
					position: relative; 
					visibility: hidden;
				}
							
			</style>
			
		</xsl:when>
		<xsl:otherwise>

			<style type="text/css">
				
				.reading-list-item {
					padding: .5em;
					margin-bottom: .5em;
				}
							
			</style>		
		
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<xsl:template name="module_javascript">
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"  type="text/javascript"></script>
	<script src="{$base_include}/javascript/courses.js"  type="text/javascript"></script>
</xsl:template>

<xsl:template name="main">
		
	<xsl:if test="//lti/instructor = '1'">
		<div class="reading-list-header">

			<img src="{$base_url}/images/famfamfam/add.png" alt="" /> <xsl:text> </xsl:text>
			<a href="courses/select">Add records</a>
		</div>
	</xsl:if>

	<xsl:if test="results/records/record/xerxes_record">
	
		<div id="reading-list-content">
		
		<ul>
		
		<xsl:for-each select="results/records/record/xerxes_record">
		
			<li id="reader_list_{../id}" class="reading-list-item">
			
				<xsl:if test="//lti/instructor = '1'">
				
					<div class="reading-list-item-action">
						<img src="{$base_url}/images/famfamfam/arrow_out.png" alt="" />
						<div style="position: absolute; top: 0px; right: 10px">
							<a href="{../url_save_delete}"><img src="{$base_url}/images/delete.gif" alt="" /><xsl:text> </xsl:text>Remove</a>
						</div>
					</div>
				
				</xsl:if>	

				<div>
					<strong>
						<a href="{../url_open}" target="_blank"><xsl:value-of select="title_normalized" /></a>
					</strong>
					<xsl:text> </xsl:text>
				</div>
				<div>
					<xsl:value-of select="format/public" />
				</div>
				<div>
					<xsl:value-of select="journal" />
				</div>
				<div style="color: #555; font-size: 95%; margin-top: 1em">
					<xsl:value-of select="abstract" />
				</div>				
				
			</li>
			
		</xsl:for-each>
		
		</ul>
		
		</div>

	</xsl:if>

</xsl:template>
</xsl:stylesheet>
