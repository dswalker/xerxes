$(document).ready(addSelectAll);

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