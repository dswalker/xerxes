<?xml version="1.0" encoding="utf-8"?>

<!--

 author: David Walker
 copyright: 2009 California State University
 version: $Id: eng.xsl 1898 2011-04-15 11:26:15Z helix84@centrum.sk $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<!-- 
	TEXT LABELS 
	These are global variables that provide the text for the system.
	
	Variable names should follow the pattern of: text_{location}_{unique-name}
	Keep them in alphabetical order!!
-->
	
	<xsl:variable name="text_ada_version">For best results, click this link for accessible version</xsl:variable>
	<xsl:variable name="text_ada_table_for_display">for display only</xsl:variable>
	
	<xsl:variable name="text_app_name"	select="//config/application_name" />
	
	<xsl:variable name="text_authentication_error_not_authorized">Sorry, our records show you are not authorized to use this service</xsl:variable>
	<xsl:variable name="text_authentication_login_explain"></xsl:variable>
	<xsl:variable name="text_authentication_login_failed">Sorry, your username or password was incorrect.</xsl:variable>
	<xsl:variable name="text_authentication_login_pagename">Login</xsl:variable>
	<xsl:variable name="text_authentication_login_password">password:</xsl:variable>
	<xsl:variable name="text_authentication_login_username">username:</xsl:variable>
		
	<xsl:variable name="text_authentication_logout_confirm">Are you sure you want to end your session?</xsl:variable>
	<xsl:variable name="text_authentication_logout_pagename">Logout</xsl:variable>
	
	<xsl:variable name="text_breadcrumb_separator"> &gt; </xsl:variable>
	<xsl:variable name="text_breadcrumb_seperator" select="$text_breadcrumb_separator" />
	
	<xsl:variable name="text_citation_apa">APA</xsl:variable>
	<xsl:variable name="text_citation_mla">MLA</xsl:variable>
	<xsl:variable name="text_citation_turabian">Turabian / Chicago</xsl:variable>
	
	<xsl:variable name="text_collections_add_database">Add databases</xsl:variable>
	<xsl:variable name="text_collections_add_section">Add a new section:</xsl:variable>
	<xsl:variable name="text_collections_blank_name">Blank name, not changed</xsl:variable>
	<xsl:variable name="text_collections_cancelled">Cancelled</xsl:variable>
	<xsl:variable name="text_collections_change_database_order">Change database order</xsl:variable>
	<xsl:variable name="text_collections_change_name">Change collection name</xsl:variable>
	<xsl:variable name="text_collections_change_section_name">Change section name</xsl:variable>
	<xsl:variable name="text_collections_change_section_order">Change section order</xsl:variable>
	<xsl:variable name="text_collections_created_by">Created by <xsl:value-of select="/*/category/@owned_by_user" /></xsl:variable>
	<xsl:variable name="text_collections_database_already_saved">Database was already saved to %s in %s</xsl:variable>
	<xsl:variable name="text_collections_database_order_changed">Database order changed</xsl:variable>
	<xsl:variable name="text_collections_database_saved">Saved database in %s</xsl:variable>
	<xsl:variable name="text_collections_delete_collection">Delete collection</xsl:variable>
	<xsl:variable name="text_collections_delete_collection_confirm">Are you sure you want to delete this collection?</xsl:variable>
	<xsl:variable name="text_collections_delete_section">Delete section</xsl:variable>
	<xsl:variable name="text_collections_delete_section_confirm">Are you sure you want to delete this section?</xsl:variable>
	<xsl:variable name="text_collections_deleted_category">Deleted %s</xsl:variable>
	<xsl:variable name="text_collections_deleted_subcategory">Deleted %s</xsl:variable>
	<xsl:variable name="text_collections_done_editing">I'm done editing!</xsl:variable>
	<xsl:variable name="text_collections_edit">Add databases and Edit</xsl:variable>
	
	<xsl:variable name="text_collections_error_embed_not_published">Your collection must be published in order to use the 'embed' feature</xsl:variable>	
	<xsl:variable name="text_collections_error_no_such_category">Selected category not found.</xsl:variable>
	<xsl:variable name="text_collections_error_no_such_section">Selected section not found.</xsl:variable>	
	<xsl:variable name="text_collections_error_not_logged_in">You must be logged in to use this function.</xsl:variable>
	<xsl:variable name="text_collections_error_personal_collection_not_found">Personal collection not found.</xsl:variable>
	<xsl:variable name="text_collections_error_private_collection_save">You must be logged in as %s to save to a personal database collection owned by that user.</xsl:variable>
	<xsl:variable name="text_collections_error_private_collection">This is a private database collection only accessible to the user who created it. Please log in if you are that user.</xsl:variable>

	<xsl:variable name="text_collections_list_databases">List databases matching: </xsl:variable>
	<xsl:variable name="text_collections_made_private">Collection made private.</xsl:variable>
	<xsl:variable name="text_collections_made_published">Collection published.</xsl:variable>
	<xsl:variable name="text_collections_no_matches">No databases found matching</xsl:variable>	
	<xsl:variable name="text_collections_name_changed">Collection name changed.</xsl:variable>
	<xsl:variable name="text_collections_private">Private</xsl:variable>
	<xsl:variable name="text_collections_public">Public</xsl:variable>
	<xsl:variable name="text_collections_public_url">Public URL:</xsl:variable>
	<xsl:variable name="text_collections_publish">Make collection:</xsl:variable>
	<xsl:variable name="text_collections_remove_searchbox">I'm done adding databases!</xsl:variable>
	<xsl:variable name="text_collections_removed_database">Removed Database</xsl:variable>
	<xsl:variable name="text_collections_renamed">Renamed</xsl:variable>
	<xsl:variable name="text_collections_reorder_db_title">Reorder Databases</xsl:variable>
	<xsl:variable name="text_collections_reorder_subcat_title">Reorder Sections</xsl:variable>
	<xsl:variable name="text_collections_section_new">New section created</xsl:variable>
	<xsl:variable name="text_collections_section_order_changed">Section order changed</xsl:variable>
	
	<xsl:variable name="text_database_availability">Availability:</xsl:variable>
	<xsl:variable name="text_database_available_registered">Only available to registered users.</xsl:variable>
	<xsl:variable name="text_database_available_everyone">Available to everyone.</xsl:variable>
	<xsl:variable name="text_database_coverage">Coverage:</xsl:variable>
	<xsl:variable name="text_database_creator">Creator</xsl:variable>
	<xsl:variable name="text_database_guide">Guide:</xsl:variable>
	<xsl:variable name="text_database_guide_help">Help in using this database</xsl:variable>
	<xsl:variable name="text_database_go_to_database">Go to this database!</xsl:variable>
	<xsl:variable name="text_database_link">Link:</xsl:variable>
	<xsl:variable name="text_database_publisher">Publisher:</xsl:variable>
	<xsl:variable name="text_database_save_database">Save database</xsl:variable>
	<xsl:variable name="text_database_search_hints">Search Hints:</xsl:variable>
	
	<xsl:variable name="text_databases_access_available">Only available to </xsl:variable>
	<xsl:variable name="text_databases_access_group_and">and</xsl:variable>
	<xsl:variable name="text_databases_access_users">users</xsl:variable>
	
	<xsl:variable name="text_databases_az_backtop">Back to top</xsl:variable>
	<xsl:variable name="text_databases_az_breadcrumb_all">All databases</xsl:variable>
	<xsl:variable name="text_databases_az_breadcrumb_matching">Databases matching</xsl:variable>
	<xsl:variable name="text_databases_az_databases">databases</xsl:variable>
	<xsl:variable name="text_databases_az_hint_info">more information</xsl:variable>
	<xsl:variable name="text_databases_az_hint_searchable">searchable by <xsl:value-of select="$text_app_name" /></xsl:variable>
	<xsl:variable name="text_databases_az_letter_separator"> | </xsl:variable>
	<xsl:variable name="text_databases_az_pagename">Databases A-Z</xsl:variable>
	<xsl:variable name="text_databases_az_search">List databases matching: </xsl:variable>
	
	<xsl:variable name="text_databases_category_pagename">Home</xsl:variable>
	<xsl:variable name="text_databases_category_quick_desc">
		<xsl:text>Search </xsl:text>
		<xsl:call-template name="text_number_to_words">
			<xsl:with-param name="number" select="count(//category[1]/subcategory[1]/database[searchable = 1])" /> 
		</xsl:call-template>
		<xsl:text> of our most popular databases</xsl:text>
	</xsl:variable>
	<xsl:variable name="text_databases_category_subject">Search by Subject</xsl:variable>
	<xsl:variable name="text_databases_category_subject_desc">Search databases specific to your area of study.</xsl:variable>

	<xsl:variable name="text_databases_subject_hint_direct_search">Go directly to </xsl:variable>
	<xsl:variable name="text_databases_subject_hint_more_info_about">More information about </xsl:variable>
	<xsl:variable name="text_databases_subject_hint_native_only">Click database title to search individually</xsl:variable>
	<xsl:variable name="text_databases_subject_hint_restricted">Restricted, click database title to search individually</xsl:variable>
	
	<xsl:variable name="text_databases_subject_librarian_address">Office:</xsl:variable>
	<xsl:variable name="text_databases_subject_librarian_email">Email:</xsl:variable>
	<xsl:variable name="text_databases_subject_librarian_fax">Fax:</xsl:variable>
	<xsl:variable name="text_databases_subject_librarian_telephone">Telephone:</xsl:variable>

	<xsl:variable name="text_error">Sorry, there was an error</xsl:variable>
	<xsl:variable name="text_error_access_denied">Access Denied</xsl:variable>
	<xsl:variable name="text_error_databases_permission">You do not have access to search these databases</xsl:variable>
	<xsl:variable name="text_error_databases_registered">Only available to registered users.</xsl:variable>
	<xsl:variable name="text_error_pdo_exception">There was a problem with the database.</xsl:variable>
	<xsl:variable name="text_error_not_authorized_db">Not authorized to search certain databases</xsl:variable>
	<xsl:variable name="text_error_not_found">Not Found</xsl:variable>
	<xsl:variable name="text_error_search_expired">Your search appears to have expired</xsl:variable>
	
	<xsl:variable name="text_folder_email_address">email address</xsl:variable>
	<xsl:variable name="text_folder_email_notes">notes</xsl:variable>
	<xsl:variable name="text_folder_email_options">Email Options</xsl:variable>
	<xsl:variable name="text_folder_email_pagename">Email records to yourself</xsl:variable>
	<xsl:variable name="text_folder_email_success">Email successfully sent</xsl:variable>
	<xsl:variable name="text_folder_email_subject">subject</xsl:variable>

	<xsl:variable name="text_folder_endnote_direct">directly into Endnote, Zotero, or other citation management application</xsl:variable>
	<xsl:variable name="text_folder_endnote_file">to a file I will import myself</xsl:variable>
	<xsl:variable name="text_folder_endnote_pagename">Download to Endnote, Zotero, etc.</xsl:variable>
	
	<xsl:variable name="text_folder_error_email_not_sent">Could not send email</xsl:variable>
	<xsl:variable name="text_folder_error_no_email">Please enter an email address</xsl:variable>
	
	<xsl:variable name="text_folder_export_download">Download</xsl:variable>
	<xsl:variable name="text_folder_export_export">Export</xsl:variable>
	<xsl:variable name="text_folder_export_send">Send</xsl:variable>
	<xsl:variable name="text_folder_file_pagename">Download to text file</xsl:variable>
	<xsl:variable name="text_folder_header_export">Export Records</xsl:variable>
	<xsl:variable name="text_folder_header_temporary">Temporary Saved Records</xsl:variable>
	<xsl:variable name="text_folder_limit_format">Format</xsl:variable>
	<xsl:variable name="text_folder_limit_tag">Label</xsl:variable>
	<xsl:variable name="text_folder_login_temp">
		( <a href="{//navbar/login_link}">Log-in</a> to save records beyond this session. )
	</xsl:variable>
	<xsl:variable name="text_folder_no_records">There are currently no saved records</xsl:variable>
	<xsl:variable name="text_folder_no_records_for">of</xsl:variable>
	<xsl:variable name="text_folder_options_tags">Labels</xsl:variable>
	<xsl:variable name="text_folder_options_format">Limit by Format</xsl:variable>
	<xsl:variable name="text_folder_records_export">Records to export</xsl:variable>
	<xsl:variable name="text_folder_refworks_pagename">Export to Refworks</xsl:variable>
	<xsl:variable name="text_folder_return">Return to search results</xsl:variable>
	
	<xsl:variable name="text_folder_tags_edit_updated">Your labels have been updated</xsl:variable>
	<xsl:variable name="text_folder_tags_edit_return">Return to </xsl:variable>
	<xsl:variable name="text_folder_tags_edit_return_to_records">the saved records page</xsl:variable>
	
	<xsl:variable name="text_header_collections">My Saved Databases</xsl:variable>
	<xsl:variable name="text_header_collections_subcat">Databases</xsl:variable>
	<xsl:variable name="text_header_embed">Embed</xsl:variable>
	<xsl:variable name="text_header_facets">Limit top results by:</xsl:variable>
	<xsl:variable name="text_header_login">Log-in</xsl:variable>
	<xsl:variable name="text_header_logout">
		<xsl:text>Log-out </xsl:text>
		<xsl:choose>
			<xsl:when test="//session/role = 'named'">
				<xsl:value-of select="//request/session/username" />
			</xsl:when>
			<xsl:when test="//session/role = 'guest'">
				<xsl:text>Guest</xsl:text>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="text_header_my_collections">My Collections</xsl:variable>
	<xsl:variable name="text_header_my_collections_explain">Collections are a way to organize your saved databases.</xsl:variable>
	<xsl:variable name="text_header_my_collections_new">Create a new collection:</xsl:variable>
	<xsl:variable name="text_header_my_collections_add">Add</xsl:variable>
	<xsl:variable name="text_header_myaccount">My Account</xsl:variable>
	<xsl:variable name="text_header_savedrecords">My Saved Records</xsl:variable>
	<xsl:variable name="text_header_snippet_generate">Embed</xsl:variable>
	<xsl:variable name="text_header_snippet_generate_collection">
		<xsl:copy-of select="$text_header_snippet_generate"/> Collection
	</xsl:variable>
	<xsl:variable name="text_header_snippet_generate_database">
		<xsl:copy-of select="$text_header_snippet_generate"/><xsl:text> </xsl:text><xsl:copy-of select="$text_record_database"/>
	</xsl:variable>
	<xsl:variable name="text_header_snippet_generate_subject"><xsl:copy-of select="$text_header_snippet_generate"/> Subject</xsl:variable>
  
	<xsl:variable name="text_link_holdings">Availability</xsl:variable>
	<xsl:variable name="text_link_original_record">Original record</xsl:variable>
	<xsl:variable name="text_link_resolver_available">Full text available</xsl:variable>
	<xsl:variable name="text_link_resolver_check">Check for availability</xsl:variable>
	<xsl:variable name="text_link_resolver_checking">Checking availability . . .</xsl:variable>
	<xsl:variable name="text_link_resolver_name">Link Resolver</xsl:variable>
	<xsl:variable name="text_link_resolver_load_msg">Loading content from</xsl:variable>
	<xsl:variable name="text_link_resolver_direct_link_prefix">Full-Text Available: </xsl:variable>

	<xsl:variable name="text_metasearch_error_no_databases">Please choose one or more databases to search</xsl:variable>
	<xsl:variable name="text_metasearch_error_no_search_terms">Please enter search terms</xsl:variable>
	<xsl:variable name="text_metasearch_error_not_authorized">You are not authorized to search the databases you selected. Please choose other databases and try again.</xsl:variable>
	<xsl:variable name="text_metasearch_error_too_many_databases">You can only search up to %s databases at a time</xsl:variable>
	
	<xsl:variable name="text_metasearch_hits_error">Sorry, we're having technical difficulties right now.</xsl:variable>
	<xsl:variable name="text_metasearch_hits_error_explain">
		You can try again later, or use the library's website to select and search databases individually.
	</xsl:variable>
	<xsl:variable name="text_metasearch_hits_no_match">Sorry, your search did not match any records.</xsl:variable>
	<xsl:variable name="text_metasearch_hits_pagename">Search Status</xsl:variable>
	<xsl:variable name="text_metasearch_hits_table_database">Database</xsl:variable>
	<xsl:variable name="text_metasearch_hits_table_status">Status</xsl:variable>
	<xsl:variable name="text_metasearch_hits_table_count">Hits</xsl:variable>
	<xsl:variable name="text_metasearch_hits_unfinished">
		It looks like some of the databases had technical problems.  You might want to try your search again later.
	</xsl:variable>
	<xsl:variable name="text_metasearch_hits_in_progress">Your search is still in progress. </xsl:variable>
	<xsl:variable name="text_metasearch_hits_check_status">Check the status of the search</xsl:variable>
	
	<xsl:variable name="text_metasearch_results_limit">Limit</xsl:variable>
	<xsl:variable name="text_metasearch_results_summary">
		Results <strong><xsl:value-of select="//summary/range" /></strong> 
		of <strong><xsl:value-of select="//summary/total" /></strong>	
	</xsl:variable>
	<xsl:variable name="text_metasearch_results_native_results">View results at</xsl:variable>
	<xsl:variable name="text_metasearch_results_search_results">Search results</xsl:variable>
	<xsl:variable name="text_metasearch_results_by_db">Results by database</xsl:variable>	
	<xsl:variable name="text_metasearch_results_error_merge_bug">Sorry, there was an error.</xsl:variable>
	<xsl:variable name="text_metasearch_results_error_merge_bug_try_again">
		Please <a href="{//request/server/request_uri}">try again</a>
		or select an individual set of results to the right.
	</xsl:variable>
	<xsl:variable name="text_metasearch_results_found">results found</xsl:variable>
	<xsl:variable name="text_metasearch_status_done">DONE</xsl:variable>
	<xsl:variable name="text_metasearch_status_error">ERROR</xsl:variable>
	<xsl:variable name="text_metasearch_status_fetching">FETCHING</xsl:variable>
	<xsl:variable name="text_metasearch_status_start">START</xsl:variable>
	<xsl:variable name="text_metasearch_status_started">STARTED</xsl:variable>
	<xsl:variable name="text_metasearch_status_stopped">STOPPED</xsl:variable>
	<xsl:variable name="text_metasearch_top_results">Top Results</xsl:variable>
	
	<xsl:variable name="text_record_author_corp">Corporate author</xsl:variable>
	<xsl:variable name="text_record_breadcrumb">Record</xsl:variable>
	<xsl:variable name="text_record_chapters">Chapters</xsl:variable>
	<xsl:variable name="text_record_cite_this">Cite this</xsl:variable>
	<xsl:variable name="text_record_citation_note">
		These citations are software generated and  may contain errors. To verify accuracy, 
		check the appropriate style guide.
	</xsl:variable>	
	<xsl:variable name="text_record_conf">Conference</xsl:variable>
	<xsl:variable name="text_record_contents">Contents</xsl:variable>
	<xsl:variable name="text_record_database">Database</xsl:variable>
	<xsl:variable name="text_record_degree">Degree</xsl:variable>
	<xsl:variable name="text_record_format_label">Format</xsl:variable>
	<xsl:variable name="text_record_inst">Institution</xsl:variable>
	<xsl:variable name="text_record_language_label">Language</xsl:variable>
	<xsl:variable name="text_record_notes">Additional Notes</xsl:variable>
	<xsl:variable name="text_record_publisher">Publisher</xsl:variable>
	<xsl:variable name="text_record_summary">Summary</xsl:variable>
	<xsl:variable name="text_record_summary_subjects">Covers the topics</xsl:variable>
	<xsl:variable name="text_record_summary_toc">Includes chapters on</xsl:variable>
	<xsl:variable name="text_record_subjects">Covers the topics</xsl:variable>
	<xsl:variable name="text_record_standard_nos">Standard Numbers</xsl:variable>
	<xsl:variable name="text_records_tags">Labels: </xsl:variable>
	<xsl:variable name="text_records_tags_update">Update</xsl:variable>
	<xsl:variable name="text_records_tags_updated">Updated</xsl:variable>
	<xsl:variable name="text_records_tags_update_err">Sorry, there was an error, your labels could not be updated.</xsl:variable>
	
	<xsl:variable name="text_records_fulltext_pdf">Full-Text in PDF</xsl:variable>
	<xsl:variable name="text_records_fulltext_html">Full-Text in HTML</xsl:variable>
	<xsl:variable name="text_records_fulltext_available">Full-Text Available</xsl:variable>	
	
	<xsl:variable name="text_results_author">By</xsl:variable>
	<xsl:variable name="text_results_breadcrumb">Results</xsl:variable>
	<xsl:variable name="text_results_hint_remove_limit">remove limit</xsl:variable>
	<xsl:variable name="text_results_no_title">[ No Title ]</xsl:variable>
	<xsl:variable name="text_results_published_in">Published in</xsl:variable>
	<xsl:variable name="text_results_record_saved">Record saved</xsl:variable>
	<xsl:variable name="text_results_record_saved_temp">Temporarily Saved</xsl:variable>
	<xsl:variable name="text_results_record_save_it">Save this record</xsl:variable>
	<xsl:variable name="text_results_record_saved_perm">login to save permanently</xsl:variable>
	<xsl:variable name="text_results_record_save_err">Sorry, an error occured, your record was not saved.</xsl:variable>
	<xsl:variable name="text_results_record_delete">Delete this record</xsl:variable>
	<xsl:variable name="text_results_record_removing">Removing...</xsl:variable>
	<xsl:variable name="text_results_record_saving">Saving...</xsl:variable>
	<xsl:variable name="text_results_record_delete_confirm">Are you sure you want to delete this record?</xsl:variable>
	<xsl:variable name="text_results_refereed">Peer Reviewed</xsl:variable>
	<xsl:variable name="text_results_sort_by">sort by</xsl:variable>
	<xsl:variable name="text_results_year">Year</xsl:variable>
	<xsl:variable name="text_results_next">Next</xsl:variable>
	
	<xsl:variable name="text_search_record">Record</xsl:variable>
	<xsl:variable name="text_search_module">Find Books &amp; Articles</xsl:variable>
	<xsl:variable name="text_search_results">Search results</xsl:variable>
	
	<xsl:variable name="text_searchbox_ada_boolean">Boolean operator: </xsl:variable>
	<xsl:variable name="text_searchbox_boolean_and">And</xsl:variable>
	<xsl:variable name="text_searchbox_boolean_or">Or</xsl:variable>
	<xsl:variable name="text_searchbox_boolean_without">Without</xsl:variable>
	<xsl:variable name="text_searchbox_field_keyword">all fields</xsl:variable>
	<xsl:variable name="text_searchbox_field_title">title</xsl:variable>
	<xsl:variable name="text_searchbox_field_author">author</xsl:variable>
	<xsl:variable name="text_searchbox_field_subject">subject</xsl:variable>
	<xsl:variable name="text_searchbox_field_year">year</xsl:variable>
	<xsl:variable name="text_searchbox_field_issn">ISSN</xsl:variable>
	<xsl:variable name="text_searchbox_field_isbn">ISBN</xsl:variable>
	<xsl:variable name="text_searchbox_for">for</xsl:variable>
	<xsl:variable name="text_searchbox_go">GO</xsl:variable>
	<xsl:variable name="text_searchbox_options_fewer">Fewer Options</xsl:variable>
	<xsl:variable name="text_searchbox_options_more">More Options</xsl:variable>
	<xsl:variable name="text_searchbox_search">Search</xsl:variable>
	<xsl:variable name="text_searchbox_spelling_error">Did you mean: </xsl:variable>
	
	<xsl:variable name="text_snippet_display_all">ALL</xsl:variable>
	<xsl:variable name="text_snippet_display_no">no</xsl:variable>
	<xsl:variable name="text_snippet_display_options">Display Options</xsl:variable>
	<xsl:variable name="text_snippet_display_yes">yes</xsl:variable>
	<xsl:variable name="text_snippet_example">Example</xsl:variable>	
	<xsl:variable name="text_snippet_include_html">HTML Source</xsl:variable>
	<xsl:variable name="text_snippet_include_html_explain">
		Last resort. If this is your only option, you can embed this HTML source directly into your external website. 
		However, if data or features change here, your snippet will not reflect those changes, and may even stop working. 
		Use with care.
	</xsl:variable>
	<xsl:variable name="text_snippet_include_html_source">View snippet source</xsl:variable>
	<xsl:variable name="text_snippet_include_javascript">Javascript widget</xsl:variable>
	<xsl:variable name="text_snippet_include_javascript_explain">
		Should work in any external website that allows javascript, but viewers' browsers must support javascript.
	</xsl:variable>
	<xsl:variable name="text_snippet_include_options">Include Options</xsl:variable>
	<xsl:variable name="text_snippet_include_server">Server-side include url</xsl:variable>
	<xsl:variable name="text_snippet_include_server_explain">
		Preferred method of inclusion, if your external website can support a server-side include.
	</xsl:variable>
	<xsl:variable name="text_snippet_include_url">Pass-through URL</xsl:variable>
	<xsl:variable name="text_snippet_include_url_explain">
		This link serves as a persistent URL to this database, and will also provide proxy access for off-campus users.
	</xsl:variable>
	<xsl:variable name="text_snippet_refresh">Refresh</xsl:variable>	
	<xsl:variable name="text_snippet_show_css">Include CSS?</xsl:variable>
	<xsl:variable name="text_snippet_show_css_explain">
		Including the CSS works imperfectly.  If you need to, it's better to define 
		CSS styles for the snippet in the external website itself.
	</xsl:variable>
	<xsl:variable name="text_snippet_show_databases">Show databases?</xsl:variable>
	<xsl:variable name="text_snippet_show_info_button">Show info button?</xsl:variable>
	<xsl:variable name="text_snippet_show_desc">Show description?</xsl:variable>
	<xsl:variable name="text_snippet_show_desc_lang">Description language:</xsl:variable>
	<xsl:variable name="text_snippet_show_searchbox">Show search box?</xsl:variable>
	<xsl:variable name="text_snippet_show_section">Show specific section?</xsl:variable>	
	<xsl:variable name="text_snippet_show_title">Show title?</xsl:variable>
  
	<xsl:template name="text_recommendation_header">
		People who read this <xsl:value-of select="php:function('Xerxes\Utility\Parser::strtolower', string(format))"/> also read	
	</xsl:template>

	<xsl:template name="text_number_to_words">
		<xsl:param name="number" />
		<xsl:choose>
			<xsl:when test="$number = 1">one</xsl:when>
			<xsl:when test="$number = 2">two</xsl:when>
			<xsl:when test="$number = 3">three</xsl:when>
			<xsl:when test="$number = 4">four</xsl:when>
			<xsl:when test="$number = 5">five</xsl:when>
			<xsl:when test="$number = 6">six</xsl:when>
			<xsl:when test="$number = 7">seven</xsl:when>
			<xsl:when test="$number = 8">eight</xsl:when>
			<xsl:when test="$number = 9">nine</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$number" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
		
	<xsl:template name="text_databases_subject_librarian_email_value">
		<a href="mailto:{library_email}"><xsl:value-of select="library_email" /></a>
	</xsl:template>

	<xsl:template name="text_collections_add_database_section">add database <xsl:value-of select="title_display" /> to this section</xsl:template>
	
	<xsl:template name="text_collections_remove_database">remove database <xsl:value-of select="title_display" /> from section</xsl:template>
	
	
	<!-- 
		the templates deal with text labels that are in the XML itself.  they largely
		just take the value and print it, but one could override the template and use
		a <xsl:choose> block to change the underlying value to something else
	-->
	
	<xsl:template name="text_results_language">
		<xsl:if test="language and language != 'English' and format != 'Video'">
			<span class="results-language"> written in <xsl:value-of select="language" /></span>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="text_results_format">
		<xsl:param name="format" />
		<xsl:value-of select="$format" />
	</xsl:template>
	
	<xsl:template name="text_facet_group">
		<xsl:value-of select="@name" />
	</xsl:template>
	
	<xsl:template name="text_results_sort_by">
		<xsl:param name="option" />
		<xsl:value-of select="$option" />
	</xsl:template>
	
	<xsl:template name="text_results_sort_options">
		<xsl:param name="option" />
		<xsl:value-of select="$option" />
	</xsl:template>
	
</xsl:stylesheet>
