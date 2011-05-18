{*
    including the header-template
    ATTENTION to the absolute template-path!
    Content:
        - set the headtitle
        - set the css-filepath
        - variable: headtitle,headscript
*}
{include file="$myfulltemplatedir/header.tpl"}

<body onload='startTimer()'>

{*
    including the topbar.tpl
    ATTENTION to the path!
    Content:
        - place for banners
        - top-navigation
        - variable: gametitle,pagetitle,helptext,topnav
*}
{include file="$myfulltemplatedir/topbar.tpl"}

<tr><td>
    <table>
    <tr>
        <td align='left' valign='top' width='150' background='{$mytemplatedir}/images/bg.png'>
            {*
                including the leftbar.tpl
                ATTENTION to the path!
                Content:
                    - navigation
                    - or stats
                    - or banners
                    - variable: leftbar
            *}
            {include file="$myfulltemplatedir/leftbar.tpl"}
        </td>
        <td valign='top' width='100%'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr><td>
<!-- main start -->
{$main}
<!-- main end -->
                </td></tr>
            </table>
        </td>
        <td align='right' valign='top' width='150'>
            {*
                inlcuding the rightbar.tpl
                ATTENTION to the path
                Content:
                    - navigation
                    - or stats
                    - or banners
                    - variable: rightbar
            *}
            {include file="$myfulltemplatedir/rightbar.tpl"}
            </td>
    </tr>
    </table>
</td></tr>
{*
    including the footer-template
    ATTENTION to the path!
    Content:
        - pagegenerationtime
        - variables: version,copyright,servertime,pagegen
*}
{include file="$myfulltemplatedir/footer.tpl"}
