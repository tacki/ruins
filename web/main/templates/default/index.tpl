{extends file="layout.tpl"}

{block name=headScripts}
{foreach $cssHeadIncludes as $cssFile}
    <link href='{$cssFile}' rel='stylesheet' type='text/css' />
{/foreach}
    <link href='{$mytemplatedir}/styles/content_stdelements.css' rel='stylesheet' type='text/css' />
    <link href='{$mytemplatedir}/styles/content.css' rel='stylesheet' type='text/css' />
    <link href='{$mytemplatedir}/styles/layout.css' rel='stylesheet' type='text/css' />
{foreach $jsHeadIncludes as $jsFile}
    <script src='{$jsFile}' type='text/javascript'></script>
{/foreach}
{/block}

{block name=navShared}
    {foreach $navShared as $entry}
        <a href='{$entry.url}' title='{$entry.title}'>{$entry.display}</a>
    {/foreach}
{/block}

{block name=mainContent}
    {foreach $main as $entry}
        {$entry}
    {/foreach}
{/block}