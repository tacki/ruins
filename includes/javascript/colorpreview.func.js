function setColorPreview(input_id, preview_id)
{
    var callURL			= "includes/helpers/ajax/btcode.ajax.php";

    var cpInputId 		= "#" + input_id;
    var cpPreviewId 		= "#" + preview_id;

    var search_timeout = undefined;

    $(cpInputId).keyup(function() {
        if(search_timeout != undefined) {
            clearTimeout(search_timeout);
        }

        search_timeout = setTimeout(function() {
            search_timeout = undefined;

            $.getJSON(callURL+"?action=decode&decodestring="+Url.encode($(cpInputId).val()), {}, function(json) {
                $(cpPreviewId).html ( json );
            });
        }, 500);
    });
}
