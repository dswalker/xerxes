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

<xsl:template name="module_header">

	<link href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" rel="stylesheet" media="screen" />
	<link href="{$base_include}/css/jquery.tagit.css" rel="stylesheet" type="text/css" />

	<style type="text/css">
		
		#database-form label.error {
			color: red;
			padding-top: 4px;
			display: inline-block;
			padding-left: 1em;
		}
		
		ul.tagit {
			margin-left: 0;
			width: 300px;
			border: 1px solid #ccc;
			font-size: 14px;
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
		}
		
	</style>

</xsl:template>

<xsl:template name="main">
	
	<xsl:choose>
		<xsl:when test="database">
			<xsl:for-each select="database">
				<xsl:call-template name="databases_edit" />
			</xsl:for-each>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="databases_edit" />
		</xsl:otherwise>
	</xsl:choose>
				
</xsl:template>

<xsl:template name="databases_edit">
	
	<h1>Database</h1>
	
	<div class="database-form-edit">
		
		<form class="form-horizontal" action="{//request/controller}/update-database" method="post" id="database-form">	
			<input type="hidden" name="postback" value="true" />
			<input type="hidden" name="id" value="{id}" />
		  
		  <div class="control-group">
			<label class="control-label" for="title">Title</label>
			<div class="controls">
			  <input type="text" name="title" class="input-long required" required="required" value="{title}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Link</label>
			<div class="controls">
			  <input type="text" name="link" class="input-long required" placeholder="to the database" required="required" value="{link}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Description</label>
			<div class="controls">
			  <textarea name="description" class="input-long" rows="8">
			  	<xsl:value-of select="description" />
			  </textarea>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Coverage</label>
			<div class="controls">
			  <input type="text" name="coverage" class="input-long" value="{coverage}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Active</label>
			<div class="controls">
			  <input type="checkbox" name="active">
			  	<xsl:if test="active">
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
			  </input>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Requires proxy</label>
			<div class="controls">
			   <input type="checkbox" name="proxy">
			    	<xsl:if test="proxy">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
			   </input>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Trial until</label>
			<div class="controls">
			  <input type="text" name="date_trial_expiry" class="datepicker" maxlength="10" size="10" 
			  	placeholder="date when trial is over" value="{substring(string(date_trial_expiry/date),1,10)}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">New until</label>
			<div class="controls">
			  <input type="text" name="date_new_expiry" class="datepicker" maxlength="10" size="10" 
			  	placeholder="date no longer new"  value="{substring(string(date_new_expiry/date),1,10)}" />					  
			</div>
		  </div>	
		  <div class="control-group">
			<label class="control-label">Keywords</label>
			<div class="controls">
			  <input name="keywords" id="form-keywords" class="input-long" data-original-title="Coverage" data-placement="right">
			  	<xsl:attribute name="value">
					<xsl:for-each select="keywords/keyword">
						<xsl:value-of select="text()" />
						<xsl:if test="following-sibling::keyword">
							<xsl:text>,</xsl:text>
						</xsl:if>
					</xsl:for-each>
				</xsl:attribute>
			  </input>			  	  
			</div>
		  </div>  
		  <div class="control-group">
			<label class="control-label">Creator</label>
			<div class="controls">
			  <input type="text" name="creator" class="input-long" value="{creator}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Publisher</label>
			<div class="controls">
			  <input type="text" name="publisher" class="input-long" value="{publisher}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Search hints</label>
			<div class="controls">
			  <textarea name="search-hints" class="input-long" rows="8">
			  	<xsl:value-of select="search_hints" />
			  </textarea>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Link guide</label>
			<div class="controls">
			  <input type="text" name="link-guide" class="input-long" placeholder="Link to a guide or instructions page" value="{link_guide}" />
			</div>
		  </div>
		  
		  <div style="padding: 20px; padding-left: 200px">
			  <button class="btn btn-large btn-primary" type="submit">Update</button>
		  </div>
		  
		</form>
	</div>
		
</xsl:template>

</xsl:stylesheet>
