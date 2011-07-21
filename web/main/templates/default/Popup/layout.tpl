{extends file="base.tpl"}

{block name=body}
<div id="header">
    {if isset($smarty.get.navid)}
    <ul class='popupnav' id='navidsel_{$smarty.get.navid}'>
    {else}
    <ul class='popupnav' id='navidsel_0'>
    {/if}
        {block name=navMain}{/block}
    </ul>
</div>

<div id="main-body">

    <div id="content">
    <!-- Main Content Start -->
    {block name=mainContent}No Text{/block}
    <!-- Main Content Ends -->
    </div>

</div>

<div id="footer">
    <div id=time><span id='serverTime'>{$smarty.now|date_format:'%H:%M:%S' nocache}</span> Uhr</div>
    <div id=pagegen>{$pagegen}ms</div>
</div>
{/block}