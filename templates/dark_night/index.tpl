<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>{$headtitle}</title>
    <meta http-equiv="content-type" content="application/xhtml+xml;charset=utf-8" />
    <link href="{$mytemplatedir}/index.css" rel="stylesheet" type="text/css" />
    {$headscript}
</head>

<body onload='startTimer()'>

<div id="wrap">

    <div id="header">
        <!-- Header Content Start -->
        {include file="$myfulltemplatedir/topbar.tpl"}
        <!-- Header Content End -->
    </div>

    <div id="main-body">

        <div id="sidebar">
            <!-- Navigation Content Start -->
            {$leftbar}
            <!-- Navigation Content End -->
        </div>

        <div id="content">
            <!-- Main Content Start -->
            {$main}
            <!-- Main Content Ends -->
        </div>

        <div id="stats">
            <!-- Stat Content Start -->
            {include file="$myfulltemplatedir/rightbar.tpl"}
            <!-- Stat Content End -->
        </div>

    </div>

    <div id="footer">
        <!-- Footer Content Start -->
        {include file="$myfulltemplatedir/footer.tpl"}
        <!-- Footer Content End -->
    </div>

</div> <!-- End #wrap -->
</body>
</html>
