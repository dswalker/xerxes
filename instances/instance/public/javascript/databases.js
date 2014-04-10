$(document).ready(highlight);
$(document).ready(resort);
$(document).ready(addDeleteConfirm);

function addDeleteConfirm()
{
	$('.delete-confirm').click(function()
	{
  		return confirm(xerxes_labels['text_databases_confirm_delete']);
	});
}

function highlight()
{
	$('.delete-confirm')
	
	$(".list-item").mouseover(function() {
			$(this).addClass("list-highlight");
			$(this).children(".list-item-action").css('visibility', 'visible');
		});

	$(".list-item").mouseout(function() {
			$(this).removeClass("list-highlight");
			$(this).children(".list-item-action").css('visibility', 'hidden');
		});
}

// drag and drop

function resort()
{	
	$("#subject-list ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
		
		var category = $(this).attr('data-source')
		var order = $(this).sortable("serialize") + "&category=" + category;

		$.post("databases-edit/reorder-subcategories", order, function(theResponse){
			
			// do something

		});														 
	}});
}
