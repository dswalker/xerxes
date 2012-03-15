<?xml version="1.0" encoding="UTF-8"?>

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
	
<xsl:import href="../includes.xsl" />
<xsl:import href="../search/record.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_search" />
	<xsl:value-of select="$text_search_record" />
</xsl:template>

<xsl:template name="page_name">
	<xsl:for-each select="/*/results/records/record/xerxes_record">
		<xsl:call-template name="record_title" />
	</xsl:for-each>
</xsl:template>

<xsl:template name="main">	
	<xsl:call-template name="record" />
</xsl:template>

	<xsl:template name="record_database">
	
		<xsl:choose>
			<xsl:when test="database_name">
				<div>
				<dt><xsl:copy-of select="$text_record_database" />:</dt>
				<dd>
					<xsl:value-of select="database_name" />
				</dd>
				</div>
			</xsl:when>
			<xsl:when test="publisher">
				<div>
				<dt>Source:</dt>
				<dd>
					<xsl:value-of select="publisher" />
				</dd>
				</div>
				
			</xsl:when>
		</xsl:choose>
		
	</xsl:template>

</xsl:stylesheet>