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

 Edit: Databases search page
 author: David Walker <dwalker@calstate.edu>
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<a href="databases-edit">Databases</a>
</xsl:template>

<xsl:template name="module_nav"></xsl:template>

<xsl:template name="main">
	
	<xsl:choose>
		<xsl:when test="librarians">
			<xsl:for-each select="librarians">
				<xsl:call-template name="librarian_edit" />
			</xsl:for-each>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="librarian_edit" />
		</xsl:otherwise>
	</xsl:choose>
				
</xsl:template>

<xsl:template name="librarian_edit">
	
	<h1>Librarian</h1>
	
	<div style="margin: 4em">
		
		<form class="form-horizontal" action="{//request/controller}/update-librarian" method="post" id="librarian-form">	
			<input type="hidden" name="postback" value="true" />
			<input type="hidden" name="id" value="{id}" />
		  
		  <div class="control-group">
			<label class="control-label" for="title">Name</label>
			<div class="controls">
			  <input type="text" name="name" style="width:400px" required="required" class="required" value="{name}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Link</label>
			<div class="controls">
			  <input type="text" name="link" style="width:400px" placeholder="to librarian page" required="required" class="required" value="{link}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Image</label>
			<div class="controls">
			  <input type="text" name="image" style="width:400px" placeholder="link to picture of librarian" value="{image}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Email</label>
			<div class="controls">
			   <input type="text" name="email" style="width:400px" value="{email}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Phone</label>
			<div class="controls">
			   <input type="text" name="phone" style="width:400px" value="{phone}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Office</label>
			<div class="controls">
			   <input type="text" name="office" style="width:400px" value="{office}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Office hours</label>
			<div class="controls">
			   <input type="text" name="office_hours" style="width:400px" value="{office_hours}" />
			</div>
		  </div>
		  
		  <div style="padding: 20px; padding-left: 200px">
			  <button class="btn btn-large btn-primary" type="submit">Update</button>
		  </div>
		  
		</form>
	</div>
		
</xsl:template>

</xsl:stylesheet>
