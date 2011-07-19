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

{block name=navMain}
    {foreach $navMain as $entry}
        {if !isset($openBox) && !$entry.url}
            <!-- First NavHead -->
            <div class='navbox'>
            <h3>{$entry.display}</h3>
            <div class='links'>
            {$openBox=true}
        {elseif $openBox && !$entry.url}
            <!-- Any other NavHead -->
            </div></div>
            <div class='navbox'>
            <h3>{$entry.display}</h3>
            <div class='links'>
        {elseif $openBox}
            <!-- Normal Link -->
            <div class='linkitem'>
            <a href='{$entry.url}' title='{$entry.title}'>» {$entry.display}</a>
            </div>
        {else}
            <div class='navbox'>
            <h3>No NavHead</h3>
            <div class='links'>
            <div class='linkitem'>
            <a href='{$entry.url}' title='{$entry.title}'>» {$entry.display}</a>
            </div>
            {$openBox=true}
        {/if}
    {/foreach}
    </div></div>
{/block}

{block name=mainContent}
    {foreach $main as $entry}
        {$entry}
    {/foreach}
{/block}

{block name=toolBox}
    {if count($toolBox) > 0}
        <script type='text/javascript'>
        $(document).ready(function() {ldelim}
            $('.toolboxitem').hover(function() {ldelim}
                document.body.style.cursor='pointer';
            {rdelim});
            $('.toolboxitem').mouseout(function() {ldelim}
                document.body.style.cursor='default';
            {rdelim});
        {rdelim});
        </script>
        {foreach $toolBox as $entry}
            <img id='{$entry.name}' class='toolboxitem' src='{$entry.imagesrc}' title='{$entry.description}' />
            <script type='text/javascript'>
            $('#{$entry.name}').click(function() {ldelim}
                $.ajax({ldelim}
                  type: 'GET',
                  url: '{$entry.url}',
                  dataType: 'script'
                  {rdelim});
                $(this).replaceWith("<img src='{$entry.replaceimagesrc}' />");
            {rdelim});
            </script>
        {/foreach}
    {/if}
{/block}
