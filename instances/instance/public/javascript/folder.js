$(document).ready(addSelectAll);
$(document).ready(addDeleteConfirm);
$(document).ready(function(){ $("[rel='tooltip']").tooltip(); });


function addSelectAll()
{
	$('#folder-select-all').click(function() {
		if ( $("#folder-select-all").prop("checked") == true )
		{
			$(".folder-output-checkbox").prop("checked", true);
		}
		else
		{
			$(".folder-output-checkbox").prop("checked", false);
		}
		return true;
	});
}

function addDeleteConfirm()
{
	$('#folder-delete').click(function()
	{
  		return confirm("Delete these records?");
	});
}