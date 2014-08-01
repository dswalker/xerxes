$(document).ready(highlight);
$(document).ready(resort);
$(document).ready(addDeleteConfirm);
$(document).ready(databaseFilter);
$(document).ready(databaseForm);

function addDeleteConfirm()
{
	$('.delete-fade').click(function()
	{		
		var target = $(this).attr('href');
		var data_source = $(this).attr('data-source');
		
		data_source = "#" + data_source;

		$.get( target, function( data ) {
			$(data_source).fadeOut(500, function() { $(data_source).remove(); });
		});			
		
		return false;
	});
	
	$('.delete-confirm').click(function()
	{		
  		var confirmed = confirm(xerxes_labels['text_databases_confirm_delete']);

		if ( confirmed )
		{
			return true;
		}
		
		return false;
	});
}

function highlight()
{
	$(".list-item").mouseover(function() {
			$(this).addClass("list-highlight");
			$(this).children(".list-item-action-menu").css('visibility', 'visible');
		});

	$(".list-item").mouseout(function() {
			$(this).removeClass("list-highlight");
			$(this).children(".list-item-action-menu").css('visibility', 'hidden');
		});
}

// drag and drop

function resort()
{	
	$(".subject-list ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
		
		var target = $(this).attr('data-target');
		var category = $(this).attr('data-category');
		var subcategory = $(this).attr('data-subcategory');
		var order = $(this).sortable("serialize") + "&cat=" + category + "&subcat=" + subcategory + "&noredirect=1";

		$.post(target, order, function(theResponse){
			
			// do something

		});														 
	}});
}

function databaseFilter() 
{
	$(".filter").keyup(function(){
 
		// Retrieve the input field text and reset the count to zero
		var filter = $(this).val(), count = 0;
 
		// Loop through the comment list
		$(".database-choice-list tr").each(function(){
 
			// If the list item does not contain the text phrase fade it out
			if ($(this).text().search(new RegExp(filter, "i")) < 0) {
				$(this).fadeOut();
 
			// Show the list item if the phrase matches and increase the count by 1
			} else {
				$(this).show();
				count++;
			}
		});
	});
}

function databaseForm()
{
	$.fn.editable.defaults.mode = 'inline';	
	$('.edit').editable();

	$( ".datepicker" ).datepicker();
	
	$('#form-keywords').tagit({
		'allowSpaces': true
	});
	
	$("#database-form").validate();
	
	$('[data-toggle="popover"]').popover({'placement': 'top'});		
				
	var tags = $("#type").attr('data-source');
	
	if ( tags != null )
	{
		var availableTags = tags.split(';');
		$( "#type" ).autocomplete({
			source: availableTags
		});
	}
}