function updateSettings(settingsobject, setting, data)
{
    var postdata = new Object();
    postdata['settingsobject'] 		= settingsobject;
    postdata['setting'] 		= setting;
    postdata['data']			= data;

    $.post("Json/Main/UpdateSettings", postdata);
}

function updateArraySettings(settingsobject, setting, data, arrayaction)
{
    var postdata = new Object();
    postdata['settingsobject'] 		= settingsobject;
    postdata['setting'] 		= setting;
    postdata['data']			= data;
    postdata['arrayaction']		= arrayaction;

    $.post("Json/Main/UpdateSettings", postdata);
}
