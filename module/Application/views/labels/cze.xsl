<?xml version="1.0" encoding="utf-8"?>

<!--

 author: David Walker, Ivan Masár
 copyright: 2009 California State University, 2010 Ivan Masár
 version: $Id: cze.xsl 1898 2011-04-15 11:26:15Z helix84@centrum.sk $
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
	
	<xsl:variable name="text_ada_version">Nejlepší výsledky poskytne přístupná verze, kterou získáte kliknutím na tento odkaz</xsl:variable>
	<xsl:variable name="text_ada_table_for_display">pouze pro prohlížení</xsl:variable>
	
	<xsl:variable name="text_app_name"	select="//config/application_name" />
	
	<xsl:variable name="text_authentication_error_not_authorized">Litujeme, dle našich záznamů nemáte oprávnění používat tuto službu</xsl:variable>
	<xsl:variable name="text_authentication_login_explain"></xsl:variable>
	<xsl:variable name="text_authentication_login_failed">Litujeme, vaše uživatelské jméno nebo heslo bylo nesprávné.</xsl:variable>
	<xsl:variable name="text_authentication_login_pagename">Přihlásit se</xsl:variable>
	<xsl:variable name="text_authentication_login_password">heslo:</xsl:variable>
	<xsl:variable name="text_authentication_login_username">uživatel:</xsl:variable>
		
	<xsl:variable name="text_authentication_logout_confirm">Jste si jisti, že chcete ukončit tuto relaci?</xsl:variable>
	<xsl:variable name="text_authentication_logout_pagename">Odhlásit se</xsl:variable>
	
	<xsl:variable name="text_breadcrumb_separator"> &gt; </xsl:variable>
	<xsl:variable name="text_breadcrumb_seperator" select="$text_breadcrumb_separator" />
	
	<xsl:variable name="text_citation_apa">APA</xsl:variable>
	<xsl:variable name="text_citation_mla">MLA</xsl:variable>
	<xsl:variable name="text_citation_turabian">Turabian / Chicago</xsl:variable>
	
	<xsl:variable name="text_collections_add_database">Přidat databáze</xsl:variable>
	<xsl:variable name="text_collections_add_section">Přidat novou sekci:</xsl:variable>
	<xsl:variable name="text_collections_blank_name">Zadán prázdný název, nebude nezmeněn</xsl:variable>
	<xsl:variable name="text_collections_cancelled">Zrušeno</xsl:variable>
	<xsl:variable name="text_collections_change_database_order">Změnit pořadí databází</xsl:variable>
	<xsl:variable name="text_collections_change_name">Změnit název kolekce</xsl:variable>
	<xsl:variable name="text_collections_change_section_name">Změnit název sekce</xsl:variable>
	<xsl:variable name="text_collections_change_section_order">Změnit pořadí sekcí</xsl:variable>
	<xsl:variable name="text_collections_created_by">Vytvořil <xsl:value-of select="/*/category/@owned_by_user" /></xsl:variable>
	<xsl:variable name="text_collections_database_already_saved">Databáze už byla uložena do %s v %s</xsl:variable>
	<xsl:variable name="text_collections_database_order_changed">Pořadí databází změněno</xsl:variable>
	<xsl:variable name="text_collections_database_saved">Databáze uložena do %s</xsl:variable>
	<xsl:variable name="text_collections_delete_collection">Smazat kolekci</xsl:variable>
	<xsl:variable name="text_collections_delete_collection_confirm">Jste si jisti, že chcete smazat tuto kolekci?</xsl:variable>
	<xsl:variable name="text_collections_delete_section">Smazat sekci</xsl:variable>
	<xsl:variable name="text_collections_delete_section_confirm">Jste si jisti, že chcete smazat tuto sekci?</xsl:variable>
	<xsl:variable name="text_collections_deleted_category">Smazána kategorie %s</xsl:variable>
	<xsl:variable name="text_collections_deleted_subcategory">Smazána podkategorie %s</xsl:variable>
	<xsl:variable name="text_collections_done_editing">Dokončil jsem úpravy!</xsl:variable>
	<xsl:variable name="text_collections_edit">Přidat databáze a upravit</xsl:variable>
	
	<xsl:variable name="text_collections_error_embed_not_published">Vaše kolekce musí být publikována, aby jste moli využívat funkcionalitu embed</xsl:variable>	
	<xsl:variable name="text_collections_error_no_such_category">Zvolená kategorie nebyla nalezena.</xsl:variable>
	<xsl:variable name="text_collections_error_no_such_section">Zvolená sekce nebyla nalezena.</xsl:variable>	
	<xsl:variable name="text_collections_error_not_logged_in">Abyste mohli využívat tuto funkci, musíte se přihlásit.</xsl:variable>
	<xsl:variable name="text_collections_error_personal_collection_not_found">Osobní kolekce nebyla nalezena.</xsl:variable>
	<xsl:variable name="text_collections_error_private_collection_save">Abyste mohli ukládat do kolekce, kterou vlastní uživatel %s, musíte se přihlásit jako tento uživatel.</xsl:variable>
	<xsl:variable name="text_collections_error_private_collection">Toto je soukromá kolekce databázi, která je přístupná pouze uživateli, který ji vytvořil. Jestli vy jste tento uživatel, přihlašte se, prosím.</xsl:variable>
	
	<xsl:variable name="text_collections_list_databases">Vypsat vybrané databáze: </xsl:variable>
	<xsl:variable name="text_collections_made_private">Kolekce byla označena jako soukromá.</xsl:variable>
	<xsl:variable name="text_collections_made_published">Kolekce byla označena jako veřejná.</xsl:variable>
	<xsl:variable name="text_collections_no_matches">Nebyly nalezeny odpovídající databáze</xsl:variable>	
	<xsl:variable name="text_collections_name_changed">Název kolekce se změnil.</xsl:variable>
	<xsl:variable name="text_collections_private">Soukromá</xsl:variable>
	<xsl:variable name="text_collections_public">Veřejná</xsl:variable>
	<xsl:variable name="text_collections_public_url">Veřejné URL:</xsl:variable>
	<xsl:variable name="text_collections_publish">Vytvořit kolekci:</xsl:variable>
	<xsl:variable name="text_collections_remove_searchbox">Skončil jsem s přidáváním databází!</xsl:variable>
	<xsl:variable name="text_collections_removed_database">Databáze odstraněna</xsl:variable>
	<xsl:variable name="text_collections_renamed">Prejmenována</xsl:variable>
	<xsl:variable name="text_collections_reorder_db_title">Změnit pořadí databází</xsl:variable>
	<xsl:variable name="text_collections_reorder_subcat_title">Změnit pořadí sekcí</xsl:variable>
	<xsl:variable name="text_collections_section_new">Vytvořena nová sekce</xsl:variable>
	<xsl:variable name="text_collections_section_order_changed">Pořadí sekcí změněno</xsl:variable>
	
	<xsl:variable name="text_database_availability">Dostupnost:</xsl:variable>
	<xsl:variable name="text_database_available_registered">Dostupné pouze pro registrované uživatele.</xsl:variable>
	<xsl:variable name="text_database_available_everyone">Dostupné pro všechny.</xsl:variable>
	<xsl:variable name="text_database_coverage">Pokrytí:</xsl:variable>
	<xsl:variable name="text_database_creator">Tvůrce</xsl:variable>
	<xsl:variable name="text_database_guide">Průvodce:</xsl:variable>
	<xsl:variable name="text_database_guide_help">Průvodce jak používat tuto databázi</xsl:variable>
	<xsl:variable name="text_database_go_to_database">Přejít na tuto databázi!</xsl:variable>
	<xsl:variable name="text_database_link">Odkaz:</xsl:variable>
	<xsl:variable name="text_database_publisher">Vydavatel:</xsl:variable>
	<xsl:variable name="text_database_save_database">Uložit databázi</xsl:variable>
	<xsl:variable name="text_database_search_hints">Nápověda k vyhledávání:</xsl:variable>
	
	<xsl:variable name="text_databases_access_available">Dostupné pouze pro uživatele </xsl:variable>
	<xsl:variable name="text_databases_access_group_and">a</xsl:variable>
	<xsl:variable name="text_databases_access_users"></xsl:variable>
	
	<xsl:variable name="text_databases_az_backtop">Zpět nahoru</xsl:variable>
	<xsl:variable name="text_databases_az_breadcrumb_all">Všechny databáze</xsl:variable>
	<xsl:variable name="text_databases_az_breadcrumb_matching">Vybrané databáze</xsl:variable>
	<xsl:variable name="text_databases_az_databases">databází</xsl:variable>
	<xsl:variable name="text_databases_az_hint_info">další informace</xsl:variable>
	<xsl:variable name="text_databases_az_hint_searchable">prohledatelné pomocí <xsl:value-of select="$text_app_name" /></xsl:variable>
	<xsl:variable name="text_databases_az_letter_separator"> | </xsl:variable>
	<xsl:variable name="text_databases_az_pagename">Databáze A-Z</xsl:variable>
	<xsl:variable name="text_databases_az_search">Seznam vybraných databází: </xsl:variable>
	
	<xsl:variable name="text_databases_category_pagename">Domů</xsl:variable>
	<xsl:variable name="text_databases_category_quick_desc">
		<xsl:text>Prohledat </xsl:text>
		<xsl:call-template name="text_number_to_words">
			<xsl:with-param name="number" select="count(//category[1]/subcategory[1]/database[searchable = 1])" /> 
		</xsl:call-template>
		<xsl:text> z našich nejpopulárnějších databází.</xsl:text>
	</xsl:variable>
	<xsl:variable name="text_databases_category_subject">Vyhledat dle předmětu</xsl:variable>
	<xsl:variable name="text_databases_category_subject_desc">Vyhledat v databázích odpovídajících oboru vašeho studia.</xsl:variable>

	<xsl:variable name="text_databases_subject_hint_direct_search">Přejít přímo na </xsl:variable>
	<xsl:variable name="text_databases_subject_hint_more_info_about">Další informace o </xsl:variable>
	<xsl:variable name="text_databases_subject_hint_native_only">Klikněte na název databáze pro individuální hledání</xsl:variable>
	<xsl:variable name="text_databases_subject_hint_restricted">Omezené, klikněte na název databáze pro individuální hledání</xsl:variable>
	
	<xsl:variable name="text_databases_subject_librarian_address">Kancelář:</xsl:variable>
	<xsl:variable name="text_databases_subject_librarian_email">Email:</xsl:variable>
	<xsl:variable name="text_databases_subject_librarian_fax">Fax:</xsl:variable>
	<xsl:variable name="text_databases_subject_librarian_telephone">Telefon:</xsl:variable>

	<xsl:variable name="text_error">Litujeme, vyskytla se chyba</xsl:variable>
	<xsl:variable name="text_error_access_denied">Přístup zamítnut</xsl:variable>
	<xsl:variable name="text_error_databases_permission">Nemáte přístup k hledání v těchto databázích</xsl:variable>
	<xsl:variable name="text_error_databases_registered">Dostupné pouze pro registrované uživatele.</xsl:variable>
	<xsl:variable name="text_error_pdo_exception">Vyskytl se problém s databází.</xsl:variable>
	<xsl:variable name="text_error_not_authorized_db">Nemáte oprávnění prohledávat jisté databáze</xsl:variable>
	<xsl:variable name="text_error_not_found">Nenalezeno</xsl:variable>
	<xsl:variable name="text_error_search_expired">Zdá se, že vaše vyhledávací relace vypršela</xsl:variable>
	
	<xsl:variable name="text_folder_email_address">emailová adresa</xsl:variable>
	<xsl:variable name="text_folder_email_notes">poznámky</xsl:variable>
	<xsl:variable name="text_folder_email_options">Možnosti emailu</xsl:variable>
	<xsl:variable name="text_folder_email_pagename">Poslat záznamy na svůj email</xsl:variable>
	<xsl:variable name="text_folder_email_success">Email byl úspěšně odeslán</xsl:variable>
	<xsl:variable name="text_folder_email_subject">předmět</xsl:variable>

	<xsl:variable name="text_folder_endnote_direct">přímo do Endnote, Zotero nebo jiné aplikace pro správu citací</xsl:variable>
	<xsl:variable name="text_folder_endnote_file">do souboru, který sám importuji</xsl:variable>
	<xsl:variable name="text_folder_endnote_pagename">Stáhnout do Endnote, Zotero atd.</xsl:variable>
	
	<xsl:variable name="text_folder_error_email_not_sent">Nepodařilo se poslat mail</xsl:variable>
	<xsl:variable name="text_folder_error_no_email">Prosím, zadejte emailovou adresu</xsl:variable>
	
	<xsl:variable name="text_folder_export_download">Stáhnout</xsl:variable>
	<xsl:variable name="text_folder_export_export">Exportovat</xsl:variable>
	<xsl:variable name="text_folder_export_send">Poslat</xsl:variable>
	<xsl:variable name="text_folder_file_pagename">Stáhnout do textového souboru</xsl:variable>
	<xsl:variable name="text_folder_header_export">Exportovat záznamy</xsl:variable>
	<xsl:variable name="text_folder_header_temporary">Dočasně uložené záznamy</xsl:variable>
	<xsl:variable name="text_folder_limit_format">Formát</xsl:variable>
	<xsl:variable name="text_folder_limit_tag">Štítek</xsl:variable>
	<xsl:variable name="text_folder_login_temp">
		( <a href="{//navbar/login_link}">Přihlaste se</a>, abyste mohli své výsledky uložit a použít i po ukončení této relace. )
	</xsl:variable>
	<xsl:variable name="text_folder_no_records">Momentálně nemáte uložené žádné záznamy</xsl:variable>
	<xsl:variable name="text_folder_no_records_for">z</xsl:variable>
	<xsl:variable name="text_folder_options_tags">Štítky</xsl:variable>
	<xsl:variable name="text_folder_options_format">Omezit dle formátu</xsl:variable>
	<xsl:variable name="text_folder_records_export">Exportovat záznamy</xsl:variable>
	<xsl:variable name="text_folder_refworks_pagename">Exportovat do Refworks</xsl:variable>
	<xsl:variable name="text_folder_return">Zpět na výsledky vyhledávání</xsl:variable>
	
	<xsl:variable name="text_folder_tags_edit_updated">Vaše štítky byli aktualizovány</xsl:variable>
	<xsl:variable name="text_folder_tags_edit_return">Zpět na </xsl:variable>
	<xsl:variable name="text_folder_tags_edit_return_to_records">stránku uložených záznamů</xsl:variable>
	
	<xsl:variable name="text_header_collections">Moje uložené databáze</xsl:variable>
	<xsl:variable name="text_header_collections_subcat">Databáze</xsl:variable>
	<xsl:variable name="text_header_embed">Vložit</xsl:variable>
	<xsl:variable name="text_header_facets">Omezit první výsledky dle:</xsl:variable>
	<xsl:variable name="text_header_login">Přihlásit se</xsl:variable>
	<xsl:variable name="text_header_logout">
		<xsl:text>Odhlásit </xsl:text>
		<xsl:choose>
			<xsl:when test="//session/role = 'named'">
				<xsl:text>uživatele </xsl:text><xsl:value-of select="//request/session/username" />
			</xsl:when>
			<xsl:when test="//session/role = 'guest'">
				<xsl:text>hosta</xsl:text>
			</xsl:when>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="text_header_my_collections">Moje kolekce</xsl:variable>
	<xsl:variable name="text_header_my_collections_explain">Kolekce slouží k organizaci vašich uložených databází.</xsl:variable>
	<xsl:variable name="text_header_my_collections_new">Vytvořit novou kolekci:</xsl:variable>
	<xsl:variable name="text_header_my_collections_add">Přidat</xsl:variable>
	<xsl:variable name="text_header_myaccount">Můj účet</xsl:variable>
	<xsl:variable name="text_header_savedrecords">Moje uložené záznamy</xsl:variable>
	<xsl:variable name="text_header_snippet_generate">Vložit</xsl:variable>
	<xsl:variable name="text_header_snippet_generate_collection">
		<xsl:copy-of select="$text_header_snippet_generate"/> Kolekce
	</xsl:variable>
	<xsl:variable name="text_header_snippet_generate_database">
		<xsl:copy-of select="$text_header_snippet_generate"/><xsl:text> </xsl:text><xsl:copy-of select="$text_record_database"/>
	</xsl:variable>
	<xsl:variable name="text_header_snippet_generate_subject"><xsl:copy-of select="$text_header_snippet_generate"/> Předmětovou kategorii</xsl:variable>
  
	<xsl:variable name="text_link_holdings">Dostupnost</xsl:variable>
	<xsl:variable name="text_link_original_record">Původní záznam</xsl:variable>
	<xsl:variable name="text_link_resolver_available">Dostupný plný text</xsl:variable>
	<xsl:variable name="text_link_resolver_check">Zkontrolovat dostupnost</xsl:variable>
	<xsl:variable name="text_link_resolver_checking">Kontroluje se dostupnost . . .</xsl:variable>
	<xsl:variable name="text_link_resolver_name">Překladač odkazů</xsl:variable>
	<xsl:variable name="text_link_resolver_load_msg">Načítá se obsah z</xsl:variable>
	<xsl:variable name="text_link_resolver_direct_link_prefix">Dostupný plný text: </xsl:variable>
	
	<xsl:variable name="text_metasearch_error_no_databases">Prosím, vyberte jednu nebo více databází k prohledání</xsl:variable>
	<xsl:variable name="text_metasearch_error_no_search_terms">Prosím, vyplňte vyhledávací dotaz</xsl:variable>
	<xsl:variable name="text_metasearch_error_not_authorized">Nemáte oprávnění prohledávat databáze, které jste zvolili. Prosím, vyberte jiné databáze a zkuste to znovu.</xsl:variable>
	<xsl:variable name="text_metasearch_error_too_many_databases">Můžete prohledávat pouze %s databází najednou</xsl:variable>
	
	<xsl:variable name="text_metasearch_hits_error">Litujeme, momentálně máme technické potíže.</xsl:variable>
	<xsl:variable name="text_metasearch_hits_error_explain">
		Můžete to zkusit později nebo použít web knihovny a jednotlivě prohledávat databáze.
	</xsl:variable>
	<xsl:variable name="text_metasearch_hits_no_match">Litujeme, vaše vyhledávání nepřineslo žádné výsledky.</xsl:variable>
	<xsl:variable name="text_metasearch_hits_pagename">Stav hledání</xsl:variable>
	<xsl:variable name="text_metasearch_hits_table_database">Databáze</xsl:variable>
	<xsl:variable name="text_metasearch_hits_table_status">Stav</xsl:variable>
	<xsl:variable name="text_metasearch_hits_table_count">Nalezených</xsl:variable>
	<xsl:variable name="text_metasearch_hits_unfinished">
		Pravděpodobně má některá z databází technické potíže. Můžete se později vrátit a opakovat vyhledávání.
	</xsl:variable>
	<xsl:variable name="text_metasearch_hits_in_progress">Hledání pořád probíhá. </xsl:variable>
	<xsl:variable name="text_metasearch_hits_check_status">Zkontrolovat stav hledání</xsl:variable>
	
	<xsl:variable name="text_metasearch_results_limit">Omezení</xsl:variable>
	<xsl:variable name="text_metasearch_results_summary">
		Výsledky <strong><xsl:value-of select="//summary/range" /></strong> 
		z <strong><xsl:value-of select="//summary/total" /></strong>	
	</xsl:variable>
	<xsl:variable name="text_metasearch_results_native_results">Zobrazit výsledky na</xsl:variable>
	<xsl:variable name="text_metasearch_results_search_results">Výsledky hledání</xsl:variable>
	<xsl:variable name="text_metasearch_results_by_db">Výsledky dle databáze</xsl:variable>	
	<xsl:variable name="text_metasearch_results_error_merge_bug">Litujeme, došlo k chybě.</xsl:variable>
	<xsl:variable name="text_metasearch_results_error_merge_bug_try_again">
		Prosím, <a href="{//request/server/request_uri}">zkuste to znovu</a>
		nebo si vyberte individuální sadu výsledků vpravo.
	</xsl:variable>
	<xsl:variable name="text_metasearch_results_found">nalezených výsledků</xsl:variable>
	<xsl:variable name="text_metasearch_status_done">HOTOVO</xsl:variable>
	<xsl:variable name="text_metasearch_status_error">CHYBA</xsl:variable>
	<xsl:variable name="text_metasearch_status_fetching">STAHUJE SE</xsl:variable>
	<xsl:variable name="text_metasearch_status_start">SPUSTIT</xsl:variable>
	<xsl:variable name="text_metasearch_status_started">SPUŠTĚNO</xsl:variable>
	<xsl:variable name="text_metasearch_status_stopped">ZASTAVENO</xsl:variable>
	<xsl:variable name="text_metasearch_top_results">Výsledky</xsl:variable>
	
	<xsl:variable name="text_record_author_corp">Korporativní autor</xsl:variable>
	<xsl:variable name="text_record_breadcrumb">Záznam</xsl:variable>
	<xsl:variable name="text_record_chapters">Kapitoly</xsl:variable>
	<xsl:variable name="text_record_cite_this">Citovat</xsl:variable>
	<xsl:variable name="text_record_citation_note">
		Tyto citace vytvořil software a mohou obsahovat chyby. Pro ověření přesnosti si nastudujte příslušnou citační normu nebo příručku.
	</xsl:variable>	
	<xsl:variable name="text_record_conf">Konference</xsl:variable>
	<xsl:variable name="text_record_contents">Obsah</xsl:variable>
	<xsl:variable name="text_record_database">Databáze</xsl:variable>
	<xsl:variable name="text_record_degree">Degree</xsl:variable> <!-- TODO -->
	<xsl:variable name="text_record_format_label">Formát</xsl:variable>
	<xsl:variable name="text_record_inst">Instituce</xsl:variable>
	<xsl:variable name="text_record_language_label">Jazyk</xsl:variable>
	<xsl:variable name="text_record_notes">Další poznámky</xsl:variable>
	<xsl:variable name="text_record_publisher">Vydavatel</xsl:variable>
	<xsl:variable name="text_record_summary">Shrnutí</xsl:variable>
	<xsl:variable name="text_record_summary_subjects">Pokrývá témata</xsl:variable>
	<xsl:variable name="text_record_summary_toc">Obsahuje kapitoly o</xsl:variable>
	<xsl:variable name="text_record_subjects">Pokrývá témata</xsl:variable>
	<xsl:variable name="text_record_standard_nos">Standardní čísla</xsl:variable>
	<xsl:variable name="text_records_tags">Štítky: </xsl:variable>
	<xsl:variable name="text_records_tags_update">Aktualizovat</xsl:variable>
	<xsl:variable name="text_records_tags_updated">Aktualizovány</xsl:variable>
	<xsl:variable name="text_records_tags_update_err">Litujeme, nastala chyba. Vaše štítky nebylo možné aktualizovat.</xsl:variable>
	
	<xsl:variable name="text_records_fulltext_pdf">Plný text v PDF</xsl:variable>
	<xsl:variable name="text_records_fulltext_html">Plný text v HTML</xsl:variable>
	<xsl:variable name="text_records_fulltext_available">Dostupný plný text</xsl:variable>	
	
	<xsl:variable name="text_results_author">Autor</xsl:variable>
	<xsl:variable name="text_results_breadcrumb">Výsledky</xsl:variable>
	<xsl:variable name="text_results_hint_remove_limit">odstranit limit</xsl:variable>
	<xsl:variable name="text_results_no_title">[ Bez názvu ]</xsl:variable>
	<xsl:variable name="text_results_published_in">Publikováno v</xsl:variable>
	<xsl:variable name="text_results_record_saved">Záznam uložen</xsl:variable>
	<xsl:variable name="text_results_record_saved_temp">Dočasně uložen</xsl:variable>
	<xsl:variable name="text_results_record_save_it">Uložit tento záznam</xsl:variable>
	<xsl:variable name="text_results_record_saved_perm">trvalé uložení je možné po přihlášení</xsl:variable>
	<xsl:variable name="text_results_record_save_err">Litujeme, nastala chyba. Váš záznam nebyl uložen.</xsl:variable>
	<xsl:variable name="text_results_record_delete">Smazat tento záznam</xsl:variable>
	<xsl:variable name="text_results_record_saving">Ukládá se...</xsl:variable>
	<xsl:variable name="text_results_record_delete_confirm">Jste si jisti, že chcete smazat tento záznam?</xsl:variable>
	<xsl:variable name="text_results_record_removing">Odstraňuje se...</xsl:variable>
	<xsl:variable name="text_results_refereed">Recenzovaný</xsl:variable>
	<xsl:variable name="text_results_sort_by">řadit dle</xsl:variable>
	<xsl:variable name="text_results_year">Rok</xsl:variable>
	<xsl:variable name="text_results_next">Další</xsl:variable>
	
	<xsl:variable name="text_searchbox_ada_boolean">Booleovský operátor: </xsl:variable>
	<xsl:variable name="text_searchbox_boolean_and">a</xsl:variable>
	<xsl:variable name="text_searchbox_boolean_or">nebo</xsl:variable>
	<xsl:variable name="text_searchbox_boolean_without">Bez</xsl:variable>
	<xsl:variable name="text_searchbox_field_keyword">ve všech polích</xsl:variable>
	<xsl:variable name="text_searchbox_field_title">v názvu</xsl:variable>
	<xsl:variable name="text_searchbox_field_author">v autorech</xsl:variable>
	<xsl:variable name="text_searchbox_field_subject">v předmětu</xsl:variable>
	<xsl:variable name="text_searchbox_field_year">rok</xsl:variable>
	<xsl:variable name="text_searchbox_field_issn">ISSN</xsl:variable>
	<xsl:variable name="text_searchbox_field_isbn">ISBN</xsl:variable>
	<xsl:variable name="text_searchbox_for"></xsl:variable>
	<xsl:variable name="text_searchbox_go">Vykonat</xsl:variable>
	<xsl:variable name="text_searchbox_options_fewer">Méně možností</xsl:variable>
	<xsl:variable name="text_searchbox_options_more">Další možnosti</xsl:variable>
	<xsl:variable name="text_searchbox_search">Hledat</xsl:variable>
	<xsl:variable name="text_searchbox_spelling_error">Měli jste na mysli: </xsl:variable>
	
	<xsl:variable name="text_snippet_display_all">VŠECHNY</xsl:variable>
	<xsl:variable name="text_snippet_display_no">ne</xsl:variable>
	<xsl:variable name="text_snippet_display_options">Možnosti zobrazení</xsl:variable>
	<xsl:variable name="text_snippet_display_yes">ano</xsl:variable>
	<xsl:variable name="text_snippet_example">Příklad</xsl:variable>	
	<xsl:variable name="text_snippet_include_html">Zdroj HTML</xsl:variable>
	<xsl:variable name="text_snippet_include_html_explain">
		Poslední možnost. Pokud nemáte jinou možnost, můžete vložit tento HTML zdroj přímo do vaší externí webstránky.
		Nicméně, dojde-li ke změně dat nebo formátu, váš kód nebude reflektovat tyto změny a může dokonce přestat fungovat.
		Používejte opatrně.
	</xsl:variable>
	<xsl:variable name="text_snippet_include_html_source">Zobrazit zdrojový kód</xsl:variable>
	<xsl:variable name="text_snippet_include_javascript">Javascriptový widget</xsl:variable>
	<xsl:variable name="text_snippet_include_javascript_explain">
		Měl by fungovat na libovolné externí webstránce umožňující použití Javascriptu, ale prohlížeče návštěvníků musí podporovat Javascript.
	</xsl:variable>
	<xsl:variable name="text_snippet_include_options">Možnosti vložení</xsl:variable>
	<xsl:variable name="text_snippet_include_server">URL server-side include</xsl:variable>
	<xsl:variable name="text_snippet_include_server_explain">
		Preferovaný způsob vkládání, pokud vaše externí webstránka podporuje server-side include.
	</xsl:variable>
	<xsl:variable name="text_snippet_include_url">Přesměrovací URL</xsl:variable>
	<xsl:variable name="text_snippet_include_url_explain">
		Tento odkaz slouží jako perzistentní URL na tuto databázi a poskytne také přístup přes proxy pro uživatele mimo univerzitní síť.
	</xsl:variable>
	<xsl:variable name="text_snippet_refresh">Obnovit</xsl:variable>	
	<xsl:variable name="text_snippet_show_css">Vložit CSS?</xsl:variable>
	<xsl:variable name="text_snippet_show_css_explain">
		Vložení CSS souboru funguje nedokonale. Vhodnější je definovat CSS styly kódu na samotné externí stránce.
	</xsl:variable>
	<xsl:variable name="text_snippet_show_databases">Zobrazit databáze?</xsl:variable>
	<xsl:variable name="text_snippet_show_info_button">Zobrazit tlačítko info?</xsl:variable>
	<xsl:variable name="text_snippet_show_desc">Zobrazit popis?</xsl:variable>
	<xsl:variable name="text_snippet_show_desc_lang">Jazyk popisu:</xsl:variable>
	<xsl:variable name="text_snippet_show_searchbox">Zobrazit vyhledávací pole?</xsl:variable>
	<xsl:variable name="text_snippet_show_section">Zobrazit konkrétní sekci?</xsl:variable>	
	<xsl:variable name="text_snippet_show_title">Zobrazit název?</xsl:variable>
  
	<xsl:template name="text_recommendation_header">
		Lidé, kteří čtou tento <xsl:value-of select="php:function('Xerxes\Utility\Parser::strtolower', string(format/public))"/> čtou také	
	</xsl:template>

	<xsl:template name="text_number_to_words">
		<xsl:param name="number" />
		<xsl:choose>
			<xsl:when test="$number = 1">jednu</xsl:when>
				<xsl:when test="$number = 2">dvě</xsl:when>
			<xsl:when test="$number = 3">tři</xsl:when>
			<xsl:when test="$number = 4">čtyři</xsl:when>
			<xsl:when test="$number = 5">pět</xsl:when>
			<xsl:when test="$number = 6">šest</xsl:when>
			<xsl:when test="$number = 7">sedm</xsl:when>
			<xsl:when test="$number = 8">osum</xsl:when>
			<xsl:when test="$number = 9">devět</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$number" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
		
	<xsl:template name="text_databases_subject_librarian_email_value">
		<a href="mailto:{library_email}"><xsl:value-of select="library_email" /></a>
	</xsl:template>

	<xsl:template name="text_collections_add_database_section">přidat databázi <xsl:value-of select="title_display" /> do této sekce</xsl:template>
	
	<xsl:template name="text_collections_remove_database">odstranit databázi <xsl:value-of select="title_display" /> z této sekce</xsl:template>
	
	
	<!-- 
		the templates deal with text labels that are in the XML itself.  they largely
		just take the value and print it, but one could override the template and use
		a <xsl:choose> block to change the underlying value to something else
	-->
	
	<xsl:template name="text_results_language">
		<xsl:if test="language and language != 'angličtina' and format/internal != 'VIDEO'">
			<span>, </span><span class="results-language">jazyk: <xsl:value-of select="language" /></span>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="text_results_format">
		<xsl:param name="format" />
		<xsl:choose>
			<xsl:when test="$format = 'Thesis'">Kvalifikační práce</xsl:when>
			<xsl:when test="$format = 'Dissertation'">Dizertační práce</xsl:when>
			<xsl:when test="$format = 'Conference Paper'">Příspěvek do konference</xsl:when>
			<xsl:when test="$format = 'Conference Proceeding'">Sborník z konference</xsl:when>
			<xsl:when test="$format = 'Hearing'"></xsl:when>
			<xsl:when test="$format = 'Working Paper'"></xsl:when>
			<xsl:when test="$format = 'Book Review'">Recenze knihy</xsl:when>
			<xsl:when test="$format = 'Film Review'">Recenze filmu</xsl:when>
			<xsl:when test="$format = 'Review'">Recenze</xsl:when>
			<xsl:when test="$format = 'Book Chapter'">Kapitola knihy</xsl:when>
			<xsl:when test="$format = 'Article'">Článek</xsl:when>
			<xsl:when test="$format = 'Book'">Kniha</xsl:when>
			<xsl:when test="$format = 'Pamphlet'">Brožura</xsl:when>
			<xsl:when test="$format = 'Essay'">Esej</xsl:when>
			<xsl:when test="$format = 'Microfilm'">Mikrofilm</xsl:when>
			<xsl:when test="$format = 'Microfiche'">Mikrofiš</xsl:when>
			<xsl:when test="$format = 'Micropaque'">Mikrotisk</xsl:when>
			<xsl:when test="$format = 'Book--Large print'">Kniha--velký formát</xsl:when>
			<xsl:when test="$format = 'Book--Braille'">Kniha--Braillovo písmo</xsl:when>
			<xsl:when test="$format = 'eBook'">Elektronická kniha</xsl:when>
			<xsl:when test="$format = 'Archive'">Archiv</xsl:when>
			<xsl:when test="$format = 'Map'">Mapa</xsl:when>
			<xsl:when test="$format = 'Printed Music'">Tištěná hudebnina</xsl:when>
			<xsl:when test="$format = 'Audio Book'">Zvuková kniha</xsl:when>
			<xsl:when test="$format = 'Sound Recording'">Zvukový záznam</xsl:when>
			<xsl:when test="$format = 'Photograph or Slide'">Snímek</xsl:when>
			<xsl:when test="$format = 'Video'">Video</xsl:when>
			<xsl:when test="$format = 'Website'">Webová stránka</xsl:when>
			<xsl:when test="$format = 'Computer File'">Počítačový soubor</xsl:when>
			<xsl:when test="$format = 'Journal or Newspaper'">Časopis nebo noviny</xsl:when>
			<xsl:when test="$format = 'Patent'">Patent</xsl:when>

			<xsl:when test="$format = 'Unknown'">Neznámý formát</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$format" />
			</xsl:otherwise>
		</xsl:choose>
		<!--xsl:value-of select="$format" /-->
	</xsl:template>
	
	<xsl:template name="text_facet_group">
		<xsl:choose>
			<xsl:when test="@name = 'TOPIC'">téma</xsl:when>
			<xsl:when test="@name = 'DATE'">datum</xsl:when>
			<xsl:when test="@name = 'AUTHOR'">autor</xsl:when>
			<xsl:when test="@name = 'JOURNAL'">časopis</xsl:when>
			<xsl:when test="@name = 'DATABASE'">databáze</xsl:when>
			<xsl:when test="@name = 'SUBJECT'">předmět</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="@name" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_results_sort_by">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'relevance'">relevance</xsl:when>
			<xsl:when test="$option = 'date'">data</xsl:when>
			<xsl:when test="$option = 'title'">názvu</xsl:when>
			<xsl:when test="$option = 'author'">autora</xsl:when>
			<xsl:when test="$option = 'most recently added'">naposledy přidané</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_results_sort_options">
		<xsl:param name="option" />
		<xsl:value-of select="$option" />
	</xsl:template>
	
</xsl:stylesheet>
