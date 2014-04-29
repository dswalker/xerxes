$(document).ready(highlight);
$(document).ready(resort);
$(document).ready(addDeleteConfirm);

function addDeleteConfirm()
{
	$('.delete-confirm-fade').click(function()
	{		
  		var confirmed = confirm(xerxes_labels['text_databases_confirm_delete']);
		var target = $(this).attr('href');
		var data_source = $(this).attr('data-source');
		
		data_source = "#" + data_source;

		if ( confirmed )
		{
			$.get( target, function( data ) {
				$(data_source).fadeOut(500, function() { $(data_source).remove(); });
			});			
		}
		
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
	$("#subject-list ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
		
		var target = $(this).attr('data-target');
		var category = $(this).attr('data-category');
		var subcategory = $(this).attr('data-subcategory');
		var order = $(this).sortable("serialize") + "&cat=" + category + "&subcat=" + subcategory + "&noredirect=1";

		$.post(target, order, function(theResponse){
			
			// do something

		});														 
	}});
}
