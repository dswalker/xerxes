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

<xsl:template name="module_javascript">
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"  type="text/javascript"></script>
	<script src="{$base_include}/javascript/readmore.min.js"  type="text/javascript"></script>
	<script src="{$base_include}/javascript/courses.js"  type="text/javascript"></script>
	<script type="text/javascript">
		$('.abstract').readmore({
		  speed: 75,
		  maxHeight: 100,
		  heightMargin: 20
		});
	</script>
</xsl:template>

<xsl:template name="main">
		
	<xsl:if test="//lti/instructor = '1'">
	
		<div class="reading-list-header">
		
			<div style="float:right">
			
				<xsl:choose>
					<xsl:when test="//request/session/reading_minimize = 'true'">
						<a href="{//request/controller}/minimize?minimize=false" class="btn">
							Show abstracts
						</a>
					</xsl:when>
					<xsl:otherwise>
						<a href="{//request/controller}/minimize?minimize=true" class="btn">
							Hide abstracts
						</a>
					</xsl:otherwise>
				</xsl:choose>
			

			</div>
			
			<a href="{course_nav/url_search}" class="btn ">
				<i class="icon-search"></i><xsl:text> </xsl:text>Search for new records
			</a>
			<xsl:text> </xsl:text>
			<a href="{course_nav/url_previously_saved}" class="btn">
				<i class="icon-folder-open-alt"></i><xsl:text> </xsl:text>Add previously saved records
			</a>
			
		</div>
		
	</xsl:if>

	<xsl:if test="results/records/record/xerxes_record">
	
		<div id="reading-list-content">
		
		<ul data-source="{//request/course_id}">
		
		<xsl:for-each select="results/records/record/xerxes_record">
		
			<xsl:variable name="delete_position" select="position() - 1" />
		
			<a name="record-{../id}"></a>
			<a name="position-{position()}"></a>
		
			<li id="reader_list_{../id}" class="reading-list-item">
			
				<xsl:if test="//lti/instructor = '1'">
				
					<div class="reading-list-item-action">
									
						<img src="{$base_url}/images/famfamfam/arrow_out.png" alt="" />
						
						<div style="position: absolute; top: 3px; right: 10px">

							<a id="facet-more-link-{group_id}" href="#reading-modal-{../id}" role="button" class="btn btn-small facet-more-launch" data-toggle="modal"> 
								<i class="icon-pencil"></i> Edit
							</a>
							<xsl:text> </xsl:text>
							<a href="{../url_save_delete}&amp;position={$delete_position}" class="btn btn-small">
								<i class="icon-trash"></i> Remove
							</a>
						</div>
					</div>
				
				</xsl:if>	

				<div class="title">
					<strong>
						<a href="{../url_open}" target="_blank"><xsl:value-of select="title_normalized" /></a>
					</strong>
					<xsl:text> </xsl:text>
				</div>
				<div>
					<xsl:value-of select="primary_author" />
				</div>
				<div>
					<xsl:value-of select="journal" />
				</div>
				
				<xsl:if test="not(//request/session/reading_minimize = 'true')">
				
					<div class="abstract">
						<xsl:value-of select="abstract" />
					</div>
					
				</xsl:if>
			</li>
			
		</xsl:for-each>
		
		</ul>
		
		</div>
		
		<xsl:for-each select="results/records/record/xerxes_record">
			
			<div id="reading-modal-{../id}" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="reading-modal-{../id}-label" aria-hidden="true">
			
				<form action="{//request/controller}/edit">
					<input type="hidden" name="record_id" value="{../id}" />
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<h3 id="reading-modal-{../id}-label">Edit</h3>
					</div>
					<div class="modal-body">
	
						<div class="reading-group">
							<label class="reading-label" for="reading-input-title">Title</label>
							<div class="reading-input">
								<textarea name="title" id="reading-input-title">
									<xsl:value-of select="title_normalized" />
								</textarea>
							</div>
						</div>
						
						<div class="reading-group">
							<label class="reading-label" for="reading-input-author">Author(s)</label>
							<div class="reading-input">
								<textarea rows="2" name="author" id="reading-input-author">
									<xsl:value-of select="primary_author" />
								</textarea>
							</div>
						</div>
	
						<div class="reading-group">
							<label class="reading-label" for="reading-input-journal">Publication</label>
							<div class="reading-input">
								<textarea rows="3" name="publication" id="reading-input-journal">
									<xsl:value-of select="journal" />
								</textarea>
							</div>
						</div>
						
						<div class="reading-group">
							<label class="control-label" for="reading-input-abstract">Description</label>
							<div class="reading-input">
								<textarea rows="10" name="abstract" id="reading-input-abstract">
									<xsl:value-of select="abstract"	/>
								</textarea>
							</div>
						</div>

						<div>
							<input type="checkbox" name="reset" id="reading-input-restore" value="true" />
							<xsl:text> </xsl:text>
							<label class="control-label" for="reading-input-restore">Restore original information</label>
						</div>
	
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary"><xsl:value-of select="$text_facets_submit" /></button>
						<button class="btn" data-dismiss="modal" aria-hidden="true"><xsl:value-of select="$text_facets_close" /></button>
					</div>
				</form>	
			
			</div>
		
		</xsl:for-each>

	</xsl:if>

</xsl:template>

<xsl:template name="advanced_search_option" />

</xsl:stylesheet>
