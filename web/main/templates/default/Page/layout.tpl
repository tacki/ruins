{extends file="base.tpl"}

{block name=body}
<div id="wrap">
    <div id="header">
        <!-- Header Content Start -->
        <div class="head">
            {$pagetitle}
        </div>
        <div id="navShared">
            {block name=navShared}{/block}
        </div>
        <!-- Header Content End -->
    </div>

    <div id="main-body">

        <div id="navMain">
            <!-- Navigation Content Start -->
            {block name=navMain}{/block}
            <!-- Navigation Content End -->
        </div>

        <div id="content">
            <!-- Main Content Start -->
            {block name=mainContent}No Text{/block}
            <!-- Main Content Ends -->
        </div>

        <div id="stats">
            <!-- Stat Content Start -->
            {block name=statsContent}{/block}
            {block name=charactersNear}{/block}
            <!-- Stat Content End -->
        </div>

        <div id="characterlist">
            <!-- CharacterList Content Start -->
            {block name=characterList}{/block}
            <!-- CharacterList Content End -->
        </div>

    </div>

    <div id="footer">
        <!-- Footer Content Start -->
        <div id="footer-inner">
            <div class="time">
                Servertime: <span id='serverTime'>{$smarty.now|date_format:'%H:%M:%S' nocache}</span> Uhr
            </div>
            <div class="info">
                {if isset($user)}
                    [User: {$user->login}]
                {/if}
                {$copyright}
            </div>
            <div id="toolbox">
                {block name=toolBox}{/block}
            </div>
        </div>
        <div id="pagegen">
            Site generated in {$pagegen}ms
        </div>
        <!-- Footer Content End -->
    </div>
</div> <!-- End #wrap -->
{/block}