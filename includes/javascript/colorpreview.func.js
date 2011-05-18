function setColorPreview(input_id, preview_id)
{
    btCode = new btCode();
    var cpInputId = "#" + input_id;
    var cpPreviewId = "#" + preview_id;

    $(cpInputId).keyup(function() {
        var str = $(cpInputId).val();

        $(cpPreviewId).html( btCode.decode(str) );
    });
}
