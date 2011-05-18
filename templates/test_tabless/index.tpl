<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

{*
    including the header-template
    ATTENTION to the absolute template-path!
    Content:
        - set the headtitle
        - set the css-filepath
        - variable: headtitle,headscript
*}

<head>
    <title>{$headtitle}</title>
    <meta http-equiv="content-type" content="application/xhtml+xml;charset=utf-8" />
    <link href="{$mytemplatedir}/index.css" rel="stylesheet" type="text/css" />
    {$headscript}
</head>

<body onload='startTimer()'>

<div id="wrap">
    <div id="wrap-inner">
    <div id="header">
        <!-- Header Content Start -->
        {*
            including the topbar.tpl
            ATTENTION to the path!
            Content:
                - place for banners
                - top-navigation
                - variable: gametitle,pagetitle,helptext,topnav
        *}
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
        {$stats}
        <!-- Stat Content End -->
    </div>

    <div id="userlist">
        <!-- Userlist Content Start -->
        {$userlist}
        <!-- Userlist Content End -->
    </div>

    </div>

    <div id="footer">
        {*
            including the footer-template
            ATTENTION to the path!
            Content:
            - pagegenerationtime
            - variables: version,copyright,servertime,pagegen
        *}
        {include file="$myfulltemplatedir/footer.tpl"}
    </div>
    </div> <!-- End #wrap-inner -->
</div> <!-- End #wrap -->
</body>
</html>
