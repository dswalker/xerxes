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

 Master includes
 author: David Walker <dwalker@calstate.edu>
 
 -->

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
	
	<!-- 
		TEXT LABLES
		These are variables that define the labels of the system
	-->
	
	<xsl:include href="labels/eng.xsl" />

	<!-- 
		GLOBAL VARIABLES
		Configuration values used throughout the application
	-->
	
	<xsl:variable name="base_url" select="//base_url" />
	
	<xsl:variable name="base_include">
		<xsl:choose>
			<xsl:when test="//config/shared_assets">
				<xsl:value-of select="//config/shared_assets" />	
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$base_url" />			
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<xsl:variable name="asset_version" select="//config/asset_version" />

	<xsl:variable name="link_target" select="//config/link_target" />

	<xsl:variable name="is_mobile">
		<xsl:choose>
			<xsl:when test="//request/session/is_mobile = '1'">1</xsl:when>
			<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<xsl:variable name="is_ada">
		<xsl:choose>
			<xsl:when test="request/session/ada">1</xsl:when>
			<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="temporarySession">
		<xsl:choose>
			<xsl:when test="//request/session/role = 'named'">
				<xsl:text>false</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>true</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<!-- extra content to include in the HTML 'head' section -->
	
	<xsl:variable name="text_extra_meta_tags" />
	<xsl:variable name="text_extra_html_head_content" />
	
	<!-- 
		LANGUAGE VARIABLES
		@todo bring this back somehow
	-->
	
	<xsl:variable name="default_language">
		<xsl:value-of select="//config/languages/language[position()=1]/@code" />
	</xsl:variable>
	<xsl:variable name="language">
		<xsl:choose>
			<xsl:when test="//request/lang and //request/lang != ''"> <!-- @todo: allow only languages defined in //config/languages/language[@code] -->
				<xsl:value-of select="//request/lang" />
			</xsl:when>
			<xsl:when test="$default_language"> <!-- if it's defined, use it -->
				<xsl:value-of select="$default_language" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>eng</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<!--
		For languages other than English, will add "-code" suffix, where code is language code.
		This can be used to define language-dependent CSS classes, e.g. for buttons.
		If you wish to turn this off, just define <xsl:variable name="language_suffix" /> in your local includes.xsl
		
		XSLT example: <a href='example.html' class='myclass{$language_suffix}'></a>
		CSS example:
			.myclass {
				background-image:url('english-label.png')
			}
			.myclass-ger {
				background-image:url('german-label.png')
			}
	-->
	<xsl:variable name="language_suffix">
		<xsl:choose>
			<xsl:when test="$language != 'eng'">
				<xsl:text>-</xsl:text><xsl:value-of select="$language" />
			</xsl:when>
			<xsl:otherwise>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	<xsl:variable name="language_position">
		<xsl:value-of select="//config/db_description_multilingual/language[@code=$language]/@order" />
	</xsl:variable>
	
	<xsl:variable name="locale"><xsl:value-of select="//config/languages/language[@code=$language]/@locale" /></xsl:variable>
	<xsl:variable name="rfc1766">
		<xsl:choose>
			<xsl:when test="$locale = '' or $locale = 'C'">
				<xsl:text>en</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="substring-before($locale, '_')" />
				<!--
				Code to generate RFC 1766 subcode (e.g. en-US, pt-BR, ...), if it ever becomes necessary in Xerxes
				
				<xsl:variable name="rfc1766temp"><xsl:value-of select="substring-before($locale, '.')" /></xsl:variable>
				<xsl:variable name="rfc1766sub"><xsl:value-of select="substring-after($rfc1766temp, '_')" /></xsl:variable>
				<xsl:if test="$rfc1766sub">
					<xsl:text>-</xsl:text>
					<xsl:value-of select="$rfc1766sub" />
				</xsl:if>
				-->
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	
	
	<!-- 	
		TEMPLATE: SURROUND
		This is the master template that defines the overall design for the application; place
		here the header, footer and other design elements which all pages should contain.
	-->
	
	<xsl:template name="surround">
		<xsl:param name="surround_template"><xsl:value-of select="//config/template" /></xsl:param>
		<xsl:param name="sidebar" />
		
		<!-- doctype: html 5 -->
		
		<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;</xsl:text>
	
		<html lang="en">
	
		<xsl:call-template name="surround_head" />

		<body class="controller-{//request/controller} action-{//request/action}">
		
		<xsl:call-template name="surround_tip_top" />
		
		<xsl:if test="$is_mobile = 0">
			
			<div class="ada">
				<xsl:if test="$is_ada = 0">
					<a href="{navbar/accessible_link}">
						<xsl:copy-of select="$text_ada_version" /> 
					</a>
				</xsl:if>
			</div>
			
			<xsl:call-template name="surround_top" />
			
		</xsl:if>
	
		<xsl:call-template name="surround_main">
			<xsl:with-param name="surround_template"><xsl:value-of select="$surround_template" /></xsl:with-param>
			<xsl:with-param name="sidebar"><xsl:value-of select="$sidebar" /></xsl:with-param>
		</xsl:call-template>
		
		<xsl:if test="$is_mobile = 0">
			<xsl:call-template name="surround_bottom" />
		</xsl:if>

		<!-- javascript: only when not ada or mobile -->
		<xsl:if test="$is_ada = 0">
			<xsl:call-template name="javascript_include" />
		</xsl:if>
		
		</body>
		</html>
		
	</xsl:template>

	<!-- 
		TEMPLATE: surround main
	-->
	
	<xsl:template name="surround_main">
		<xsl:param name="surround_template"><xsl:value-of select="//config/template" /></xsl:param>
		<xsl:param name="sidebar" />
	
		<div data-role="page" id="{//config/document}" class="{$surround_template}">
		
			<div class="ada">
				<xsl:if test="results">
					<a href="{//request/server/request_uri}#skip-to-results">
						<xsl:value-of select="$text_ada_skip_limits" />
					</a>
				</xsl:if>
				<a href="{//request/server/request_uri}#skip-nav">
					<xsl:value-of select="$text_ada_skip_nav" />
				</a>
			</div>
	
			<!-- The main content is split into subtemplates to make customiztion of parts easier -->
			
			<xsl:call-template name="surround_hd" />
			
			<a id="skip-nav" />
	
			<xsl:call-template name="surround_bd">
				<xsl:with-param name="sidebar"><xsl:value-of select="$sidebar" /></xsl:with-param>
			</xsl:call-template>
	
			<xsl:call-template name="surround_ft" />
	
		</div>	
	
	</xsl:template>

	<!-- 
		TEMPLATE: surround head
		page html <head>
	-->
	
	<xsl:template name="surround_head">
		<head>
		<title><xsl:call-template name="title" /> | <xsl:value-of select="//config/application_name" /></title>
		<xsl:call-template name="surround_meta" />
		
		<!-- jquery mobile adds its own base tag, so we don't here; weird, I know -->
		
		<xsl:if test="$is_mobile = 0">
			<base href="{$base_url}/" />
		</xsl:if>
		
		<!-- css -->
		<xsl:call-template name="css_include" />
		
		<!-- header content added by module -->
		
		<xsl:choose>
			<xsl:when test="$is_mobile = 1">
				<xsl:call-template name="module_header_mobile" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="module_header" />
			</xsl:otherwise>
		</xsl:choose>
		
		<!-- and by local implementation -->
		<xsl:copy-of select="$text_extra_html_head_content" />
		
		<!-- good junk -->
		<xsl:call-template name="surround_google_analytics" />
		</head>
	</xsl:template>

	<!-- 
		TEMPLATE: surround main
	-->
	
	<xsl:template name="surround_meta">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<xsl:copy-of select="$text_extra_meta_tags" />
	</xsl:template>
	
	<!-- 
		TEMPLATE: surround hd
		page header
	-->
	<xsl:template name="surround_hd">
	
		<xsl:if test="not(no_header)">
	
			<div id="hd" data-role="header">
				<xsl:choose>
					<xsl:when test="$is_mobile = '1'">
						<xsl:call-template name="mobile_header" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="header_div" />
					</xsl:otherwise>
				</xsl:choose>
			</div>
			
		</xsl:if>
		
	</xsl:template>
	
	<!-- 
		TEMPLATE: surround bd
		page body - main content
	-->
	<xsl:template name="surround_bd">
		<xsl:param name="sidebar" />
	
			<div id="bd" data-role="content">
			
				<xsl:call-template name="surround_bd_top" />
			
				<div class="row-fluid">
				
					<div>
						<xsl:if test="$sidebar != 'none' and $is_mobile != '1'">
							<xsl:attribute name="class">span8</xsl:attribute>
						</xsl:if>
						
						<xsl:if test="request/flash_messages/*">
							<xsl:call-template name="message_display"/>
						</xsl:if>
						
						<xsl:call-template name="main" />
					</div>

				
					<xsl:if test="$sidebar != 'none' and $is_mobile = '0'">
						<xsl:call-template name="sidebar_wrapper" />
					</xsl:if>
					
				</div>
	
			</div>
	</xsl:template>
	
	<!-- 
		TEMPLATE: surround bd top
		breadcrumbs and account links
	-->
	
	<xsl:template name="surround_bd_top">
		
		<div id="bd-top">
		
			<div class="row-fluid">
				<div class="span6">	
	
					<!-- breadcrumbs -->
	
					<div class="trail">
						<xsl:call-template name="breadcrumb" />
					</div>
	
				</div>
				
				<div class="span6">
				
					<!-- my account -->
				
					<div class="account">
						<xsl:call-template name="account_options" />
					</div>
					
				</div>
	
			</div>
			
		</div>
	
	</xsl:template>
	
	
	<!-- 
		TEMPLATE: surround ft
		page footer
	-->
	
	<xsl:template name="surround_ft">
			<div id="ft">
				<xsl:choose>
					<xsl:when test="$is_mobile = '1'">
						<xsl:call-template name="mobile_footer" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="footer_div" />
					</xsl:otherwise>
				</xsl:choose>
			</div>
	</xsl:template>
	
	<!-- 
		TEMPLATE: surround bottom
		page footer
	-->
	
	<xsl:template name="surround_bottom" />
	

	
	<!-- 
		TEMPLATE: MESSAGE_DISPLAY
		A generic way to display a message to the user in any page, usually
		used for non-ajax version of completion status messages.
	-->
	
	<xsl:template name="message_display">
		
		<xsl:for-each select="request/flash_messages/*">
		
			<div class="alert alert-{@original_key}">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<xsl:value-of select="text()" />
			</div>
			
		</xsl:for-each>

	</xsl:template>
	
	<!--
		TEMPLATE: SIDEBAR WRAPPER
		This defines the overarching sidebar element.  Pages normally will use sidebar template, which 
		defines the content, but if a page can call this template to change the _structure_ of the 
		sidebar as well
	-->
	
	<xsl:template name="sidebar_wrapper">
		<div class="span4">
			<div class="sidebar">
				<xsl:call-template name="sidebar_top" />
				<xsl:call-template name="sidebar" />
				<xsl:call-template name="sidebar_additional" />
			</div>
		</div>
	</xsl:template>
	
	
	<!-- 
		TEMPLATE: BREADCRUMB START
		The start of the breadcrumb trail, which can include links to the library or campus
		website.  Also here we break out the Xerxes 'home' link in case some section of the
		application (my saved records, for example) that might not want to be conceptually
		separate
	-->
	
	<xsl:template name="breadcrumb_start" />
	
	<!-- 
		TEMPLATE: TITLE
		the title that appears in the browser window.  This is assumed to be the 
		page name, unless the page overrides it
	-->
	
	<xsl:template name="title">
		<xsl:call-template name="page_name" />
	</xsl:template>
	
	<!-- 
		TEMPLATE: MOBILE HEADER
		A special (slimmed-down) header to use when displaying for a mobile device
	-->
	
	<xsl:template name="mobile_header" >
	
		<a href="{$base_url}/" data-icon="home">Home</a>
		<a href="{//navbar/full_display_link}" class="ui-btn-right" data-ajax="false">View full site</a>
	
		<h1><xsl:value-of select="$text_app_name" /></h1>
	
	</xsl:template>
	
	<!-- 
		TEMPLATE: MOBILE FOOTER
		A special (slimmed-down) footer to use when displaying for a mobile device
	-->
	
	<xsl:template name="mobile_footer" />
	
	<!--
		TEMPLATE: MY ACCOUNT SIDEBAR
		sidebar account block
	-->
	
	<xsl:template name="account_sidebar">
		<div id="account" class="box">
			<h2><xsl:copy-of select="$text_header_myaccount" /></h2>
			<xsl:call-template name="account_options" />			
		</div>
	</xsl:template>

	<!--
		TEMPLATE: ACCOUNT OPTIONS
		links to login/out, my saved records, and other personalization features
	-->	
	
	<xsl:template name="account_options">
	
		<ul>
			<li id="login-option">

				<xsl:choose>
					<xsl:when test="//request/session/role and //request/session/role = 'named'">
										
						<a id="logout" class="btn btn-small">
						<xsl:attribute name="href"><xsl:value-of select="//navbar/logout_link" /></xsl:attribute>
							<xsl:call-template name="img_logout" />
							<xsl:text> </xsl:text>
							<xsl:copy-of select="$text_header_logout" />
						</a>
						
					</xsl:when>
					<xsl:otherwise>
					
						<a id="login" class="btn btn-small">
						<xsl:attribute name="href"><xsl:value-of select="//navbar/login_link" /></xsl:attribute>
							<xsl:call-template name="img_login" />
							<xsl:text> </xsl:text>	
							<xsl:copy-of select="$text_header_login" />
						</a>
						
					</xsl:otherwise>
				</xsl:choose>
	
			</li>
			<li>
				<xsl:call-template name="my_saved_records_group" />
			</li>
			
			<xsl:call-template name="module_nav" />
			
		</ul>	
	
	</xsl:template>
	
	<xsl:template name="my_saved_records_group">

		<xsl:choose>
			<xsl:when test="//config/show_my_saved_databases">
				<div class="btn-group">
					<xsl:call-template name="my_saved_records" />
					<button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li id="my-saved-databases">
							<!--
							<xsl:call-template name="img_save_record">
								<xsl:with-param name="id">my-databases</xsl:with-param>
							</xsl:call-template>
							<xsl:text> </xsl:text>
							-->
							<a>
							<xsl:attribute name="href"><xsl:value-of select="//navbar/my_databases_link" /></xsl:attribute>
								<xsl:copy-of select="$text_header_my_collections" />
							</a>
						</li>
					</ul>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="my_saved_records" />
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
	
	<xsl:template name="my_saved_records">
	
		<a class="btn btn-small">
		<xsl:attribute name="href"><xsl:value-of select="//navbar/my_account_link" /></xsl:attribute>
			<xsl:call-template name="img_save_record">
				<xsl:with-param name="id">folder</xsl:with-param>
			</xsl:call-template>
			<xsl:text> </xsl:text>
			<xsl:copy-of select="$text_header_savedrecords" />
		</a>
	
	</xsl:template>

	<!--
		TEMPLATE: JAVASCRIPT
	-->
	
	<xsl:template name="javascript_include">
			
		<xsl:call-template name="jslabels" />
	
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" type="text/javascript" ></script>	
		<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js" type="text/javascript" ></script>
		<script src="{$base_include}/javascript/flot/jquery.flot.min.js" type="text/javascript" ></script>
		<script src="{$base_include}/javascript/flot/jquery.flot.time.min.js?version={$asset_version}"  type="text/javascript"></script>
		
		<xsl:comment><![CDATA[[if lte IE 8]>]]>
		
			&lt;script language="javascript" type="text/javascript" src="<xsl:value-of select="$base_include"/>/javascript/flot/excanvas.min.js"&gt;&lt;/script&gt;
			
		<![CDATA[<![endif]]]></xsl:comment>
		
		<script src="{$base_include}/javascript/bootstrap-select.min.js?version={$asset_version}"  type="text/javascript"></script>
		
		<xsl:call-template name="module_javascript" />
		
		<script src="{$base_include}/javascript/results.js?version={$asset_version}"  type="text/javascript"></script>

	</xsl:template>
		
	<!-- 	
		TEMPLATE: JSLABELS
		maps text from the i18n label files into a Javascript array, so we can use them in external js files
	-->
	
	<xsl:template name="jslabels">
	
		<script  type="text/javascript" src="{$base_url}/asset/labels?version={$asset_version}"></script> 
	
	</xsl:template>

	<!-- 
		TEMPLATE: surround google analytics
		Google analytics script
	-->
	<xsl:template name="surround_google_analytics">
		<xsl:if test="//config/google_analytics">
			<script type="text/javascript">
				var _gaq = _gaq || [];
				_gaq.push(['_setAccount', '<xsl:value-of select="//config/google_analytics"/>']);
				_gaq.push(['_trackPageview']);
	
				(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				})();
			</script>
		</xsl:if>
		
		<xsl:call-template name="surround_google_universal_analytics" />
		
	</xsl:template>
	
	<xsl:template name="surround_google_universal_analytics">
	
		<xsl:if test="//config/google_univeral_analytics">
			<script>
			  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
			
			  ga('create', '<xsl:value-of select="//config/google_univeral_analytics"/>', '<xsl:value-of select="//config/google_univeral_analytics_domain"/>');
			  ga('send', 'pageview');
			
			</script>
		</xsl:if>
		
	</xsl:template>

	
	<!-- 
		TEMPLATE: CSS INCLUDE 
	-->
	
	<xsl:template name="css_include">
				
		<xsl:choose>
			<xsl:when test="$is_mobile = '1'">
				
				<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
				<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
				<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
				
				<link href="{$base_include}/css/xerxes-mobile.css?version={$asset_version}" rel="stylesheet" type="text/css" />	
				<link href="{$base_url}/css/local-mobile.css?version={$asset_version}" rel="stylesheet" type="text/css" />	

			</xsl:when>
			<xsl:otherwise>
				
				<link rel="stylesheet" type="text/css" href="{$base_include}/css/bootstrap-select.min.css" />
				<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css" rel="stylesheet" />
				<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet" />
				<link href="{$base_include}/css/xerxes.css?version={$asset_version}" rel="stylesheet" type="text/css" />
				<link href="css/local.css?version={$asset_version}" rel="stylesheet" type="text/css" />
				
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:template>
	
	
	<!-- 	
		TEMPLATES THAT SHOULD BE OVERRIDEN IN PAGES OR LOCAL INCLUDES.XSL
		Defined here in case they are not, so as not to stop the proceedings
	-->
	
	<xsl:template name="surround_tip_top" />
	<xsl:template name="surround_top" />
	<xsl:template name="header_div" />
	<xsl:template name="footer_div" />
	<xsl:template name="page_name" />
	<xsl:template name="breadcrumb" />
	<xsl:template name="sidebar_top" />
	<xsl:template name="sidebar" />
	<xsl:template name="sidebar_additional" />
	<xsl:template name="module_header" />
	<xsl:template name="module_header_mobile" />
	<xsl:template name="module_javascript" />
	<xsl:template name="module_nav" />
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
<!--
	#############################
	#                           #
	#      IMAGE TEMPLATES      #
	#                           #
	#############################
-->

<xsl:variable name="app_mini_icon_url">images/famfamfam/page_find.png</xsl:variable>
<xsl:variable name="image_sfx">
	<xsl:value-of select="$base_url" /><xsl:text>/images/sfx.gif</xsl:text>
</xsl:variable>
<xsl:variable name="img_src_original_record">images/famfamfam/link.png</xsl:variable>
<xsl:variable name="img_src_holdings">images/book.gif</xsl:variable>
<xsl:variable name="img_src_chain">images/famfamfam/link.png</xsl:variable>

<xsl:template name="img_databases_az_hint_info">
	<img alt="{$text_databases_az_hint_info}" title="{$text_databases_az_hint_info}" src="images/info.gif" class="icon-info mini-icon">
	</img>
</xsl:template>

<xsl:template name="img_databases_az_hint_searchable">
	<img alt="{$text_databases_az_hint_searchable}" title="{$text_databases_az_hint_searchable}" 
		class="icon-searchable mini-icon" src="images/famfamfam/magnifier.png"/>
</xsl:template>

<xsl:template name="img_refereed">
	<img src="{$base_url}/images/refereed_hat.gif" width="20" height="14" alt="" />
</xsl:template>

<xsl:template name="img_save_record">
	<xsl:param name="id" />
	<xsl:param name="class" />
	<xsl:param name="alt" />
	<xsl:param name="test" />
	<img id="{$id}" alt="{$alt}"  class="{$class}">
		<xsl:attribute name="src">
			<xsl:value-of select="$base_url" />
			<xsl:text>/</xsl:text>
			<xsl:choose> 
				<xsl:when test="$test">images/folder_on.gif</xsl:when>
				<xsl:otherwise>images/folder.gif</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
	</img>
</xsl:template>

<xsl:template name="img_delete">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/delete.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_facet_remove">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/facet-remove.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_holdings">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/book.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_login">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/famfamfam/user.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_logout">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/famfamfam/user_delete.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_results_hint_remove_limit">
	<xsl:param name="alt" select="$text_results_hint_remove_limit" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/delete.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_az_info">
	<xsl:param name="alt" select="$text_results_hint_remove_limit" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/info.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_info_about">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/info.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_embed_info">
	<xsl:param name="alt">info</xsl:param>
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/info.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_book_not_available">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/book-out.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_format_pdf">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/pdf.gif" width="16" height="16"  alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_format_html">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/html.gif" width="16" height="16"  alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_format_unknown">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/html.gif" width="16" height="16"  alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_databases_subject_hint_restricted">
	<xsl:param name="alt" select="$text_databases_subject_hint_restricted" />
	<xsl:param name="title" select="$text_databases_subject_hint_restricted" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/lock.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_grey_checkbox">
	<xsl:param name="alt" select="$text_databases_subject_hint_restricted" />
	<xsl:param name="title" select="$text_databases_subject_hint_native_only" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/link-out.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_back">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/back.gif" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_ill">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/ill.gif"  alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_phone">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/phone.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_hold">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/famfamfam/accept.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_search">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/famfamfam/magnifier.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

<xsl:template name="img_add">
	<xsl:param name="alt" />
	<xsl:param name="title" />
	<xsl:param name="class" />
	<img src="{$base_url}/images/famfamfam/add.png" alt="{$alt}" title="{$title}" class="{$class}" />
</xsl:template>

</xsl:stylesheet>
