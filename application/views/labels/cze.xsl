<?xml version="1.0" encoding="utf-8"?>

<!--

 This file is part of Xerxes.

 (c) California State University <library@calstate.edu>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

-->
<!--

 Czech labels
 author: Ivan Masár <helix84@centrum.sk>
 
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
		( <a href="{//navbar/element[@id='login']/url}">Přihlaste se</a>, abyste mohli své výsledky uložit a použít i po ukončení této relace. )
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
			<xsl:when test="//request/authorization_info/affiliated[@user_account = 'true']">
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
		Tyto citace vytvořil software a mohou obsahovat chyby.
		Pro ověření přesnosti si nastudujte příslušnou citační normu nebo příručku.
	</xsl:variable>	
	<xsl:variable name="text_record_conf">Konference</xsl:variable>
	<xsl:variable name="text_record_contents">Obsah</xsl:variable>
	<xsl:variable name="text_record_database">Databáze</xsl:variable>
	<xsl:variable name="text_record_degree">Stupeň</xsl:variable>
	<xsl:variable name="text_record_edition">Vydání</xsl:variable>
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
	<xsl:variable name="text_results_hint_remove_limit">odstranit omezení</xsl:variable>
	<xsl:variable name="text_results_no_title">[ Bez názvu ]</xsl:variable>
	<xsl:variable name="text_results_published_in">Publikováno v</xsl:variable>
	<xsl:variable name="text_results_record_hold">Rezervovat tuto položku</xsl:variable>
	<xsl:variable name="text_results_record_recall">Požádat o vrácení této položky</xsl:variable>
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
	
	<xsl:variable name="text_search_combined">Všechny výsledky</xsl:variable>
	<xsl:variable name="text_search_record">Záznam</xsl:variable>
	<xsl:variable name="text_search_module">Hledat knihy a články</xsl:variable>
	<xsl:variable name="text_search_results">Výsledky vyhledávání</xsl:variable>
	
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
		Vložení souboru CSS funguje nedokonale.
		Vhodnější je definovat CSS styly kódu na samotné externí stránce.
	</xsl:variable>
	<xsl:variable name="text_snippet_show_databases">Zobrazit databáze?</xsl:variable>
	<xsl:variable name="text_snippet_show_info_button">Zobrazit tlačítko info?</xsl:variable>
	<xsl:variable name="text_snippet_show_desc">Zobrazit popis?</xsl:variable>
	<xsl:variable name="text_snippet_show_desc_lang">Jazyk popisu:</xsl:variable>
	<xsl:variable name="text_snippet_show_searchbox">Zobrazit vyhledávací pole?</xsl:variable>
	<xsl:variable name="text_snippet_show_section">Zobrazit konkrétní sekci?</xsl:variable>	
	<xsl:variable name="text_snippet_show_title">Zobrazit název?</xsl:variable>
  
	<xsl:template name="text_recommendation_header">
		Lidé, kteří čtou tento <xsl:value-of select="php:function('Xerxes_Framework_Parser::strtolower', string(format))"/> čtou také	
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
		<xsl:if test="language and language != 'angličtina' and format != 'VIDEO'">
			<span>, </span><span class="resultsLanguage">jazyk: <xsl:value-of select="language" /></span>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="text_results_format">
		<xsl:param name="format" />
		<xsl:choose>
			<xsl:when test="$format = 'archive'">archiv</xsl:when>
			<xsl:when test="$format = 'article'">článek</xsl:when>
			<xsl:when test="$format = 'Archive'">archiv</xsl:when>
			<xsl:when test="$format = 'Article'">článek</xsl:when>
			<xsl:when test="$format = 'Audio Book'">zvuková kniha</xsl:when>
			<xsl:when test="$format = 'Book--Braille'">kniha--braillovo písmo</xsl:when>
			<xsl:when test="$format = 'Book Chapter'">kapitola knihy</xsl:when>
			<xsl:when test="$format = 'Book'">kniha</xsl:when>
			<xsl:when test="$format = 'Book / eBook'">kniha / eKniha</xsl:when>
			<xsl:when test="$format = 'Book--Large print'">kniha--velký formát</xsl:when>
			<xsl:when test="$format = 'Book Review'">recenze knihy</xsl:when>
			<xsl:when test="$format = 'Computer File'">počítačový soubor</xsl:when>
			<xsl:when test="$format = 'Conference Paper'">příspěvek do konference</xsl:when>
			<xsl:when test="$format = 'Conference Proceeding'">sborník z konference</xsl:when>
			<xsl:when test="$format = 'Dissertation'">dizertační práce</xsl:when>
			<xsl:when test="$format = 'eBook'">elektronická kniha</xsl:when>
			<xsl:when test="$format = 'Essay'">esej</xsl:when>
			<xsl:when test="$format = 'Film Review'">recenze filmu</xsl:when>
			<xsl:when test="$format = 'Hearing'">projednání</xsl:when>
			<xsl:when test="$format = 'Journal'">odborný časopis</xsl:when>
			<xsl:when test="$format = 'Journal or Newspaper'">časopis nebo noviny</xsl:when>
			<xsl:when test="$format = 'Journal Article'">článek v odborném časopise</xsl:when>
			<xsl:when test="$format = 'Magazine Article'">článek v časopise</xsl:when>
			<xsl:when test="$format = 'Map'">mapa</xsl:when>
			<xsl:when test="$format = 'Microfiche'">mikrofiš</xsl:when>
			<xsl:when test="$format = 'Microfilm'">mikrofilm</xsl:when>
			<xsl:when test="$format = 'Micropaque'">mikrotisk</xsl:when>
			<xsl:when test="$format = 'Newspaper Article'">článek v novinách</xsl:when>
			<xsl:when test="$format = 'Pamphlet'">brožura</xsl:when>
			<xsl:when test="$format = 'Patent'">patent</xsl:when>
			<xsl:when test="$format = 'Photograph or Slide'">snímek</xsl:when>
			<xsl:when test="$format = 'Printed Music'">tištěná hudebnina</xsl:when>
			<xsl:when test="$format = 'Review'">recenze</xsl:when>
			<xsl:when test="$format = 'Report'">zpráva</xsl:when>
			<xsl:when test="$format = 'Sound Recording'">zvukový záznam</xsl:when>
			<xsl:when test="$format = 'Thesis'">kvalifikační práce</xsl:when>
			<xsl:when test="$format = 'Video'">video</xsl:when>
			<xsl:when test="$format = 'Website'">webová stránka</xsl:when>
			<xsl:when test="$format = 'Working Paper'">pracovní, podkladová studie</xsl:when>
			<xsl:when test="$format = 'Web Resource'">webový zdroj</xsl:when>
			<xsl:when test="$format = 'Archival Material'">archivní materiál</xsl:when>
			<xsl:when test="$format = 'Audio Recording'">zvukový záznam</xsl:when>
			<xsl:when test="$format = 'Case'">případ</xsl:when>
			<xsl:when test="$format = 'Data Set'">datová sada</xsl:when>
			<xsl:when test="$format = 'Electronic Resource'">elektronický zdroj</xsl:when>
			<xsl:when test="$format = 'Exam'">zkouška</xsl:when>
			<xsl:when test="$format = 'Film'">film</xsl:when>
			<xsl:when test="$format = 'Government Document'">vládní dokument</xsl:when>
			<xsl:when test="$format = 'Image'">obrázek</xsl:when>
			<xsl:when test="$format = 'Journal / eJournal'">odborný časopis / eČasopis</xsl:when>
			<xsl:when test="$format = 'Magazine'">časopis</xsl:when>
			<xsl:when test="$format = 'Manuscript'">rukopis</xsl:when>
			<xsl:when test="$format = 'Market Research'">průzkum trhu</xsl:when>
			<xsl:when test="$format = 'Microform'">mikrozáznam</xsl:when>
			<xsl:when test="$format = 'Mikrofilm'">mikrofilm</xsl:when>
			<xsl:when test="$format = 'Model'">model</xsl:when>
			<xsl:when test="$format = 'Music Score'">hudební partitura</xsl:when>
			<xsl:when test="$format = 'Newspaper'">noviny</xsl:when>
			<xsl:when test="$format = 'Newsletter'">newsletter</xsl:when>
			<xsl:when test="$format = 'Paper'">dokument</xsl:when>
			<xsl:when test="$format = 'Play'">hra</xsl:when>
			<xsl:when test="$format = 'Poem'">báseň</xsl:when>
			<xsl:when test="$format = 'Presentation'">prezentace</xsl:when>
			<xsl:when test="$format = 'Publication'">publikace</xsl:when>
			<xsl:when test="$format = 'Reference'">příručka</xsl:when>
			<xsl:when test="$format = 'Spoken Word Recording'">záznam mluveného slova</xsl:when>
			<xsl:when test="$format = 'Standard'">standard</xsl:when>
			<xsl:when test="$format = 'Technical Report'">technická zpráva</xsl:when>
			<xsl:when test="$format = 'Trade Publication Article'">článek v obchodní publikaci</xsl:when>
			<xsl:when test="$format = 'Transcript'">transkript</xsl:when>
			<xsl:when test="$format = 'Video Recording'">videozáznam</xsl:when>
			
			<xsl:when test="$format = 'Unknown'">Neznámý formát</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$format" />
			</xsl:otherwise>
		</xsl:choose>
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
			<xsl:when test="$option = 'recent'">datum přidání</xsl:when>
			<xsl:when test="$option = 'title'">název</xsl:when>
			<xsl:when test="$option = 'author'">autor</xsl:when>
			<xsl:when test="$option = 'date'">nejnovější</xsl:when>
			<xsl:when test="$option = 'date-old'">nejstarší</xsl:when>

			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_facet_fields">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'IsScholarly'">Vědecké</xsl:when>
			<xsl:when test="$option = 'ContentType'">Formát</xsl:when>
			<xsl:when test="$option = 'SubjectTerms'">Téma</xsl:when>
			<xsl:when test="$option = 'Discipline'">Oblast</xsl:when>
			<xsl:when test="$option = 'PublicationDate'">Datum publikace</xsl:when>
			<xsl:when test="$option = 'Language'">Jazyk</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_facet_groups">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'format'">Formát</xsl:when>
			<xsl:when test="$option = 'label'">Štítky</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_facet_subject">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'accounting'">účetnictví</xsl:when>
			<xsl:when test="$option = 'acoustics'">akustika</xsl:when>
			<xsl:when test="$option = 'adult'">dospělý</xsl:when>
			<xsl:when test="$option = 'agriculture'">zemědělství</xsl:when>
			<xsl:when test="$option = 'agronomy'">agronomie</xsl:when>
			<xsl:when test="$option = 'algorithms'">algoritmy</xsl:when>
			<xsl:when test="$option = 'analysis'">analýza</xsl:when>
			<xsl:when test="$option = 'animals'">zvířata</xsl:when>
			<xsl:when test="$option = 'anthropology'">antropologie</xsl:when>
			<xsl:when test="$option = 'antibodies'">protilátky</xsl:when>
			<xsl:when test="$option = 'apoptosis'">apoptóza</xsl:when>
			<xsl:when test="$option = 'applied sciences'">aplikované vědy</xsl:when>
			<xsl:when test="$option = 'architecture'">architektura</xsl:when>
			<xsl:when test="$option = 'article'">článek</xsl:when>
			<xsl:when test="$option = 'artificial intelligence'">umělá inteligence</xsl:when>
			<xsl:when test="$option = 'arts &amp; humanities, general'">umění a humanitní obory, obecné</xsl:when>
			<xsl:when test="$option = 'art'">umění</xsl:when>
			<xsl:when test="$option = 'asia'">Ázie</xsl:when>
			<xsl:when test="$option = 'astronomy &amp; astrophysics'">astronomie a astrofyzika</xsl:when>
			<xsl:when test="$option = 'astronomy &amp; astrophysics'">astronomie a astrofyzika</xsl:when>
			<xsl:when test="$option = 'astronomy'">astronomie</xsl:when>
			<xsl:when test="$option = 'astrophysics'">astrofyzika</xsl:when>
			<xsl:when test="$option = 'automation &amp; control systems'">automatizační a řídicí systémy</xsl:when>
			<xsl:when test="$option = 'automation'">automatizace</xsl:when>
			<xsl:when test="$option = 'banking industry'">bankovní sektor</xsl:when>
			<xsl:when test="$option = 'banks'">banky</xsl:when>
			<xsl:when test="$option = 'behavioral sciences'">behaviorální vědy</xsl:when>
			<xsl:when test="$option = 'behavior'">chování</xsl:when>
			<xsl:when test="$option = 'biochemistry &amp; molecular biology'">biochemie a molekulární biologie</xsl:when>
			<xsl:when test="$option = 'biochemistry'">biochemie</xsl:when>
			<xsl:when test="$option = 'biological sciences'">biologické vědy</xsl:when>
			<xsl:when test="$option = 'biology'">biologie</xsl:when>
			<xsl:when test="$option = 'biomechanics'">biomechanika</xsl:when>
			<xsl:when test="$option = 'biomedical engineering'">biomedicínské inženýrství</xsl:when>
			<xsl:when test="$option = 'biophysics'">biofyzika</xsl:when>
			<xsl:when test="$option = 'biotechnology &amp; applied microbiology'">biotechnologie a aplikovaná mikrobiologie</xsl:when>
			<xsl:when test="$option = 'biotechnology &amp; applied microbiology'">výživa a dietetika</xsl:when>
			<xsl:when test="$option = 'bone'">kost</xsl:when>
			<xsl:when test="$option = 'book reviews'">knižní recenze</xsl:when>
			<xsl:when test="$option = 'books'">knihy</xsl:when>
			<xsl:when test="$option = 'brain'">mozek</xsl:when>
			<xsl:when test="$option = 'breast cancer'">rakovina prsu</xsl:when>
			<xsl:when test="$option = 'business conditions'">obchodní podmínky</xsl:when>
			<xsl:when test="$option = 'business, finance'">podnikání, finance</xsl:when>
			<xsl:when test="$option = 'business'">podnikání</xsl:when>
			<xsl:when test="$option = 'canada'">Kanada</xsl:when>
			<xsl:when test="$option = 'cancer'">rakovina</xsl:when>
			<xsl:when test="$option = 'carbon nanotubes'">uhlíkové nanotrubičky</xsl:when>
			<xsl:when test="$option = 'carcinoma'">karcinom</xsl:when>
			<xsl:when test="$option = 'cardiac &amp; cardiovascular systems'">srdeční a kardiovaskulární systém</xsl:when>
			<xsl:when test="$option = 'care and treatment'">péče a léčba</xsl:when>
			<xsl:when test="$option = 'case studies'">případové studie</xsl:when>
			<xsl:when test="$option = 'cell biology'">buněčná biologie</xsl:when>
			<xsl:when test="$option = 'cells'">buňky</xsl:when>
			<xsl:when test="$option = 'chemicals'">chemikálie</xsl:when>
			<xsl:when test="$option = 'chemistry, analytical'">chemie, analytická</xsl:when>
			<xsl:when test="$option = 'chemistry, applied'">chemie, aplikovaná</xsl:when>
			<xsl:when test="$option = 'chemistry'">chemie</xsl:when>
			<xsl:when test="$option = 'chemistry, inorganic &amp; nuclear'">chemie, anorganická a nukleární</xsl:when>
			<xsl:when test="$option = 'chemistry, multidisciplinary'">chemie, multidisciplinární</xsl:when>
			<xsl:when test="$option = 'chemistry, organic'">chemie, organická</xsl:when>
			<xsl:when test="$option = 'chemistry, physical'">chemie, fyzikální</xsl:when>
			<xsl:when test="$option = 'chemotherapy'">chemoterapie</xsl:when>
			<xsl:when test="$option = 'children'">děti</xsl:when>
			<xsl:when test="$option = 'civil engineering'">stavebnictví</xsl:when>
			<xsl:when test="$option = 'clergy'">duchovenstvo</xsl:when>
			<xsl:when test="$option = 'clinical neurology'">klinická neurologie</xsl:when>
			<xsl:when test="$option = 'colleges &amp; universities'">vysoké školy a univerzity</xsl:when>
			<xsl:when test="$option = 'communication and the arts'">komunikace a umění</xsl:when>
			<xsl:when test="$option = 'communication'">komunikace</xsl:when>
			<xsl:when test="$option = 'computed tomography'">počítačová tomografie</xsl:when>
			<xsl:when test="$option = 'computer programs'">počítačové programy</xsl:when>
			<xsl:when test="$option = 'computer science, artificial intelligence'">počítačové vědy, umělá inteligence</xsl:when>
			<xsl:when test="$option = 'computer science, hardware &amp; architecture'">počítačové vědy, hardware a architektura</xsl:when>
			<xsl:when test="$option = 'computer science, information systems'">počítačové vědy, informační systémy</xsl:when>
			<xsl:when test="$option = 'computer science, interdisciplinary applications'">počítačové vědy, mezioborové aplikace</xsl:when>
			<xsl:when test="$option = 'computer science'">počítačové vědy</xsl:when>
			<xsl:when test="$option = 'computer science, software engineering'">počítačové vědy, softwarové inženýrství</xsl:when>
			<xsl:when test="$option = 'computer science, theory &amp; methods'">počítačové vědy, teorie a metody</xsl:when>
			<xsl:when test="$option = 'computer simulation'">počítačová simulace</xsl:when>
			<xsl:when test="$option = 'computer software industry'">softwarový průmysl</xsl:when>
			<xsl:when test="$option = 'computers'">počítače</xsl:when>
			<xsl:when test="$option = 'condensed matter'">kondenzované látky</xsl:when>
			<xsl:when test="$option = 'construction &amp; building technology'">konstrukce a stavební technologie</xsl:when>
			<xsl:when test="$option = 'control systems'">řídící systémy</xsl:when>
			<xsl:when test="$option = 'copyrights'">autorská práva</xsl:when>
			<xsl:when test="$option = 'cosmology'">kosmologie</xsl:when>
			<xsl:when test="$option = 'crystallography'">krystalografie</xsl:when>
			<xsl:when test="$option = 'curricula'">učební osnovy</xsl:when>
			<xsl:when test="$option = 'data processing'">zpracování dat</xsl:when>
			<xsl:when test="$option = 'deformation'">deformace</xsl:when>
			<xsl:when test="$option = 'depression'">deprese</xsl:when>
			<xsl:when test="$option = 'design engineering'">konstruktérské práce</xsl:when>
			<xsl:when test="$option = 'diagnosis'">diagnóza</xsl:when>
			<xsl:when test="$option = 'diagnostic radiology'">diagnostická radiologie</xsl:when>
			<xsl:when test="$option = 'diet'">strava</xsl:when>
			<xsl:when test="$option = 'dynamics'">dynamika</xsl:when>
			<xsl:when test="$option = 'earth sciences'">vědy o Zemi</xsl:when>
			<xsl:when test="$option = 'ecology'">ekologie</xsl:when>
			<xsl:when test="$option = 'economic conditions'">ekonomické podmínky</xsl:when>
			<xsl:when test="$option = 'economics'">ekonomika</xsl:when>
			<xsl:when test="$option = 'education &amp; educational research'">vzdělávání a vzdělávací výzkum</xsl:when>
			<xsl:when test="$option = 'education'">vzdělávání</xsl:when>
			<xsl:when test="$option = 'electrical engineering'">elektrotechnika</xsl:when>
			<xsl:when test="$option = 'electronics'">elektronika</xsl:when>
			<xsl:when test="$option = 'emerging technologies'">vznikající technologie</xsl:when>
			<xsl:when test="$option = 'emission'">emise</xsl:when>
			<xsl:when test="$option = 'endocrinology &amp; metabolism'">endokrinologie a metabolismus</xsl:when>
			<xsl:when test="$option = 'engineering, biomedical'">inženýrství, biomedicínské</xsl:when>
			<xsl:when test="$option = 'engineering, chemical'">inženýrství, chemické</xsl:when>
			<xsl:when test="$option = 'engineering, civil'">stavebnictví</xsl:when>
			<xsl:when test="$option = 'engineering, electrical &amp; electronic'">inženýrství, elektrotechnika a elektronika</xsl:when>
			<xsl:when test="$option = 'engineering'">inženýrství</xsl:when>
			<xsl:when test="$option = 'engineering, manufacturing'">inženýrství, výroba</xsl:when>
			<xsl:when test="$option = 'engineering, mechanical'">strojírenství</xsl:when>
			<xsl:when test="$option = 'environmental sciences'">environmentální vědy</xsl:when>
			<xsl:when test="$option = 'epidemiology'">epidemiologie</xsl:when>
			<xsl:when test="$option = 'ethics'">etika</xsl:when>
			<xsl:when test="$option = 'europe'">Evropa</xsl:when>
			<xsl:when test="$option = 'evolution'">evoluce</xsl:when>
			<xsl:when test="$option = 'expression'">výraz</xsl:when>
			<xsl:when test="$option = 'female'">samice</xsl:when>
			<xsl:when test="$option = 'financial institutions'">finanční instituce</xsl:when>
			<xsl:when test="$option = 'financial services'">finanční služby</xsl:when>
			<xsl:when test="$option = 'financial services'">finanční služby</xsl:when>
			<xsl:when test="$option = 'finite element method'">metoda konečných prvků</xsl:when>
			<xsl:when test="$option = 'flow'">tok</xsl:when>
			<xsl:when test="$option = 'fluid flow'">proudění tekutin</xsl:when>
			<xsl:when test="$option = 'food'">jídlo</xsl:when>
			<xsl:when test="$option = 'food science &amp; technology'">potravinová věda a technologie</xsl:when>
			<xsl:when test="$option = 'foreign exchange markets'">devizové trhy</xsl:when>
			<xsl:when test="$option = 'foreign exchange rates'">měnové kurzy</xsl:when>
			<xsl:when test="$option = 'fracture mechanics'">lomová mechanika</xsl:when>
			<xsl:when test="$option = 'gait'">chůze</xsl:when>
			<xsl:when test="$option = 'galaxies'">galaxie</xsl:when>
			<xsl:when test="$option = 'gastroenterology &amp; hepatology'">gastroenterologie a hepatologie</xsl:when>
			<xsl:when test="$option = 'gene expression'">exprese genů</xsl:when>
			<xsl:when test="$option = 'gene'">gen</xsl:when>
			<xsl:when test="$option = 'genes'">geny</xsl:when>
			<xsl:when test="$option = 'genetic aspects'">genetické aspekty</xsl:when>
			<xsl:when test="$option = 'genetics &amp; heredity'">genetika a dědičnost</xsl:when>
			<xsl:when test="$option = 'genetics'">genetika</xsl:when>
			<xsl:when test="$option = 'geochemistry &amp; geophysics'">geochemie a geofyzika</xsl:when>
			<xsl:when test="$option = 'geosciences, multidisciplinary'">geovědy, multidisciplinární</xsl:when>
			<xsl:when test="$option = 'germany'">Německo</xsl:when>
			<xsl:when test="$option = 'growth'">růst</xsl:when>
			<xsl:when test="$option = 'health and environmental sciences'">zdraví a vědy o životním prostředí</xsl:when>
			<xsl:when test="$option = 'health aspects'">zdravotní aspekty</xsl:when>
			<xsl:when test="$option = 'health care sciences &amp; services'">vědy a služby zdravotnictví</xsl:when>
			<xsl:when test="$option = 'health informatics'">zdravotnická informatika</xsl:when>
			<xsl:when test="$option = 'health'">zdraví</xsl:when>
			<xsl:when test="$option = 'hematology'">hematologie</xsl:when>
			<xsl:when test="$option = 'high energy physics'">fyzika vysokých energií</xsl:when>
			<xsl:when test="$option = 'higher education'">vyšší vzdělávání</xsl:when>
			<xsl:when test="$option = 'history &amp; philosophy of science'">historie a filozofie vědy</xsl:when>
			<xsl:when test="$option = 'history'">historie</xsl:when>
			<xsl:when test="$option = 'hospitals'">nemocnice</xsl:when>
			<xsl:when test="$option = 'humans'">lidé</xsl:when>
			<xsl:when test="$option = 'identification'">identifikace</xsl:when>
			<xsl:when test="$option = 'imaging / radiology'">zobrazování / radiologie</xsl:when>
			<xsl:when test="$option = 'immunology'">imunologie</xsl:when>
			<xsl:when test="$option = 'infection'">infekce</xsl:when>
			<xsl:when test="$option = 'infectious diseases'">infekční nemoci</xsl:when>
			<xsl:when test="$option = 'information science &amp; library science'">informační věda a knihovnictví</xsl:when>
			<xsl:when test="$option = 'information technology'">informační technologie</xsl:when>
			<xsl:when test="$option = 'injuries'">zranění</xsl:when>
			<xsl:when test="$option = 'instruments &amp; instrumentation'">přístroje a instrumentace</xsl:when>
			<xsl:when test="$option = 'intellectual property'">duševní vlastnictví</xsl:when>
			<xsl:when test="$option = 'investing'">investování</xsl:when>
			<xsl:when test="$option = 'investment and finance'">investice a finance</xsl:when>
			<xsl:when test="$option = 'investment'">investice</xsl:when>
			<xsl:when test="$option = 'investments'">investice</xsl:when>
			<xsl:when test="$option = 'jews'">židé</xsl:when>
			<xsl:when test="$option = 'kinematics'">kinematika</xsl:when>
			<xsl:when test="$option = 'knee'">koleno</xsl:when>
			<xsl:when test="$option = 'kultura'">culture</xsl:when>
			<xsl:when test="$option = 'language, literature and linguistics'">jazyk, literatura a lingvistika</xsl:when>
			<xsl:when test="$option = 'law'">právo</xsl:when>
			<xsl:when test="$option = 'laws, regulations and rules'">zákony, předpisy a pravidla</xsl:when>
			<xsl:when test="$option = 'learning'">učení</xsl:when>
			<xsl:when test="$option = 'leaves'">listy</xsl:when>
			<xsl:when test="$option = 'life sciences'">biologické vědy</xsl:when>
			<xsl:when test="$option = 'life sciences'">vědy o životě</xsl:when>
			<xsl:when test="$option = 'literature'">literatura</xsl:when>
			<xsl:when test="$option = 'machine learning'">strojové učení</xsl:when>
			<xsl:when test="$option = 'magnetic fields'">magnetická pole</xsl:when>
			<xsl:when test="$option = 'male'">samec</xsl:when>
			<xsl:when test="$option = 'manipulators'">manipulátory</xsl:when>
			<xsl:when test="$option = 'marine &amp; freshwater biology'">mořská a sladkovodní biologie</xsl:when>
			<xsl:when test="$option = 'materials science, multidisciplinary'">vědy o materiálech, multidisciplinární</xsl:when>
			<xsl:when test="$option = 'materials science'">vědy o materiálech</xsl:when>
			<xsl:when test="$option = 'mathematical &amp; computational biology'">matematická a výpočetní biologie</xsl:when>
			<xsl:when test="$option = 'mathematical analysis'">matematická analýza</xsl:when>
			<xsl:when test="$option = 'mathematical models'">matematické modely</xsl:when>
			<xsl:when test="$option = 'mathematics, applied'">matematika, aplikovaná</xsl:when>
			<xsl:when test="$option = 'mathematics'">matematika</xsl:when>
			<xsl:when test="$option = 'mechanical engineering'">strojírenství</xsl:when>
			<xsl:when test="$option = 'mechanical properties'">mechanické vlastnosti</xsl:when>
			<xsl:when test="$option = 'mechanics'">mechanika</xsl:when>
			<xsl:when test="$option = 'medical informatics'">medicínská informatika</xsl:when>
			<xsl:when test="$option = 'medicine &amp; public health'">medicína a veřejné zdraví</xsl:when>
			<xsl:when test="$option = 'medicine, general &amp; internal'">lékařství, všeobecné a interní</xsl:when>
			<xsl:when test="$option = 'medicine, research &amp; experimental'">medicína, výzkumná a experimentální</xsl:when>
			<xsl:when test="$option = 'memory'">paměť</xsl:when>
			<xsl:when test="$option = 'meteorology &amp; atmospheric sciences'">meteorologie a vědy o atmosféře</xsl:when>
			<xsl:when test="$option = 'mice'">myši</xsl:when>
			<xsl:when test="$option = 'microbiology'">mikrobiologie</xsl:when>
			<xsl:when test="$option = 'middle aged'">středný věk</xsl:when>
			<xsl:when test="$option = 'models'">modely</xsl:when>
			<xsl:when test="$option = 'morphology'">morfologie</xsl:when>
			<xsl:when test="$option = 'mortality'">úmrtnost</xsl:when>
			<xsl:when test="$option = 'motion pictures'">filmy</xsl:when>
			<xsl:when test="$option = 'multidisciplinary sciences'">multidisciplinární vědy</xsl:when>
			<xsl:when test="$option = 'multimedia computer applications'">multimediální počítačové aplikace</xsl:when>
			<xsl:when test="$option = 'muscle, skeletal - physiology'">fyziologie svalová, kosterní</xsl:when>
			<xsl:when test="$option = 'muscles'">svaly</xsl:when>
			<xsl:when test="$option = 'musical recordings'">hudební nahrávky</xsl:when>
			<xsl:when test="$option = 'music'">hudba</xsl:when>
			<xsl:when test="$option = 'mutation'">mutace</xsl:when>
			<xsl:when test="$option = 'nanocomposites'">nanokompozity</xsl:when>
			<xsl:when test="$option = 'nanomaterials'">nanomateriály</xsl:when>
			<xsl:when test="$option = 'nanoparticle'">nanočástice</xsl:when>
			<xsl:when test="$option = 'nanoparticles'">nanočástice</xsl:when>
			<xsl:when test="$option = 'nanoscience &amp; nanotechnology'">nanověda a nanotechnologie</xsl:when>
			<xsl:when test="$option = 'nanostructure'">nanostruktura</xsl:when>
			<xsl:when test="$option = 'nanotechnology'">nanotechnologie</xsl:when>
			<xsl:when test="$option = 'nanotubes'">nanotrubičky</xsl:when>
			<xsl:when test="$option = 'networks'">sítě</xsl:when>
			<xsl:when test="$option = 'neurosciences'">neurovědy</xsl:when>
			<xsl:when test="$option = 'nonfiction'">věcná literatura</xsl:when>
			<xsl:when test="$option = 'northern america'">Severní Amerika</xsl:when>
			<xsl:when test="$option = 'novels'">romány</xsl:when>
			<xsl:when test="$option = 'nuclear science &amp; technology'">jaderná věda a technologie</xsl:when>
			<xsl:when test="$option = 'nurses'">ošetřovatelé</xsl:when>
			<xsl:when test="$option = 'nursing'">ošetřovatelství</xsl:when>
			<xsl:when test="$option = 'nutrition &amp; dietetics'">výživa a dietetika</xsl:when>
			<xsl:when test="$option = 'nutrition'">výživa</xsl:when>
			<xsl:when test="$option = 'oceanography'">oceánografie</xsl:when>
			<xsl:when test="$option = 'oncology'">onkologie</xsl:when>
			<xsl:when test="$option = 'optics'">optika</xsl:when>
			<xsl:when test="$option = 'optimization'">optimalizace</xsl:when>
			<xsl:when test="$option = 'orthopedics'">ortopedie</xsl:when>
			<xsl:when test="$option = 'particle'">částice</xsl:when>
			<xsl:when test="$option = 'particles'">částice</xsl:when>
			<xsl:when test="$option = 'pathology'">patologie</xsl:when>
			<xsl:when test="$option = 'pediatrics'">pediatrie</xsl:when>
			<xsl:when test="$option = 'peripheral vascular disease'">onemocnění periferních cév</xsl:when>
			<xsl:when test="$option = 'pharmacology &amp; pharmacy'">farmakologie a farmacie</xsl:when>
			<xsl:when test="$option = 'phenomenology'">fenomenologie</xsl:when>
			<xsl:when test="$option = 'philosophy'">filozofie</xsl:when>
			<xsl:when test="$option = 'philosophy, religion and theology'">filozofie, náboženství a teologie</xsl:when>
			<xsl:when test="$option = 'physics, applied'">fyzika, aplikovaná</xsl:when>
			<xsl:when test="$option = 'physics, atomic, molecular &amp; chemical'">fyzika, atomová, molekulární a chemická</xsl:when>
			<xsl:when test="$option = 'physics, atomic, molecular &amp; chemical'">fyzika, atomová, molekulární a chemická</xsl:when>
			<xsl:when test="$option = 'physics, condensed matter'">fyzika, kondenzované látky</xsl:when>
			<xsl:when test="$option = 'physics, fluids &amp; plasmas'">fyzika, tekutiny a plazma</xsl:when>
			<xsl:when test="$option = 'physics'">fyzika</xsl:when>
			<xsl:when test="$option = 'physics, mathematical'">fyzika, matematická</xsl:when>
			<xsl:when test="$option = 'physics, multidisciplinary'">fyzika, multidisciplinární</xsl:when>
			<xsl:when test="$option = 'physics, nuclear'">fyzika, jaderná</xsl:when>
			<xsl:when test="$option = 'physics of polymers'">fyzika polymerů</xsl:when>
			<xsl:when test="$option = 'physics, particles &amp; fields'">fyzika, částice a pole</xsl:when>
			<xsl:when test="$option = 'physiology'">fyziologie</xsl:when>
			<xsl:when test="$option = 'plant sciences'">vědy o rostlinách</xsl:when>
			<xsl:when test="$option = 'plants'">rostliny</xsl:when>
			<xsl:when test="$option = 'poetry'">poezie</xsl:when>
			<xsl:when test="$option = 'political science'">politologie</xsl:when>
			<xsl:when test="$option = 'politics'">politika</xsl:when>
			<xsl:when test="$option = 'polymerization'">polymerizace</xsl:when>
			<xsl:when test="$option = 'polymer'">polymer</xsl:when>
			<xsl:when test="$option = 'polymer sciences'">vědy o polymerech</xsl:when>
			<xsl:when test="$option = 'polymer science'">věda o polymerech</xsl:when>
			<xsl:when test="$option = 'polymers'">polymery</xsl:when>
			<xsl:when test="$option = 'product introduction'">zavedení výrobku</xsl:when>
			<xsl:when test="$option = 'proteins'">bílkoviny</xsl:when>
			<xsl:when test="$option = 'psychiatry'">psychiatrie</xsl:when>
			<xsl:when test="$option = 'psychology, clinical'">psychologie, klinická</xsl:when>
			<xsl:when test="$option = 'psychology, developmental'">psychologie, vývojová</xsl:when>
			<xsl:when test="$option = 'psychology, experimental'">psychologie, experimentální</xsl:when>
			<xsl:when test="$option = 'psychology, multidisciplinary'">psychologie, multidisciplinární</xsl:when>
			<xsl:when test="$option = 'psychology'">psychologie</xsl:when>
			<xsl:when test="$option = 'psychotherapy'">psychoterapie</xsl:when>
			<xsl:when test="$option = 'public, environmental &amp; occupational health'">zdraví veřejné, environmentální a pracovní</xsl:when>
			<xsl:when test="$option = 'public health'">veřejné zdraví</xsl:when>
			<xsl:when test="$option = 'pure sciences'">čisté vědy</xsl:when>
			<xsl:when test="$option = 'radiation'">záření</xsl:when>
			<xsl:when test="$option = 'radiology &amp; nuclear medicine'">radiologie a nukleární medicína</xsl:when>
			<xsl:when test="$option = 'radiology, nuclear medicine &amp; medical imaging'">radiologie, nukleární medicína a lékařské zobrazování</xsl:when>
			<xsl:when test="$option = 'radiology'">radiologie</xsl:when>
			<xsl:when test="$option = 'radiotherapy'">radioterapie</xsl:when>
			<xsl:when test="$option = 'rats'">krysy</xsl:when>
			<xsl:when test="$option = 'region:asia'">Oblast: Ázie</xsl:when>
			<xsl:when test="$option = 'rehabilitation'">rehabilitatace</xsl:when>
			<xsl:when test="$option = 'religion'">náboženství</xsl:when>
			<xsl:when test="$option = 'religious history'">náboženská historie</xsl:when>
			<xsl:when test="$option = 'replication'">replikace</xsl:when>
			<xsl:when test="$option = 'reports'">zprávy</xsl:when>
			<xsl:when test="$option = 'research'">výzkum</xsl:when>
			<xsl:when test="$option = 'rheology'">reologie</xsl:when>
			<xsl:when test="$option = 'risk factors'">rizikové faktory</xsl:when>
			<xsl:when test="$option = 'risk'">riziko</xsl:when>
			<xsl:when test="$option = 'robotics industry'">průmysl robotiky</xsl:when>
			<xsl:when test="$option = 'robotics'">robotika</xsl:when>
			<xsl:when test="$option = 'robots'">roboty</xsl:when>
			<xsl:when test="$option = 'school administration'">správa škol</xsl:when>
			<xsl:when test="$option = 'securities markets'">trhy cenných papírů</xsl:when>
			<xsl:when test="$option = 'sensors'">senzory</xsl:when>
			<xsl:when test="$option = 'simulation'">simulace</xsl:when>
			<xsl:when test="$option = 'social psychology'">sociální psychologie</xsl:when>
			<xsl:when test="$option = 'social sciences'">společenské vědy</xsl:when>
			<xsl:when test="$option = 'sociology'">sociologie</xsl:when>
			<xsl:when test="$option = 'soil'">půda</xsl:when>
			<xsl:when test="$option = 'spectra'">spektra</xsl:when>
			<xsl:when test="$option = 'spectroscopy'">spektroskopie</xsl:when>
			<xsl:when test="$option = 'spectroscopy'">spektroskopie</xsl:when>
			<xsl:when test="$option = 'spirituality'">duchovno</xsl:when>
			<xsl:when test="$option = 'sport sciences'">sportovní vědy</xsl:when>
			<xsl:when test="$option = 'stars &amp; galaxies'">hvězdy a galaxie</xsl:when>
			<xsl:when test="$option = 'stars'">hvězdy</xsl:when>
			<xsl:when test="$option = 'statistical data'">statistické údaje</xsl:when>
			<xsl:when test="$option = 'statistical mechanics'">statistická mechanika</xsl:when>
			<xsl:when test="$option = 'statistics &amp; probability'">statistika a pravděpodobnost</xsl:when>
			<xsl:when test="$option = 'stellar investigations'">vyšetřování hvězd</xsl:when>
			<xsl:when test="$option = 'students'">studenti</xsl:when>
			<xsl:when test="$option = 'studies'">studie</xsl:when>
			<xsl:when test="$option = 'study and teaching'">studium a vyučování</xsl:when>
			<xsl:when test="$option = 'surgery'">chirurgie</xsl:when>
			<xsl:when test="$option = 'survival'">přežití</xsl:when>
			<xsl:when test="$option = 'suspensions'">směsi</xsl:when>
			<xsl:when test="$option = 'teacher education'">vzdělávání učitelů</xsl:when>
			<xsl:when test="$option = 'teachers'">učitelé</xsl:when>
			<xsl:when test="$option = 'teaching methods'">vyučovací metody</xsl:when>
			<xsl:when test="$option = 'teaching'">vyučování</xsl:when>
			<xsl:when test="$option = 'technology'">technologie</xsl:when>
			<xsl:when test="$option = 'telecommunications'">telekomunikace</xsl:when>
			<xsl:when test="$option = 'theater'">divadlo</xsl:when>
			<xsl:when test="$option = 'theology'">teologie</xsl:when>
			<xsl:when test="$option = 'therapy'">terapie</xsl:when>
			<xsl:when test="$option = 'thermodynamics'">termodynamika</xsl:when>
			<xsl:when test="$option = 'training'">trénink</xsl:when>
			<xsl:when test="$option = 'trends'">trendy</xsl:when>
			<xsl:when test="$option = 'tumors'">nádory</xsl:when>
			<xsl:when test="$option = 'ultrasound'">ultrazvuk</xsl:when>
			<xsl:when test="$option = 'united kingdom'">Spojené království</xsl:when>
			<xsl:when test="$option = 'united states'">Spojené státy</xsl:when>
			<xsl:when test="$option = 'universe'">vesmír</xsl:when>
			<xsl:when test="$option = 'universities and colleges'">univerzity a vysoké školy</xsl:when>
			<xsl:when test="$option = 'urology &amp; nephrology'">urologie a nefrologie</xsl:when>
			<xsl:when test="$option = 'u.s'">Spojené státy</xsl:when>
			<xsl:when test="$option = 'vaccines'">vakcíny</xsl:when>
			<xsl:when test="$option = 'virology'">virologie</xsl:when>
			<xsl:when test="$option = 'viruses'">viry</xsl:when>
			<xsl:when test="$option = 'viscoelasticity'">viskoelasticita</xsl:when>
			<xsl:when test="$option = 'viscosity'">viskozita</xsl:when>
			<xsl:when test="$option = 'walking'">chůze</xsl:when>
			<xsl:when test="$option = 'women'">ženy</xsl:when>
			<xsl:when test="$option = ''"></xsl:when>
<!--
			<xsl:when test="$option = 'people's republic of china'">Čínská lidová republika</xsl:when>
-->
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_facet_discipline">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'agriculture'">zemědělství</xsl:when>
			<xsl:when test="$option = 'anatomy &amp; physiology'">anatomie a fyziologie</xsl:when>
			<xsl:when test="$option = 'anthropology'">antropologie</xsl:when>
			<xsl:when test="$option = 'applied sciences'">aplikované vědy</xsl:when>
			<xsl:when test="$option = 'architecture'">architektura</xsl:when>
			<xsl:when test="$option = 'astronomy &amp; astrophysics'">astronomie a astrofyzika</xsl:when>
			<xsl:when test="$option = 'biology'">biologie</xsl:when>
			<xsl:when test="$option = 'botany'">botanika</xsl:when>
			<xsl:when test="$option = 'business'">obchod</xsl:when>
			<xsl:when test="$option = 'chemistry'">chemie</xsl:when>
			<xsl:when test="$option = 'computer science'">výpočetní technika</xsl:when>
			<xsl:when test="$option = 'dance'">tanec</xsl:when>
			<xsl:when test="$option = 'dentistry'">zubní lékařství</xsl:when>
			<xsl:when test="$option = 'diet &amp; clinical nutrition'">strava a klinická výživa</xsl:when>
			<xsl:when test="$option = 'drama'">drama</xsl:when>
			<xsl:when test="$option = 'ecology'">ekologie</xsl:when>
			<xsl:when test="$option = 'economics'">ekonomika</xsl:when>
			<xsl:when test="$option = 'education'">vzdělávání</xsl:when>
			<xsl:when test="$option = 'engineering'">strojírenství</xsl:when>
			<xsl:when test="$option = 'environmental sciences'">vědy o životním prostředí</xsl:when>
			<xsl:when test="$option = 'film'">film</xsl:when>
			<xsl:when test="$option = 'forestry'">lesnictví</xsl:when>
			<xsl:when test="$option = 'geography'">zeměpis</xsl:when>
			<xsl:when test="$option = 'geology'">geologie</xsl:when>
			<xsl:when test="$option = 'government'">vláda</xsl:when>
			<xsl:when test="$option = 'history &amp; archaeology'">historie a archeologie</xsl:when>
			<xsl:when test="$option = 'international relations'">mezinárodní vztahy</xsl:when>
			<xsl:when test="$option = 'journalism &amp; communications'">žurnalistika a komunikace</xsl:when>
			<xsl:when test="$option = 'languages &amp; literatures'">jazyky a literatura</xsl:when>
			<xsl:when test="$option = 'law'">právo</xsl:when>
			<xsl:when test="$option = 'library &amp; information science'">knihovnictví a informační věda</xsl:when>
			<xsl:when test="$option = 'mathematics'">matematika</xsl:when>
			<xsl:when test="$option = 'medicine'">medicína</xsl:when>
			<xsl:when test="$option = 'meteorology &amp; climatology'">meteorologie a klimatologie</xsl:when>
			<xsl:when test="$option = 'military &amp; naval science'">vojenské a námořní vědy</xsl:when>
			<xsl:when test="$option = 'music'">hudba</xsl:when>
			<xsl:when test="$option = 'nursing'">ošetřovatelství</xsl:when>
			<xsl:when test="$option = 'occupational therapy &amp; rehabilitation'">pracovní terapie a rehabilitace</xsl:when>
			<xsl:when test="$option = 'oceanography'">oceánografie</xsl:when>
			<xsl:when test="$option = 'parapsychology &amp; occult sciences'">parapsychologie a okultní vědy</xsl:when>
			<xsl:when test="$option = 'pharmacy, therapeutics, &amp; pharmacology'">lékářství, léčiva a farmakologie</xsl:when>
			<xsl:when test="$option = 'philosophy'">filozofie</xsl:when>
			<xsl:when test="$option = 'physical therapy'">fyzioterapie</xsl:when>
			<xsl:when test="$option = 'physics'">fyzika</xsl:when>
			<xsl:when test="$option = 'political science'">politické vědy</xsl:when>
			<xsl:when test="$option = 'psychology'">psychologie</xsl:when>
			<xsl:when test="$option = 'public health'">veřejné zdraví</xsl:when>
			<xsl:when test="$option = 'recreation &amp; sports'">rekreace a sport</xsl:when>
			<xsl:when test="$option = 'religion'">náboženství</xsl:when>
			<xsl:when test="$option = 'sciences'">vědy</xsl:when>
			<xsl:when test="$option = 'social sciences'">společenské vědy</xsl:when>
			<xsl:when test="$option = 'social welfare &amp; social work'">sociální péče a sociální práce</xsl:when>
			<xsl:when test="$option = 'sociology &amp; social history'">sociologie a sociální dějiny</xsl:when>
			<xsl:when test="$option = 'statistics'">statistika</xsl:when>
			<xsl:when test="$option = 'veterinary medicine'">veterinární lékařství</xsl:when>
			<xsl:when test="$option = 'visual arts'">výtvarné umění</xsl:when>
<!--
			<xsl:when test="$option = women's studies'">studie o ženách</xsl:when>
-->
			<xsl:when test="$option = 'zoology'">zoologie</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_search_fields">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'keyword'">libovolné pole</xsl:when>
			<xsl:when test="$option = 'title'">název</xsl:when>
			<xsl:when test="$option = 'author'">autor</xsl:when>
			<xsl:when test="$option = 'subject'">předmět</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="text_folder_export_options_list">
		<xsl:param name="option" />
		<xsl:choose>
			<xsl:when test="$option = 'email'">Poslat záznamy na váš email</xsl:when>
			<xsl:when test="$option = 'refworks'">Export do Refworks</xsl:when>
			<xsl:when test="$option = 'endnoteweb'">Export do Endnote Web</xsl:when>
			<xsl:when test="$option = 'blackboard'">Export do Blackboard</xsl:when>
			<xsl:when test="$option = 'endnote'">Stáhnout do Endnote, Zotero atd.</xsl:when>
			<xsl:when test="$option = 'text'">Stáhnout jako textový soubor</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$option" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:variable name="text_results_clear_facets_false"> Zachovat upřesnění hledání</xsl:variable>
	<xsl:variable name="text_results_clear_facets_true"> Nové hledání</xsl:variable>
	
	<xsl:variable name="text_citation_basic_title">Název: </xsl:variable>
	<xsl:variable name="text_citation_basic_format">Formát: </xsl:variable>
	<xsl:variable name="text_citation_basic_author">Autor: </xsl:variable>
	<xsl:variable name="text_citation_basic_citation">Původní citace: </xsl:variable>
	<xsl:variable name="text_citation_basic_journal-title">Název časopisu: </xsl:variable>
	<xsl:variable name="text_citation_basic_volume">Svazek: </xsl:variable>
	<xsl:variable name="text_citation_basic_issue">Číslo: </xsl:variable>
	<xsl:variable name="text_citation_basic_spage">První stránka: </xsl:variable>
	<xsl:variable name="text_citation_basic_epage">Poslední stránka: </xsl:variable>
	<xsl:variable name="text_citation_basic_place">Místo: </xsl:variable>
	<xsl:variable name="text_citation_basic_publisher">Vydavatel: </xsl:variable>
	<xsl:variable name="text_citation_basic_year">Rok: </xsl:variable>
	<xsl:variable name="text_citation_basic_summary">Shrnutí: </xsl:variable>
	<xsl:variable name="text_citation_basic_subjects">Klíčová slova: </xsl:variable>
	<xsl:variable name="text_citation_basic_language">Jazyk: </xsl:variable>
	<xsl:variable name="text_citation_basic_notes">Poznámky: </xsl:variable>
	<xsl:variable name="text_citation_basic_items">Jednotky: </xsl:variable>
	<xsl:variable name="text_citation_basic_link">Odkaz: </xsl:variable>
	
	<xsl:variable name="text_summon_recommendation">
		<xsl:text>Nalezli jsme </xsl:text>
		<xsl:choose>
		<xsl:when test="count(results/database_recommendations/database_recommendation) &gt; 1">
			několik specializovaných databází, které by vám mohli pomoct.
		</xsl:when>
		<xsl:otherwise>
			specializovanou databázi, která by vám mohla pomoct.
		</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="text_summon_facets_refine">Upřesnit vaše hledání</xsl:variable>
	<xsl:variable name="text_summon_facets_all">Všechny výsledky</xsl:variable>
	<xsl:variable name="text_summon_facets_scholarly">Pouze akademické</xsl:variable>
	<xsl:variable name="text_summon_facets_refereed">Pouze recenzované</xsl:variable>
	<xsl:variable name="text_summon_facets_fulltext">Pouze s plným textem online</xsl:variable>
	<xsl:variable name="text_summon_facets_newspaper-add">Přidat novinové články</xsl:variable>
	<xsl:variable name="text_summon_facets_newspaper-exclude">Vyjmout novinové články</xsl:variable>
	<xsl:variable name="text_summon_facets_beyond-holdings">Vyhledávat i mimo vaši knihovnu</xsl:variable>
	
	<xsl:variable name="text_folder_output_results_title">Název</xsl:variable>
	<xsl:variable name="text_folder_output_results_author">Autor</xsl:variable>
	<xsl:variable name="text_folder_output_results_format">Formát</xsl:variable>
	<xsl:variable name="text_folder_output_results_year">Rok</xsl:variable>
	<xsl:variable name="text_folder_tags_add">Přidat záznamům štítek: </xsl:variable>
	<xsl:variable name="text_folder_export_options">Možnosti exportu: </xsl:variable>
	<xsl:variable name="text_folder_export_add">Přidat</xsl:variable>
	<xsl:variable name="text_folder_export_delete">Smazat</xsl:variable>
	<xsl:variable name="text_folder_export_delete_confirm">Smazat tyto záznamy?</xsl:variable>
	<xsl:variable name="text_folder_export_deleted">Záznamy smazány</xsl:variable>
	<xsl:variable name="text_folder_export_email_cancel">Zrušit</xsl:variable>
	<xsl:variable name="text_folder_export_email_error">Litujeme, email se momentálně nepodařilo odeslat</xsl:variable>
	<xsl:variable name="text_folder_export_email_options">Možnosti emailu</xsl:variable>
	<xsl:variable name="text_folder_export_email_send">Odeslat</xsl:variable>
	<xsl:variable name="text_folder_export_email_sent">Email byl úspěšně odeslán</xsl:variable>
	<xsl:variable name="text_folder_export_error_missing_label">Prosím, zadejte štítek.</xsl:variable>
	<xsl:variable name="text_folder_export_error_select_records">Prosím, vyberte záznamy.</xsl:variable>
	<xsl:variable name="text_folder_export_updated">Záznamy byli aktualizovány.</xsl:variable>
	
	<xsl:variable name="text_folder_record_added">Záznam byl úspěšně přidán mezi uložené záznamy</xsl:variable>
	<xsl:variable name="text_folder_record_removed">Záznam byl úspěšně odstraněn z uložených záznamů</xsl:variable>
	<xsl:variable name="text_folder_tags_limit">Omezeno na:</xsl:variable>
	<xsl:variable name="text_folder_tags_remove">Odstranit štítek ze záznamu</xsl:variable>
	<xsl:variable name="text_folder_return_to_results">výsledky vyhledávání</xsl:variable>
	
	<xsl:variable name="text_search_loading">Načítavají se výsledky . . .</xsl:variable>
	
	<xsl:variable name="text_facets_include">Včetně</xsl:variable>
	<xsl:variable name="text_facets_exclude">Mimo</xsl:variable>
	<xsl:variable name="text_facets_submit">Odeslat</xsl:variable>
	<xsl:variable name="text_facets_multiple_any">vše</xsl:variable>
	<xsl:variable name="text_facets_from">Od:</xsl:variable>
	<xsl:variable name="text_facets_to">Do:</xsl:variable>
	<xsl:variable name="text_facets_update">Aktualizovat</xsl:variable>
	
	<xsl:variable name="text_fulltext_text_in_record">Text v záznamu</xsl:variable>
	<xsl:variable name="text_uniform_title">Jednotný název:</xsl:variable>
	
	<xsl:variable name="text_record_standard_numbers_issn">ISSN</xsl:variable>
	<xsl:variable name="text_record_standard_numbers_isbn">ISBN</xsl:variable>
	<xsl:variable name="text_record_standard_numbers_gpo">Záznam GPO</xsl:variable>
	<xsl:variable name="text_record_standard_numbers_gov_doc">Vládní dok.</xsl:variable>
	<xsl:variable name="text_record_standard_numbers_oclc">OCLC</xsl:variable>
	<xsl:variable name="text_record_alternate_titles">Alternativní názvy</xsl:variable>
	<xsl:variable name="text_record_additional_titles">Další titles</xsl:variable>
	<xsl:variable name="text_record_description">Popis</xsl:variable>
	<xsl:variable name="text_record_journal_continues">Pokračuje</xsl:variable>
	<xsl:variable name="text_record_journal_continued_by">Pokračuje jako</xsl:variable>
	<xsl:variable name="text_record_series">Seriál</xsl:variable>
	
	<xsl:variable name="text_combined_record_author">Autor: </xsl:variable>
	<xsl:variable name="text_combined_record_published">Publikováno: </xsl:variable>
	<xsl:variable name="text_combined_record_no_matches">Nebyli nalezeny žádné výsledky.</xsl:variable>
	
	<xsl:variable name="text_ebsco_facets_heading">Akademické časopisy</xsl:variable>
	<xsl:variable name="text_ebsco_facets_all">všechny časopisy</xsl:variable>
	<xsl:variable name="text_ebsco_facets_scholarly">pouze akademické</xsl:variable>
	
	<xsl:variable name="text_search_books_no_copies_available">Žádné kopie nejsou k dispozici</xsl:variable>
	<xsl:template name="text_search_books_copies_available">
		<xsl:param name="num" />
		<xsl:choose>
			<xsl:when test="$num = '1'">1 kopie k dispozici</xsl:when>
			<xsl:when test="($num &gt; '1') and ($num &lt; '5')"><xsl:value-of select="$num" /> kopie k dispozici</xsl:when>
			<xsl:when test="$num &gt; '5'"><xsl:value-of select="$num" /> kopií k dispozici</xsl:when>	
		</xsl:choose>
	</xsl:template>
	<xsl:variable name="text_search_books_online">Online</xsl:variable>
	<xsl:variable name="text_search_books_printed">Tištěné kopie</xsl:variable>
	<xsl:variable name="text_search_books_bound">Vázané svazky</xsl:variable>
	<xsl:variable name="text_search_books_database">Databáze</xsl:variable>
	<xsl:variable name="text_search_books_coverage">Pokrytí</xsl:variable>
	<xsl:variable name="text_search_books_information">Informace</xsl:variable>
	<xsl:variable name="text_search_books_about">O zdroji</xsl:variable>
	<xsl:variable name="text_search_books_institution">Instituce</xsl:variable>
	<xsl:variable name="text_search_books_location">Umístění</xsl:variable>
	<xsl:variable name="text_search_books_callnumber">Signatura</xsl:variable>
	<xsl:variable name="text_search_books_status">Stav</xsl:variable>
	<xsl:variable name="text_search_books_request">Požadavek</xsl:variable>
	<xsl:variable name="text_search_books_sms_location">Odeslat umístění na váš telefon</xsl:variable>
	<xsl:variable name="text_search_books_sms_location_title">Odeslat název a umístění na váš telefon</xsl:variable>
	<xsl:variable name="text_search_books_sms_phone">Vaše telefonní číslo: </xsl:variable>
	<xsl:variable name="text_search_books_sms_provider">Operátor:</xsl:variable>
	<xsl:variable name="text_search_books_sms_choose">-- zvolte jednoho --</xsl:variable>
	<xsl:variable name="text_search_books_choose_copy">Vyberte si jednu z kopií</xsl:variable>
	<xsl:variable name="text_search_books_sms_smallprint">Služba může být zpoplatněna operátorem.</xsl:variable>
	<xsl:variable name="text_search_books_google_preview">Vyhledat další informace na Google Books</xsl:variable>
	
	<xsl:variable name="text_readinglist_breadcrumb">Zpět do čtenářského seznamu</xsl:variable>
	<xsl:variable name="text_readinglist_saved">Uloženo</xsl:variable>
	<xsl:variable name="text_readinglist_add">Přidat do čtenářského seznamu</xsl:variable>
	<xsl:variable name="text_readinglist_search">Hledat nové záznamy</xsl:variable>
	<xsl:variable name="text_readinglist_add_saved">Přidat předtím uložené záznamy</xsl:variable>
	
	<xsl:variable name="text_worldcat_institution">Instituce</xsl:variable>
	<xsl:variable name="text_worldcat_availability">Dostupnost</xsl:variable>
	<xsl:variable name="text_worldcat_check_availability">Zkontrolovat dostupnost</xsl:variable>
	
</xsl:stylesheet>
