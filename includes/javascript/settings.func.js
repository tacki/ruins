function updateSettings(settingsobject, setting, data)
{
    var postdata = new Object();
    postdata['settingsobject'] 	= settingsobject;
    postdata['setting'] 		= setting;
    postdata['data']			= data;

    $.post("includes/helpers/ajax/update_settings.ajax.php", postdata);
}
