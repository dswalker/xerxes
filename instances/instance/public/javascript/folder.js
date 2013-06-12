$(document).ready(addSelectAll);

function addSelectAll()
{
	$('#folder-select-all').click(function() {
		if ( $(".folder-output-checkbox").attr("checked") == 'checked' ) {
			$(".folder-output-checkbox").prop("checked", true);
		}
		else
		{
			$(".folder-output-checkbox").prop("checked", false);
		}
		return true;
	});
}