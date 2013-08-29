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
<xsl:import href="readinglist.xsl" />

<xsl:output method="html"/>

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="page_name" />

<xsl:template name="main">
		
	<xsl:if test="//lti/instructor = '1'">
	
		<ul class="courses-search-options">
			<li>
				<a href="{course_nav/url_search}" class="btn btn-large">
					<i class="icon-search"></i><xsl:text> </xsl:text>Search for new records
				</a>
			</li>
			<li>
				<a href="{course_nav/url_previously_saved}" class="btn btn-large">
					<i class="icon-folder-open-alt"></i><xsl:text> </xsl:text>Add previously saved records
				</a>
			</li>
		</ul>
		
	</xsl:if>

</xsl:template>
</xsl:stylesheet>
