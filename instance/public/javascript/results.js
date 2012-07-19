/**
 * results page
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */
 
$(document).ready(addFacetMoreLinks);
$(document).ready(addFacetSelection);
$(document).ready(addFacetClear);
$(document).ready(minimizeFacets);
$(document).ready(showHitCounts);
$(window).load(setNoImage);
$(document).ready(fillAvailability);
$(document).ready(addSaveLinks);


function addFacetMoreLinks()
{
	$(".facet-more-option").click(function() {
		return showFacetMore(this.id);
	});

	$(".facet-less-option").click(function(){
		return showFacetLess(this.id);
	});
}

function minimizeFacets()
{	
	$('ul.facet-list-more').hide();
	$('.facet-option-more').show();
}

function showFacetMore(id)
{
	id = id.replace("facet-more-link-", "");
	
	$('#facet-more-' + id).hide();
	$('#facet-list-' + id).show();
	$('#facet-less-' + id).show();
	
	return false;
}

function addFacetSelection()
{	
	$('.facet-selection-option').click(function() {			
			
		group_array = this.id.split('-');
		group_array.pop();
		group_id =  group_array.join('-');
		
		$('#' + group_id).attr('checked', false);
		
		$('#form-' + group_id).submit();
		loadWaitMessage();
	});
}

function addFacetClear()
{
	$('.facet-selection-clear').click(function() {
		$('input[class~="' + this.id + '"]').attr('checked', false);

		$('#form-' + this.id).submit();
		loadWaitMessage();
	});	
}

function loadWaitMessage()
{	
	$('#fullscreen').show();
	$('#loading').show();
	
	$('#fullscreen').css('top', $(window).scrollTop() + "px");
	
	mid_height = $(window).scrollTop() + ($(window).height() / 2 ) - 100;
	mid_width = $(window).width() / 2;
	
	$('#loading').css('top', mid_height + "px");
	$('#loading').css('left', mid_width + "px");
}

function showFacetLess(id)
{	
	id = id.replace("facet-less-link-", "");
	
	$('#facet-more-' + id).show();
	$('#facet-list-' + id).hide();
	$('#facet-less-' + id).hide();
	
	return false;
}

function showHitCounts()
{
	if ( $('#query') )
	{		
		var query = $('#query').val();
		var field = $('#field').val();
		
		var links = document.getElementsByTagName('span');
		
		for ( i = 0; i < links.length; i++)
		{		
			if ( /tabs-hit-number/.test(links[i].className) )
			{
				hitID = links[i].id;
								
				arrElements = links[i].id.split("-");
				controller = arrElements[1];
				source = arrElements[2];
									
				var url = controller + "/hits?&query=" + query + "&field=" + field;
				
				if ( source != '' )
				{
					url += "&source=" +  source;
				}
				
				updateElement(url, links[i]);
			}
		}
	}
}

function fillAvailability()
{		
	var divs = document.getElementsByTagName('div');
	
	// fill each div with look-up information
	// will be either based on isbn or oclc number
	
	for ( i = 0; i < divs.length; i++ )
	{
		if ( /availability-load/.test(divs[i].className) )
		{
			$(divs[i]).html("<img src=\"images/loading.gif\" alt=\"loading\" /> Checking availability . . .");
		
			var url = "";		// final url to send to server
			
			arrElements = divs[i].id.split("-");
			controller = arrElements[0];
			id = arrElements[1];
			view = arrElements[2];
			
			url = controller + "/lookup?id=" + id + "&display=" + view;			
			updateElement(url, divs[i])
		}
	}
}

function setNoImage()
{
	var imgs = document.getElementsByTagName('img');
	
	for ( i = 0; i < imgs.length; i++ )
	{
		if ( /book-jacket-large/.test(imgs[i].className) )
		{			
			if ( imgs[i].width != 1 )
			{
				$(".bookRecordBookCover").show();
				$(".bookRecord").css('marginLeft', (imgs[i].width + 20) + 'px');
			}
		}
		else ( /book-jacket/.test(imgs[i].className) )
		{
			if ( imgs[i].width == 1 )
			{
				imgs[i].src = "images/no-image.gif";
			}
		}
	}
}

function addSaveLinks()
{	
	$(".save-record").click(function() {
		return updateRecord(this);
	});
}
	
function updateRecord( record )
{
	var id = record.id;
	var id_array = id.split(/-/); id_array.shift();
	var record_number = id_array.join("-");
	
	
	if ( $(record).hasClass("disabled")) {
		return false;
	}
	
	// should be set by main page in global js variable, if not we set.
	
	if (typeof(window["numSavedRecords"]) == "undefined") {
		numSavedRecords = 0;
	}
	
	if (typeof(window["isTemporarySession"]) == "undefined") {
		 isTemporarySession = true;
	}
	
	// do it! update our icons only after success please! then we're only
	// telling them they have saved a record if they really have! hooray
	// for javascript closures.

	var workingText = xerxes_labels['text_results_record_saving'];
	
	if ( $(record).hasClass("saved") ) {
		workingText = xerxes_labels['text_results_record_removing'];
	}

	// alert(xerxes_labels['text_results_record_save_err']);
	
	$(record).html(workingText);
	$(record).addClass("disabled");
	
	// get it
	
	var url = $(record).attr('href');
	url += "&format=json";
	
	$.getJSON(url, function(json) {
		
		var savedID = json.savedRecordID;

		if ( $(record).hasClass("saved") )
		{
			numSavedRecords--;

			$('#save-record-option-' + record_number + ' .temporary-login-note').remove();
			
			$('#folder-' + record_number).attr('src',"images/folder.gif");
			$(record).html( xerxes_labels['text_results_record_save_it'] );
			$(record).removeClass("saved");
			
			// remove label input
			
			var label_input = $('#label-' + record_number);
			if (label_input) label_input.remove();
		}		
		else
		{
			numSavedRecords++;
			
			$('#folder-' + record_number).attr('src',"images/folder_on.gif");
			
			// different label depending on whether they are logged in or not. 
			// we tell if they are logged in or not, as well as find the login
			// url, based on looking for 'login' link in the dom.
			
			if ($('#logout').length == 0 )
			{
				var temporary_login_note = ' <span class="temporary-login-note"> ( <a  href="' + 
					$('#login').attr('href') +'">' + xerxes_labels['text_results_record_saved_perm'] + 
					' </a> ) </span>';
			
				// Put the login link back please 
				
				$(record).html( xerxes_labels['text_results_record_saved_temp'] );
				$(record).after(temporary_login_note);
			}
			else
			{
				$(record).html( xerxes_labels['text_results_record_saved'] );
			}
			
			$(record).addClass("saved");
			
			if ( ! isTemporarySession && savedID )
			{
				// @todo: add tag input?
			}
		}
		
		$(record).removeClass("disabled");
		
		// change master folder image
		
		if ( numSavedRecords > 0 ) {
			$('#folder').attr('src','images/folder_on.gif');
		}
		else {
			$('#folder').attr('src','images/folder.gif');
		}

	});	
		
	return false;
}
	

function updateElement(url, element)
{
	$.get(url, function(data) {
		$(element).html(data);
	});
	//.error(function() { $(hitID).html("error") });
}