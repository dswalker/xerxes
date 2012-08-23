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
	
	<xsl:include href="results.xsl" />
	<xsl:include href="../citation/styles.xsl" />
	
	<!--
		TEMPLATE: SIDEBAR
	-->		
	
	<xsl:template name="sidebar">
		<xsl:call-template name="citation" />
	</xsl:template>

	<!--
		TEMPLATE: RECORD
	-->	

	<xsl:template name="record">
		<div id="record">
		
			<xsl:for-each select="/*/results/records/record/xerxes_record">
			
				<xsl:choose>
					<xsl:when test="dothis = 'later'">
					
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="record_basic" />
					</xsl:otherwise>
				</xsl:choose>
				
			</xsl:for-each>
				
			<!-- tag input -->
			
			<xsl:call-template name="hidden_tag_layers" />
		</div>
	</xsl:template>
	
	<!--
		TEMPLATE: RECORD BASIC
	-->			
	
	<xsl:template name="record_basic">
	
		<!-- Title -->
		
		<h1><xsl:call-template name="record_title" /></h1>
		
		<!-- Basic record information (Author, Year, Format, Database, ...) -->
		
		<xsl:call-template name="record_summary" />
					
		<!-- A box with actions for current record (get full-text, link to holdings, save record) -->
		
		<xsl:call-template name="record_actions" />
		
		<!-- Umlaut stuff -->
		
		<xsl:call-template name="umlaut" />

		<!-- Detailed record information (Summary, Topics, Standard numbers, ...) -->
		
		<xsl:call-template name="record_details" />	
	
	</xsl:template>
	
	<!--
		TEMPLATE: RECORD TITLE
	-->		
	
	<xsl:template name="record_title">
		<xsl:value-of select="title_normalized" />
	</xsl:template>

	<!--
		TEMPLATE: RECORD SUMMARY
	-->	
	
	<xsl:template name="record_summary">
		<dl>
			<xsl:call-template name="additional_full_record_data_main_top" />
			<xsl:call-template name="record_uniform-title" /> <!-- uniform title -->
			<xsl:call-template name="record_authors_top" /> <!-- author wrapper -->
			<xsl:call-template name="record_conference" /> <!-- Conference -->
			<xsl:call-template name="record_format" /> <!-- Format -->
			<xsl:call-template name="record_year" /> <!-- Year -->
			<xsl:call-template name="record_institution" /> <!-- Institution -->
			<xsl:call-template name="record_degree" /> <!-- Degree -->
			<xsl:call-template name="record_source" /> <!-- Source -->
			<xsl:call-template name="record_database" /> <!-- Database -->
			<xsl:call-template name="additional_full_record_data_main_bottom" />
		</dl>
	</xsl:template>

	<!--
		TEMPLATE: RECORD ACTIONS
	-->	
	
	<xsl:template name="record_actions">
		
		<div id="record-full-text" class="raised-box record-actions">
			<xsl:call-template name="record_action_fulltext" />
			<xsl:call-template name="save_record" />
			<xsl:call-template name="additional_full_record_actions" />
		</div>
	</xsl:template>

	<!--
		TEMPLATE: UMLAUT
	-->

	<xsl:template name="umlaut">	
	
		<div id="library_copies" class="umlaut_content" style="display:none;"></div>
		<div id="document_delivery" class="umlaut_content" style="display:none;"></div>
		<div id="search_inside" class="umlaut_content" style="display:none;"></div>
		<div id="limited_preview" class="umlaut_content" style="display:none"></div>
	
	</xsl:template>

	<!--
		TEMPLATE: RECORD UNIFORM TITLE
	-->	

	<xsl:template name="record_uniform-title">
		<xsl:if test="uniform_title">
			<div>
			<dt>Uniform title:</dt>
			<dd>
				<xsl:value-of select="uniform_title" />
			</dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD AUTHORS TOP
	-->	

	<xsl:template name="record_authors_top">
	
		<xsl:call-template name="record_authors" /> <!-- Authors -->
		<xsl:call-template name="record_corp_authors" /> <!-- Corp. Authors -->
		
	</xsl:template>

	<!--
		TEMPLATE: RECORD AUTHORS
	-->	
	
	<xsl:template name="record_authors">
	
		<xsl:if test="authors/author[@type = 'personal']">
			<div>
			<dt><xsl:copy-of select="$text_results_author" />:</dt>
			<dd>
				<xsl:for-each select="authors/author[@type = 'personal']">
									
					<a href="{url}">
						<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
						<xsl:value-of select="auinit" /><xsl:text> </xsl:text>
						<xsl:value-of select="aulast" /><xsl:text> </xsl:text>
					</a>
					
					<xsl:if test="following-sibling::author[@type = 'personal']">
						<xsl:text> ; </xsl:text>
					</xsl:if>
					
				</xsl:for-each>
			</dd>
			</div>
		</xsl:if>
		
	</xsl:template>

	<!--
		TEMPLATE: RECORD CORPORATE AUTHORS
	-->	
	
	<xsl:template name="record_corp_authors">
		<xsl:if test="authors/author[@type = 'corporate']">
			<div>
			<dt><xsl:copy-of select="$text_record_author_corp" />:</dt>
			<dd>
				<xsl:for-each select="authors/author[@type = 'corporate']">
				
					<a href="{url}">
						<xsl:value-of select="aucorp" /><xsl:text> </xsl:text>
					</a>
					
					<xsl:if test="following-sibling::author[@type = 'corporate']">
						<xsl:text> ; </xsl:text>
					</xsl:if>
				</xsl:for-each>
			</dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD CONFERENCE AUTHORS
	-->	
	
	<xsl:template name="record_conference">
		<xsl:if test="authors/author[@type = 'conference']">
			<div>
			<dt><xsl:copy-of select="$text_record_conf" />:</dt>
			<dd>
				<xsl:for-each select="authors/author[@type = 'conference']">
					
					<xsl:value-of select="aucorp" /><xsl:text> </xsl:text>
					
					<xsl:if test="following-sibling::author[@type = 'conference']">
						<br />
					</xsl:if>
				</xsl:for-each>
			</dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD FORMAT
	-->	
	
	<xsl:template name="record_format">
		<xsl:if test="format">
			<div>
			<dt><xsl:copy-of select="$text_record_format_label" />:</dt>
			<dd>
				<xsl:call-template name="text_results_format">
					<xsl:with-param name="format" select="format/public" />
				</xsl:call-template>

				<xsl:if test="refereed = 1 and not(contains(format/internal,'Review'))">
					<xsl:text> </xsl:text><xsl:call-template name="img_refereed" />
					<xsl:text> </xsl:text><strong><xsl:copy-of select="$text_results_refereed" /></strong>
				</xsl:if>
			</dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD YEAR
	-->	
	
	<xsl:template name="record_year">
		<xsl:if test="year">
			<div>
			<dt><xsl:copy-of select="$text_results_year" />:</dt>
			<dd><xsl:value-of select="year" /></dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD INSTITUTION
	-->	
	
	<xsl:template name="record_institution">
		<xsl:if test="institution">
			<div>
			<dt><xsl:copy-of select="$text_record_inst" />:</dt>
			<dd><xsl:value-of select="institution" /></dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD DEGREE
	-->	
	
	<xsl:template name="record_degree">
		<xsl:if test="degree">
			<div>
			<dt><xsl:copy-of select="$text_record_degree" />:</dt>
			<dd><xsl:value-of select="degree" /></dd>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD SOURCE
	-->	
	
	<xsl:template name="record_source">
		<div>
		<xsl:choose>
			<xsl:when test="journal">
				<dt><xsl:copy-of select="$text_results_published_in" />:</dt>
				<dd>
					<xsl:choose>
						<xsl:when test="book_title">
							<xsl:value-of select="book_title" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="journal" />
						</xsl:otherwise>
					</xsl:choose>
				</dd>
				<xsl:if test="format/internal = 'CHAP'">
					<xsl:if test="publisher">
						<dt><xsl:copy-of select="$text_record_publisher" />:</dt>
						<dd>
							<xsl:value-of select="place" /><xsl:text>: </xsl:text>
							<xsl:value-of select="publisher" /><xsl:text>, </xsl:text>
							<xsl:value-of select="year" />
						</dd>
					</xsl:if>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="publisher">
					<dt><xsl:copy-of select="$text_record_publisher" />:</dt>
					<dd>
						<xsl:value-of select="place" /><xsl:text>: </xsl:text>
						<xsl:value-of select="publisher" /><xsl:text>, </xsl:text>
						<xsl:value-of select="year" />
					</dd>
				</xsl:if>
				<xsl:call-template name="description" /> <!-- extent and stuff -->
			</xsl:otherwise>
		</xsl:choose>
		</div>
	</xsl:template>

	<!--
		TEMPLATE: RECORD DATABASE
	-->	
	
	<xsl:template name="record_database">
	
		<xsl:if test="database_name">
			<div>
			<dt><xsl:copy-of select="$text_record_database" />:</dt>
			<dd>
				<xsl:value-of select="database_name" />
			</dd>
			</div>
		</xsl:if>
		
	</xsl:template>

	<!-- 
		TEMPLATE: RECORD ACTION FULL TEXT
	-->
	
	<xsl:template name="record_action_fulltext">
	
		<div id="umlaut_fulltext" class="umlaut_content" style="display:none;"></div>
		
		<xsl:call-template name="full_text_options">
			<xsl:with-param name="show_full_text_and_link_resolver">true</xsl:with-param> 
		</xsl:call-template>
		
	</xsl:template>
	
	<!--
		TEMPLATE: RECORD DETAILS
	-->
	
	<xsl:template name="record_details">
	
		<xsl:call-template name="record_abstract" />
		<xsl:call-template name="record_recommendations" />
		<xsl:call-template name="record_subjects" />
		<xsl:call-template name="record_toc" />
		<xsl:call-template name="record_authors_bottom" />
		<xsl:call-template name="record_language" />
		<xsl:call-template name="record_standard_numbers" />
		<xsl:call-template name="record_notes" />
		<xsl:call-template name="additional-title-info" />
		
	</xsl:template>	

	<!--
		TEMPLATE: RECORD ABSTRACT
	-->
	
	<xsl:template name="record_abstract">
		<xsl:if test="abstract">
			<h2><xsl:copy-of select="$text_record_summary" /></h2>
			<div class="record-abstract">
				<xsl:value-of select="abstract" />
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD RECOMMENDATIONS
	-->
	
	<xsl:template name="record_recommendations">
		<xsl:if test="//recommendations/recommendation">
		
			<h2><xsl:call-template name="text_recommendation_header" />:</h2>
			<ul id="recommendations">
				<xsl:for-each select="//recommendations/recommendation/xerxes_record">
					<li class="result">
						<a class="results-title" href="{../url_open}"><xsl:value-of select="title_normalized" /></a>
						<div class="results-info">
							<div class="results-type">
								<xsl:call-template name="text_results_format">
									<xsl:with-param name="format" select="format/public" />
								</xsl:call-template>
							</div>
							
							<xsl:value-of select="$text_results_author" /><xsl:text> </xsl:text><xsl:value-of select="primary_author" /><br />
							<xsl:value-of select="journal" />
							<xsl:call-template name="full_text_options" />
						</div>
					</li>
				</xsl:for-each>	
			</ul>	
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD TABLE OF CONTENTS
	-->
	
	<xsl:template name="record_toc">
		<xsl:if test="toc">
			<h2>
				<xsl:choose>
					<xsl:when test="format/normalized = 'BOOK'">
						<xsl:copy-of select="$text_record_chapters" />:
					</xsl:when>
					<xsl:otherwise>
						<xsl:copy-of select="$text_record_contents" />:
					</xsl:otherwise>
				</xsl:choose>
			</h2>
			<div class="record-abstract">
				<ul>
				<xsl:for-each select="toc/chapter">
					<li>
						<xsl:choose>
							<xsl:when test="statement">
								<xsl:value-of select="statement" />
							</xsl:when>
							<xsl:otherwise>
								<em><xsl:value-of select="title" /></em>
								<xsl:text> </xsl:text><xsl:copy-of select="$text_results_author" /><xsl:text> </xsl:text>
								<xsl:value-of select="author" />
							</xsl:otherwise>
						</xsl:choose>
					</li>
				</xsl:for-each>
				</ul>
			</div>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD SUBJECTS
	-->
	
	<xsl:template name="record_subjects">
		<xsl:if test="subjects">
			<h2><xsl:copy-of select="$text_record_subjects" />:</h2>
			<ul>
				<xsl:for-each select="subjects/subject">
					<li><a href="{url}"><xsl:value-of select="display" /></a></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
	
	<!--
		TEMPLATE: RECORD LANGUAGE
	-->
	
	<xsl:template name="record_language">
		<xsl:if test="language">
		
			<div>
				<dt><xsl:copy-of select="$text_record_language_label" />:</dt>
				<dd><xsl:value-of select="language" /></dd>
			</div>
			
		</xsl:if>
	</xsl:template>	
	
	<!--
		TEMPLATE: RECORD STANDARD NUMBERS
	-->	
	
	<xsl:template name="record_standard_numbers">
	
		<xsl:if test="standard_numbers">

			<h2>Standard Numbers</h2>
			<ul>
			<xsl:for-each select="standard_numbers/issn">
				<li>ISSN: <xsl:value-of select="text()" /></li>
			</xsl:for-each>
			<xsl:for-each select="standard_numbers/isbn">
				<li>ISBN: <xsl:value-of select="text()" /></li>
			</xsl:for-each>
			<xsl:for-each select="standard_numbers/gpo">
				<li>GPO Item: <xsl:value-of select="text()" /></li>
			</xsl:for-each>
			<xsl:for-each select="standard_numbers/govdoc">
				<li>Gov Doc: <xsl:value-of select="text()" /></li>
			</xsl:for-each>
			<xsl:for-each select="standard_numbers/oclc">
				<li>OCLC: <xsl:value-of select="text()" /></li>
			</xsl:for-each>
			</ul>

		</xsl:if>
		
	</xsl:template>

	<!--
		TEMPLATE: RECORD NOTES
	-->

	<xsl:template name="record_notes">
		<xsl:if test="notes">
			<h2><xsl:copy-of select="$text_record_notes" />:</h2>
			<ul>
				<xsl:for-each select="notes/note">
					<li><xsl:value-of select="text()" /></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>

	<!--
		TEMPLATE: RECORD NOTES
	-->

	<xsl:template name="description">
		<xsl:if test="description">
			<div>
				<dt>Description:</dt>
				<dd><xsl:value-of select="description" /></dd>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!--
		TEMPLATE: ADDITIONAL TITLE INFO
	-->	
	
	<xsl:template name="additional-title-info">
		<xsl:call-template name="alternate-titles" />
		<xsl:call-template name="additional-titles" />
		<xsl:call-template name="related-journal-titles" />
		<xsl:call-template name="series" />
	</xsl:template>

	<!--
		TEMPLATE: ALTERNATE TITLES
	-->
	
	<xsl:template name="alternate-titles">
		
		<xsl:if test="alternate_titles">

			<h2>Alternate titles</h2>
			<ul>
				<xsl:for-each select="alternate_titles/alternate_title">
					<li><xsl:value-of select="text()" /></li>
				</xsl:for-each>
			</ul>
			
		</xsl:if>
		
	</xsl:template>
			
	<!--
		TEMPLATE: ADDITIONAL TITLES
	-->
	
	<xsl:template name="additional-titles">
		
		<xsl:if test="additional_titles">

				<h2>Additional titles</h2>
				<ul>
					<xsl:for-each select="additional_titles/additional_title">
						<li><xsl:value-of select="text()" /></li>
					</xsl:for-each>
				</ul>
			
		</xsl:if>
		
	</xsl:template>


	<!--
		TEMPLATE: RELATED JOURNAL TITLES
	-->
	
	<xsl:template name="related-journal-titles">
		
		<xsl:if test="journal_title_continues">

			<h2>Continues</h2>
			<ul>
				<xsl:for-each select="journal_title_continues">
					<li><xsl:value-of select="journal_title_continue" /></li>
				</xsl:for-each>
			</ul>

		</xsl:if>

		<xsl:if test="journal_title_continued_by">


			<h2>Continued by</h2>
			<ul>
				<xsl:for-each select="journal_title_continued_by/journal_title_continued_by">
					<li><xsl:value-of select="text()" /></li>
				</xsl:for-each>
			</ul>

			
		</xsl:if>
		
	</xsl:template>

	<!--
		TEMPLATE: SERIES
	-->		

	<xsl:template name="series">

		<xsl:if test="series">

			<h2>Series</h2>
			<ul>
				<xsl:for-each select="series/serie">
					<li><xsl:value-of select="text()" /></li>
				</xsl:for-each>
			</ul>

			
		</xsl:if>		

	</xsl:template>	

<!-- additional record data overriden in templates -->

<xsl:template name="additional_full_record_data_main_top" />
<xsl:template name="additional_full_record_data_main_bottom" />
<xsl:template name="additional_full_record_actions" />
<xsl:template name="record_authors_bottom" />










<!-- 	
	TEMPLATE: CITATION
	record cited in all available citation styles
	for inclusion on record pages (where xerxes_record is available)
-->

<xsl:template name="citation">
	<div id="citation1" class="box">
    
		<xsl:for-each select="//records/record/xerxes_record">
		
			<h2>
				<xsl:copy-of select="$text_record_cite_this" /><xsl:text> </xsl:text>
				<xsl:call-template name="text_results_format">
					<xsl:with-param name="format" select="format/public" />
				</xsl:call-template>
				<xsl:text> :</xsl:text>
			</h2>
			
			<div class="citation" id="citation-apa">
			
				<h3><xsl:value-of select="$text_citation_apa" /></h3>
				<p class="citation-style">
					<xsl:call-template name="apa" />
				</p>
				
			</div>
			
			<div class="citation" id="citation-mla">
				
				<h3><xsl:value-of select="$text_citation_mla" /></h3>
				<p class="citation-style">
					<xsl:call-template name="mla" />
				</p>
				
			</div>
			
			<div class="citation" id="citation-turabian">
				
				<h3><xsl:value-of select="$text_citation_turabian" /></h3>
				<p class="citation-style">
					<xsl:call-template name="turabian" />
				</p>
		
			</div>
		
			<p id="citation-note">
				<xsl:copy-of select="$text_record_citation_note" />
			</p>
			
		</xsl:for-each>
	</div>
</xsl:template>


</xsl:stylesheet>