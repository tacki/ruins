{extends file="layout.tpl"}

{block name=headScripts}
{foreach $cssHeadIncludes as $cssFile}
    <link href='{$cssFile}' rel='stylesheet' type='text/css' />
{/foreach}
    <link href='{$basetemplatedir}/styles/base.css' rel='stylesheet' type='text/css' />
    <link href='{$mytemplatedir}/styles/content.css' rel='stylesheet' type='text/css' />
    <link href='{$mytemplatedir}/styles/layout.css' rel='stylesheet' type='text/css' />
{foreach $jsHeadIncludes as $jsFile}
    <script src='{$jsFile}' type='text/javascript'></script>
{/foreach}
{/block}

{block name=navMain}
    {$i=0}
    {foreach $navMain as $entry}
        <li class='navid_{$i}'>
            <a href='{$entry.url}?navid={$i}'>{$entry.display}</a>
        </li>
        {$i=$i+1}
    {/foreach}
{/block}

{block name=mainContent}
    {foreach $main as $entry}
        {$entry}
    {/foreach}
{/block}