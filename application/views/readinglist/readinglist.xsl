<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Override summon results
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

	<xsl:template name="module_header">
		<style type="text/css">
		
			#bd-top {
				display: none;
			}
			.account {
				visibility: hidden;
			}
			.action- h1 {
				display: none;
			}
			.courses-search-options li {
				margin: 2em;
			}
			.readmore-js-collapsed {
				margin-bottom: 10px;
			}
			a.readmore-js-toggle, a.readmore-js-toggle:visited  {
				font-size: 90%;
				color: green;
			}
			.abstract {
				color: #555;
				margin-top: 1em;
			}
			
		</style>

		<xsl:choose>
			<xsl:when test="//lti/instructor = '1'">
				
				<style type="text/css">
					
					.reading-list-header {
						margin-bottom: 2em; 
						padding: 1em; 
						background-color: #efefef; 
						border: 1px solid #ccc;
					}
					.reading-list-item {
						padding: .5em;
						border: 1px solid #fff;
						max-width: 650px;
					}
					.reading-list-item .title {
						margin-bottom: 7px;
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
					.reading-group input, .reading-group textarea {
						width: 500px;
					}
					.courses-search-options li {
						margin: 2em;
					}
								
				</style>
				
			</xsl:when>
			<xsl:otherwise>
	
				<style type="text/css">
						
					.reading-list-item {
						padding: 1em;
						margin-bottom: 1em;
						max-width: 650px;
					}
								
				</style>		
			
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>

	<xsl:template name="save_record">

		<xsl:variable name="source" select="source" />
		<xsl:variable name="record_id" select="record_id" />
		
		<!-- @todo: move this to the controller? -->
		
		<xsl:variable name="is_already_saved" select="//request/session/resultssaved[@key = $record_id]" />
	
		<div id="save-record-option-{$source}-{$record_id}" class="record-action save-record-action btn btn-primary">
								
			<xsl:text> </xsl:text>	
			
			<a id="link-{$source}-{$record_id}" href="{../url_save_delete}" style="color:#fff">
				
				<xsl:attribute name="class">save-record
				
					<!-- 'saved' class used as a tag by ajaxy stuff -->
					<xsl:if test="$is_already_saved">
						<xsl:text> saved</xsl:text>
					</xsl:if>
				
				</xsl:attribute>
							
				<xsl:choose>
					<xsl:when test="$is_already_saved">
						<xsl:value-of select="$text_readinglist_saved" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$text_readinglist_add" />
					</xsl:otherwise>
				</xsl:choose>
			</a>
			
		</div>
			
	</xsl:template>
	
	<xsl:template name="breadcrumb">
		<!-- <xsl:value-of select="$text_readinglist_breadcrumb" /> -->
	</xsl:template>
	
	<xsl:template name="instructor_search_options">
	
		<xsl:if test="//lti/instructor = '1'">
		
			<ul class="courses-search-options">
				<li>
					<a href="{course_nav/url_search}" class="btn btn-large">
						<i class="icon-search"></i><xsl:text> </xsl:text><xsl:value-of select="$text_readinglist_search" />
					</a>
				</li>
				<li>
					<a href="{course_nav/url_previously_saved}" class="btn btn-large">
						<i class="icon-folder-open-alt"></i><xsl:text> </xsl:text><xsl:value-of select="$text_readinglist_add_saved" />
					</a>
				</li>
			</ul>
			
		</xsl:if>	
	
	</xsl:template>
			
</xsl:stylesheet>
