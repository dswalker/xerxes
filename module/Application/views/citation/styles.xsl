<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2009 California State University
 version: $Id: styles.xsl 1028 2009-12-28 23:18:00Z dwalker@calstate.edu $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
<xsl:include href="utils.xsl" />
<xsl:output method="text" encoding="utf-8"/>

<xsl:template name="apa">
		
	<!-- if author is present -->
	
	<xsl:if test="primary_author">
		
		<!-- primary author -->
		
		<xsl:choose>
			<xsl:when test="authors/author[@rank='1']/aucorp">
				<xsl:value-of select="authors/author[@rank='1']/aucorp" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="authors/author[@rank='1']/aulast" /><xsl:text>,&#160;</xsl:text>
				<xsl:value-of select="substring(authors/author[@rank='1']/aufirst,1,1)" /><xsl:text>. </xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		
		<!-- editor -->
		
		<xsl:if test="authors/author[@rank='1']/@editor">
			<xsl:text> (Ed.).</xsl:text>
		</xsl:if>
		
		<!-- secondary authors -->
		
		<xsl:for-each select="authors/author[@rank &gt; 1 and not(aucorp)]">
			<xsl:choose>
				<xsl:when test="@rank &gt; 6">
				</xsl:when>				
				<xsl:when test="@rank = 6">
					<xsl:text>, et al.</xsl:text>
				</xsl:when>
				<xsl:when test="following-sibling::author">
					<xsl:text>, </xsl:text>
					<xsl:value-of select="aulast" /><xsl:text>,&#160;</xsl:text>
					<xsl:value-of select="substring(aufirst,1,1)" /><xsl:text>. </xsl:text>			
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>, &amp; </xsl:text>
					<xsl:value-of select="aulast" /><xsl:text>,&#160;</xsl:text>
					<xsl:value-of select="substring(aufirst,1,1)" /><xsl:text>. </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	
		<!-- date -->
		<xsl:text> (</xsl:text><xsl:value-of select="year" /><xsl:text>). </xsl:text>
	
	</xsl:if>
	
	<xsl:choose>
		<xsl:when test="journal">
		
			<!-- PERIODICAL -->
	
			<!-- title -->
			<xsl:if test="title_normalized">
				<xsl:value-of select="php:function('Xerxes_Framework_Parser::toSentenceCase', string(title_normalized))" /><xsl:text>. </xsl:text>
			</xsl:if>
				
			<!-- date if no author given-->
			<xsl:if test="not(primary_author)">
				<xsl:text> (</xsl:text><xsl:value-of select="year" /><xsl:text>). </xsl:text>
			</xsl:if>
			
			<!-- journal title -->
			<i><xsl:value-of select="journal_title" /></i><xsl:text>, </xsl:text>
			
			<!-- volume and issue -->
			
			<xsl:if test="volume">
				<i><xsl:value-of select="volume" /></i>
				
				<xsl:if test="issue">
					<xsl:text>(</xsl:text><xsl:value-of select="issue" /><xsl:text>)</xsl:text>
				</xsl:if>
				
				<xsl:text>, </xsl:text>
			</xsl:if>			
			
			<!-- pagination -->
			<xsl:choose>
				<xsl:when test="end_page and ( end_page != start_page )">
					<xsl:value-of select="start_page" />-<xsl:value-of select="end_page" />
				</xsl:when>
				<xsl:when test="start_page">
					<xsl:value-of select="start_page" />
				</xsl:when>
			</xsl:choose>
			<xsl:text>. </xsl:text>
	
		</xsl:when>
		<xsl:otherwise>
			<!-- NON-PERIODICAL (e.g., book, report, brochure, or audiovisual media) -->
			
			<!-- title -->
			<xsl:if test="title_normalized">
				<i><xsl:value-of select="title_normalized" /></i><xsl:text>. </xsl:text>
			</xsl:if>
	
			<!-- date if no author given-->					
			<xsl:if test="not(primary_author)">
				<xsl:text> (</xsl:text><xsl:value-of select="year" /><xsl:text>). </xsl:text>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="place != ''">
					<xsl:value-of select="place" /><xsl:text>:&#160;</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>n.p.: </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="publisher != ''">
				<xsl:value-of select="publisher" /><xsl:text>. </xsl:text>
			</xsl:if>
		</xsl:otherwise>
	</xsl:choose>
		
</xsl:template>


<xsl:template name="mla">

	<xsl:if test="primary_author">
		
		<!-- primary author -->
		
		<xsl:choose>
			<xsl:when test="authors/author[@rank='1']/aucorp">
				<xsl:value-of select="authors/author[@rank='1']/aucorp" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="authors/author[@rank='1']/aulast" /><xsl:text>,&#160;</xsl:text>
				<xsl:value-of select="authors/author[@rank='1']/aufirst" />
			</xsl:otherwise>
		</xsl:choose>
		
		<!-- editor -->
		
		<xsl:choose>
			<xsl:when test="authors/author[@rank='1']/@editor">
				<xsl:text>, ed. </xsl:text>
			</xsl:when>
			
			<xsl:when test="not(authors/author[@rank &gt; 1])">
				<xsl:text>. </xsl:text>
			</xsl:when>
		</xsl:choose>
		
		<!-- secondary authors -->
		
		<xsl:for-each select="authors/author[@rank &gt; 1 and not(aucorp)]">
			<xsl:choose>
				<xsl:when test="following-sibling::author">
					<xsl:text>, </xsl:text>
					<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
					<xsl:value-of select="aulast" />			
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>, and </xsl:text>
					<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
					<xsl:value-of select="aulast" /><xsl:text>. </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
		
	</xsl:if>
	
	<xsl:choose>
		<xsl:when test="journal">
		
			<!-- PERIODICAL -->
	
			<!-- title -->
			<xsl:if test="title_normalized">
				<xsl:variable name="dquot">"</xsl:variable>
				<xsl:variable name="quot">'</xsl:variable>
				<xsl:text> "</xsl:text><xsl:value-of select="translate(title_normalized,$dquot,$quot)" /><xsl:text>." </xsl:text>
			</xsl:if>
			
			<!-- journal title -->
			<i><xsl:value-of select="journal_title" /></i><xsl:text>, </xsl:text>
			
			<!-- volume and issue -->
			<xsl:if test="volume">
				<xsl:value-of select="volume" />
				
				<xsl:if test="issue">
					<xsl:text>.</xsl:text>
				</xsl:if>
			</xsl:if>
			
			<xsl:if test="issue">
				<xsl:value-of select="issue" />
			</xsl:if>
			
			<!-- date -->
			<xsl:text> (</xsl:text><xsl:value-of select="year" /><xsl:text>): </xsl:text>	
			
			<!-- pagination -->
			<xsl:choose>
				<xsl:when test="end_page and ( end_page != start_page )">
					<xsl:value-of select="start_page" />-<xsl:value-of select="end_page" /><xsl:text>. </xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="start_page" /><xsl:text>. </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
	
		</xsl:when>
		<xsl:otherwise>
			<!-- NON-PERIODICAL (e.g., book, report, brochure, or audiovisual media) -->
			
			<!-- title -->
			<xsl:if test="title_normalized">
				<i><xsl:value-of select="title_normalized" /></i><xsl:text>. </xsl:text>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="place != ''">
					<xsl:value-of select="place" /><xsl:text>: </xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>n.p.: </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="publisher != ''">
				<xsl:value-of select="publisher" /><xsl:text>, </xsl:text>
			</xsl:if>
			
			<xsl:text> </xsl:text><xsl:value-of select="year" /><xsl:text>. </xsl:text>
			
		</xsl:otherwise>
	</xsl:choose>
		
</xsl:template>


<xsl:template name="turabian">
		
	<!-- primary author -->
	
	<xsl:if test="primary_author">
	
		<xsl:choose>
			<xsl:when test="authors/author[@rank='1']/aucorp">
				<xsl:value-of select="authors/author[@rank='1']/aucorp" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="authors/author[@rank='1']/aulast" /><xsl:text>,&#160;</xsl:text>
				<xsl:value-of select="authors/author[@rank='1']/aufirst" />
			</xsl:otherwise>
		</xsl:choose>
		
		<!-- editor -->
		
		<xsl:choose>
			<xsl:when test="authors/author[@rank='1']/@editor">
				<xsl:text>, ed. </xsl:text>
			</xsl:when>
			
			<xsl:when test="not(authors/author[@rank &gt; 1])">
				<xsl:text>. </xsl:text>
			</xsl:when>
		</xsl:choose>
		
		<!-- secondary authors -->
		
		<xsl:for-each select="authors/author[@rank &gt; 1 and not(aucorp)]">
			<xsl:choose>
				<xsl:when test="following-sibling::author">
					<xsl:text>, </xsl:text>
					<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
					<xsl:value-of select="aulast" />			
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>, and </xsl:text>
					<xsl:value-of select="aufirst" /><xsl:text> </xsl:text>
					<xsl:value-of select="aulast" /><xsl:text>. </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	
	</xsl:if>
	
	<!-- date -->
	<xsl:value-of select="year" /><xsl:text>. </xsl:text>	
	
	<xsl:choose>
		<xsl:when test="journal">
		
			<!-- PERIODICAL -->
	
			<!-- title -->
			<xsl:if test="title_normalized">
				<xsl:value-of select="title_normalized" /><xsl:text>. </xsl:text>
			</xsl:if>
			
			<!-- journal title -->
			<i><xsl:value-of select="journal_title" /></i><xsl:text>. </xsl:text>
			
			<!-- volume and issue -->
			<xsl:if test="volume">
				<xsl:value-of select="volume" />
				
				<xsl:if test="issue">
					<xsl:text>, no. </xsl:text><xsl:value-of select="issue" />
				</xsl:if>
				
				<xsl:text>: </xsl:text>
			</xsl:if>
			
			<!-- pagination -->
			<xsl:choose>
				<xsl:when test="end_page and ( end_page != start_page )">
					<xsl:value-of select="start_page" />-<xsl:value-of select="end_page" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="start_page" />
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:text>. </xsl:text>
	
		</xsl:when>
		<xsl:otherwise>
			<!-- NON-PERIODICAL (e.g., book, report, brochure, or audiovisual media) -->
			
			<!-- title -->
			<xsl:if test="title_normalized">
				<i><xsl:value-of select="title_normalized" /></i><xsl:text>. </xsl:text>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="place != ''">
					<xsl:value-of select="place" /><xsl:text>: </xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>n.p.: </xsl:text>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="publisher != ''">
				<xsl:value-of select="publisher" /><xsl:text>. </xsl:text>
			</xsl:if>
			
		</xsl:otherwise>
	</xsl:choose>
		
</xsl:template>



</xsl:stylesheet>