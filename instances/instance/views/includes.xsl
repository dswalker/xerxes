<?xml version="1.0" encoding="UTF-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--
 Local includes
 author: David Walker <dwalker@calstate.edu>
 
-->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<!-- Header -->

<xsl:template name="header_div" >

	<div id="hd-banner">
		<a href="{$base_url}" id="hd-banner-link">
			<xsl:value-of select="//config/application_name" />
		</a>		
			
		<xsl:if test="count(config/languages/language) &gt; 1">
			<div id="languages">
				<xsl:for-each select="navbar/languages/language">
					<a href="{@url}"><xsl:value-of select="@name" /></a>
					<xsl:if test="following-sibling::language">
						<xsl:text> | </xsl:text>
					</xsl:if>
				</xsl:for-each>
			</div>
		</xsl:if>
		
	</div>

</xsl:template>



<!-- Footer -->

<xsl:template name="footer_div">
	
</xsl:template>

<!-- 
	Add additional elements to the sidebar 
	
	you can limit which 'page' the item appears on by using the 'base' and 'action' request
	elements, as in the example below.  if you want it to appear on _every_ page then add the 
	elements _outside_ of any condition
	
-->

<xsl:template name="sidebar_additional">

	<xsl:choose>
		<xsl:when test="request/base = 'databases' and request/action = 'categories'">

			<!-- session_auth_info provides an example of giving the user their login/authentication details. -->
			
			<!-- <xsl:call-template name="session_auth_info" /> -->

			<!-- link to alpha database page, plus other examples -->
			
			<!--
			
			<div id="home_additional_options" class="box">
			
				<h2>Additional Options</h2>
				<ul>
					<li>
						<a>
						<xsl:attribute name="href"><xsl:value-of select="navbar/element[@id='database_list']/url" /></xsl:attribute>
						Database List (A-Z)
						</a>
					</li>
					<li>Ask a Librarian</li>
					<li>Example</li>
					<li>Another Example</li>
				</ul>
				
			</div>
			
			-->
		</xsl:when>
	</xsl:choose>
	
</xsl:template>

  <!-- If using the Umlaut feature, you want to set this to your branded
       link resolver name. -->
  <!-- <xsl:variable name="text_link_resolver_name">Link Resolver</xsl:variable> -->

</xsl:stylesheet>
