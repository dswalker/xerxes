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
<xsl:import href="includes.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
	<xsl:call-template name="surround">
		<xsl:with-param name="surround_template">none</xsl:with-param>
		<xsl:with-param name="sidebar">none</xsl:with-param>
	</xsl:call-template>
</xsl:template>

<xsl:template name="breadcrumb">
	<xsl:call-template name="breadcrumb_start" />
	<a href="databases-edit">Databases</a>
</xsl:template>

<xsl:template name="module_nav"></xsl:template>

<xsl:template name="module_header">

	<link href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" rel="stylesheet" media="screen" />
	<link href="{$base_include}/css/jquery.tagit.css" rel="stylesheet" type="text/css" />

	<xsl:call-template name="databases_css" />

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
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Type of database"
					data-content="This is a semi-controlled field.  As you type in a value, Xerxes will attempt to auto-complete based on previous values you've added, and in that way you can select from those controlled list of terms.  To see all available terms, simply type a space in the field. To add a new type, simply type in a new value." >
					Type
				</a>
			</label>
			<div class="controls">
			  <input type="text" id="type" name="type" class="input-long" value="{type}">
			  	<xsl:attribute name="data-source">
					<xsl:for-each select="//database_types/database_type">
						<xsl:text> </xsl:text> <!-- space here necessary for 'enter space to see all terms' hack -->
						<xsl:value-of select="type" />
						<xsl:if test="following-sibling::database_type">
							<xsl:text>;</xsl:text>
						</xsl:if>					
					</xsl:for-each>
				</xsl:attribute>
			  </input>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Coverage"
					data-content="The (approximate) publication dates covered by this databases" >
					Coverage
				</a>
			</label>
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
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Trial end date"
					data-content="This is a date field.  You can use it to set a date when Xerxes will automatically suppress the database from display.  This is particularly useful for trial databases (hence the name of the field), so you don't have to remember to go back and remove the database after the trial has ended.  Enter date as mm/dd/yyyy, or click in the field and use the calendar to pick a date." >
					Trial until
				</a>
			</label>
			<div class="controls">
			  <input type="text" name="date_trial_expiry" class="datepicker" maxlength="10" size="10" 
			  	placeholder="date when trial is over" value="{substring(string(date_trial_expiry/date),1,10)}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="New until"
					data-content="This is a date field.  It tells Xerxes to highlight a database as new until the date you set.  This will ultimately drive a 'new databases' feature on the home page.  Enter date as mm/dd/yyyy, or click in the field and use the calendar to pick a date." >
					New until
				</a>
			</label>
			<div class="controls">
			  <input type="text" name="date_new_expiry" class="datepicker" maxlength="10" size="10" 
			  	placeholder="date no longer new"  value="{substring(string(date_new_expiry/date),1,10)}" />					  
			</div>
		  </div>	
		  <div class="control-group">
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Keywords"
					data-content="Enter any set of key terms you want to add to help users find this database when searching or browsing the databases pages.  Separate multiple keywords with a comma or by hitting enter.  As you add keywords, they are converted into separate blocks in the input field, which you can remove by clicking the 'x' icon next to each term, or by simply hitting backspace in your keyboard." >
					Keywords
				</a>
			</label>
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
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Creator"
					data-content="The organization responsible for creating and maintaining the database (e.g., American Psychological Association), if different from the publisher." >
					Creator
				</a>			
			</label>
			<div class="controls">
			  <input type="text" name="creator" class="input-long" value="{creator}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Publisher"
					data-content="The organization responsible for publishing the database (e.g. Ebsco, Proquest)." >
					Publisher
				</a>
			</label>
			<div class="controls">
			  <input type="text" name="publisher" class="input-long" value="{publisher}" />
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Search hints</label>
			<div class="controls">
			  <textarea name="search_hints" class="input-long" rows="8">
			  	<xsl:value-of select="search_hints" />
			  </textarea>
			</div>
		  </div>
		  <div class="control-group">
			<label class="control-label">Link guide</label>
			<div class="controls">
			  <input type="text" name="link_guide" class="input-long" placeholder="Link to a guide or instructions page" value="{link_guide}" />
			</div>
		  </div>
		  
		  <!--
		  <div class="control-group">
			<label class="control-label">
				<a class="tool-tip" data-toggle="popover" title="" data-placement="left" data-original-title="Link copyright"
					data-content="Use this field to link to either a Responsible Use of Electronic Resources web page or to an ERM record other page with Terms of Use for this database.">
					Link copyright
				</a>
			</label>
			<div class="controls">
			  <input type="text" name="link_copyright" class="input-long" placeholder="" value="{link_copyright}" />
			</div>
		  </div>
		  -->
		  
		  <div style="padding: 20px; padding-left: 200px">
			  <button class="btn btn-large btn-primary" type="submit">Update</button>
		  </div>
		  
		</form>
	</div>
		
</xsl:template>

</xsl:stylesheet>
