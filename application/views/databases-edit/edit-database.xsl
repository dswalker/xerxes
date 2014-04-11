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

<xsl:import href="../includes.xsl" />

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

<xsl:template name="module_javascript">

	<script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
	<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js"></script>
	<script src="{$base_include}/javascript/tag-it.js" type="text/javascript" charset="utf-8"></script>
	<script>
		$(function() {
			$( ".datepicker" ).datepicker();
		});
		
		$(document).ready(function(){
			
			$('#form-keywords').tagit({
				'allowSpaces': true
			});
			
			$("#database-form").validate();
			
			$('#form-coverage').popover({
				html: true,
				content: function(ele) { return $('#popover-content').html(); }
			});			
			
		});
	</script>

</xsl:template>

<xsl:template name="main">
	
	<xsl:choose>
		<xsl:when test="databases">
			<xsl:for-each select="databases">
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
	
	<div style="margin: 4em">
		
		<form class="form-horizontal" action="{//request/controller}/update-database" method="post" id="database-form">	
			<input type="hidden" name="postback" value="true" />
			<input type="hidden" name="id" value="{id}" />
		  
		  <div class="control-group">
			<label class="control-label" for="title">Title</label>
			<div class="controls">
			  <input type="text" name="title" style="width:400px" required="required" class="required" value="{title}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Link</label>
			<div class="controls">
			  <input type="text" name="link" style="width:400px" placeholder="to the database" required="required" class="required" value="{link}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Description</label>
			<div class="controls">
			  <textarea name="description" style="width:400px" rows="8">
			  	<xsl:value-of select="description" />
			  </textarea>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Coverage</label>
			<div class="controls">
			  <input type="text" name="coverage" style="width:400px" value="{coverage}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Active</label>
			<div class="controls">
			  <input type="checkbox" name="active" checked="checked" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Requires proxy</label>
			<div class="controls">
			   <input type="checkbox" name="proxy" checked="checked" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Trial until</label>
			<div class="controls">
			  <input type="text" name="trial_new_expiry" class="datepicker" maxlength="10" size="10" 
			  	placeholder="date when trial is over" value="{trial_new_expiry/date}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">New until</label>
			<div class="controls">
			  <input type="text" name="date_new_expiry" class="datepicker" maxlength="10" size="10" 
			  	placeholder="date no longer new"  value="{date_new_expiry/date}" />					  
			</div>
		  </div>	
		  <div class="control-group">
			<label class="control-label">Keywords</label>
			<div class="controls">
			  <input name="keywords" id="form-keywords" style="width:400px" data-original-title="Coverage" data-placement="right" value="{keywords}" />			  	  
			</div>
		  </div>  
		  <div class="control-group">
			<label class="control-label">Creator</label>
			<div class="controls">
			  <input type="text" name="creator" style="width:400px" value="{creator}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Publisher</label>
			<div class="controls">
			  <input type="text" name="publisher" style="width:400px" value="{publisher}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Search hints</label>
			<div class="controls">
			  <textarea name="search-hints" style="width:400px" rows="8">
			  	<xsl:value-of select="search_hints" />
			  </textarea>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Link guide</label>
			<div class="controls">
			  <input type="text" name="link-guide" style="width:400px" placeholder="Link to a guide or instructions page" value="{link_guide}" />
			</div>
		  </div>
		  
		  <div style="padding: 20px; padding-left: 200px">
			  <button class="btn btn-large btn-primary" type="submit">Update</button>
		  </div>
		  
		</form>
	</div>
		
</xsl:template>

</xsl:stylesheet>
