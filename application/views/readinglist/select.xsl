<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Course select view
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../folder/results.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="main">

	<div id="export">

		<form action="{request/controller}/assign" method="get">
			<input type="hidden" name="course_id" value="{//request/course_id}" />
			
			<h1><xsl:call-template name="page_name" /></h1>	
	
			<xsl:call-template name="folder_records_table" />
			
			<div style="margin: 3em">
				<button id="courses-import" type="submit" class="btn btn-success" name="action" value="delete">
					<i class="icon-plus"></i><xsl:text> </xsl:text>Add to Course
				</button>
			</div>	
		
		</form>

	</div>
	
</xsl:template>

</xsl:stylesheet>
