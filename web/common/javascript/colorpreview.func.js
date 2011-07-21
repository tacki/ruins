function setColorPreview(input_id, preview_id, url)
{
    var callURL			= url;

    var cpInputId 		= "#" + input_id;
    var cpPreviewId 		= "#" + preview_id;

    var delay			= 500;

    var search_timeout 		= undefined;

    $(cpInputId).keypress(function() {
        if(search_timeout != undefined) {
            clearTimeout(search_timeout);
        }

        search_timeout = setTimeout(function() {
            search_timeout = undefined;

            $.post(callURL+"?action=decode", {decodestring: "" + Url.encode($(cpInputId).val()) + ""}, function(data) {
                $(cpPreviewId).html ( jQuery.parseJSON(data) );
            });
        }, delay);
    });
}
