<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>{$headtitle}</title>
    <meta http-equiv="content-type" content="application/xhtml+xml;charset=utf-8" />
    <link href="{$mytemplatedir}/layout_page.css" rel="stylesheet" type="text/css" />
    <link href="{$mytemplatedir}/content_page.css" rel="stylesheet" type="text/css" />
    {$headscript}
</head>

<body>

<div id="wrap">
    <div id="header">
        <!-- Header Content Start -->
        <div class="head">
            {$pagetitle}
        </div>
        <div id="navShared">
            {$navShared}
        </div>
        <!-- Header Content End -->
    </div>

    <div id="main-body">

        <div id="navMain">
            <!-- Navigation Content Start -->
            {$navMain}
            <!-- Navigation Content End -->
        </div>

        <div id="content">
            <!-- Main Content Start -->
            {$main}
            <!-- Main Content Ends -->
        </div>

        <div id="stats">
            <!-- Stat Content Start -->
            {$stats}
            <!-- Stat Content End -->
        </div>

        <div id="characterlist">
            <!-- CharacterList Content Start -->
            {$characterlist}
            <!-- CharacterList Content End -->
        </div>

    </div>

    <div id="footer">
        <!-- Footer Content Start -->
        <div id="footer-inner">
            <div class="time">
                Servertime: <span id='serverTime'>{$servertime}</span> Uhr
            </div>
            <div class="info">
                {$version} {$copyright}
            </div>
            <div id="toolbox">
                {$toolbox}
            </div>
        </div>
        <div id="pagegen">
            Site generated in {$pagegen}ms
        </div>
        <!-- Footer Content End -->
    </div>
</div> <!-- End #wrap -->

</body>
</html>
