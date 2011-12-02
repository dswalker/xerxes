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
 
$(document).ready(addAjaxToFacetMoreLinks);
$(document).ready(minimizeFacets);
$(document).ready(showHitCounts);
$(window).load(setNoImage);
$(document).ready(fillAvailability);
$(document).ready(addAjaxToSaveLinks);


function addAjaxToFacetMoreLinks()
{
	$(".facetMoreOption").click(function() {
		return showFacetMore(this.id);
	});

	$(".facetLessOption").click(function(){
		return showFacetLess(this.id);
	});
}

function minimizeFacets()
{	
	$('ul.facetListMore').hide();
	$('.facetOptionMore').show();
}

function showFacetMore(id)
{
	id = id.replace("facet-more-link-", "");
	
	$('#facet-more-' + id).hide();
	$('#facet-list-' + id).show();
	$('#facet-less-' + id).show();
	
	return false;
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
			if ( /tabsHitNumber/.test(links[i].className) )
			{
				hitID = links[i].id;
								
				arrElements = links[i].id.split("_");
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
		if ( /availabilityLoad/.test(divs[i].className) )
		{
			$(divs[i]).html("<img src=\"images/loading.gif\" alt=\"loading\" /> Checking availability . . .");
		
			var url = "";		// final url to send to server
			
			arrElements = divs[i].id.split("_");
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

function addAjaxToSaveLinks()
{	
	$(".saveRecord").click(function() {
		return updateRecord(this);
	});
}
	
function updateRecord( record )
{
	var id = record.id;
	var id_array = id.split(/_/); id_array.shift();
	var record_number = id_array.join("_");
	
	
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

			$('#saveRecordOption_' + record_number + ' .temporary_login_note').remove();
			
			$('#folder_' + record_number).attr('src',"images/folder.gif");
			$(record).html( xerxes_labels['text_results_record_save_it'] );
			$(record).removeClass("saved");
			
			// remove label input
			
			var label_input = $('#label_' + record_number);
			if (label_input) label_input.remove();
		}		
		else
		{
			numSavedRecords++;
			
			$('#folder_' + record_number).attr('src',"images/folder_on.gif");
			
			// different label depending on whether they are logged in or not. 
			// we tell if they are logged in or not, as well as find the login
			// url, based on looking for 'login' link in the dom.
			
			if ($('#login'))
			{
				var temporary_login_note = ' <span class="temporary_login_note"> ( <a  href="' + 
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
			
			// add tag input
			
			if ( ! isTemporarySession && savedID )
			{
				/*
				
				var input_div = $('template_tag_input').cloneNode(true);
				var new_form = input_div.down('form');
				
				// take the template for a tag input and set it up for this particular
				// record
				
				input_div.id = "label_" + source + ":" + record_number; 
				new_form.record.value = savedID;
				new_form.tagsShaddow.id = 'shadow-' + savedID; 
				new_form.tags.id = 'tags-' + savedID;
				
				new_form.tags.onfocus = function () {
					activateButton(this)
				}
				new_form.tags.onkeypress = function () {
					activateButton(this)
				}
				new_form.tags.onblur = function () {
					deactivateButton(this)
				}
				
				new_form.submitButton.id = 'submit-' + savedID;
				new_form.submitButton.disabled = true;
				new_form.onsubmit = function () {
					return updateTags(this);
				}
			
				// add it to the page, now that it's all set up.
				
				var parentBlock = $(id).up('.recordActions');
				
				if (parentBlock) 
				{
					parentBlock.insert(input_div);
					
					// and add the autocompleter
					
					addAutoCompleterToID(new_form.tags.id);
					input_div.show();
				}
				
				*/
			}
		}
		
		$(record).removeClass("disabled");
		
		// change master folder image
		
		if ( numSavedRecords > 0 ) {
			$('#folder').src = 'images/folder_on.gif';
		}
		else {
			$('#folder').src = 'images/folder.gif';
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