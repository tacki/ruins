<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>{$headtitle}</title>
    <meta http-equiv="content-type" content="application/xhtml+xml;charset=utf-8" />
    <link href="{$mytemplatedir}/index_popup.css" rel="stylesheet" type="text/css" />
    {$headscript}
</head>

<body onload='startTimer()'>

<div id="main-body">

    <div id="content">
    <!-- Main Content Start -->
    {$main}
    <!-- Main Content Ends -->
    </div>

</div>

<div id="footer>
    <div id=time><span id='serverTime'>{$servertime}</span> Uhr</div>
    <div id=pagegen>{$pagegen}ms</div>
</div>

</body>
</html>
