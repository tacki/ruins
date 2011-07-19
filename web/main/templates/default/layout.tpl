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
            {$navMain}
            <!-- Navigation Content End -->
        </div>

        <div id="content">
            <!-- Main Content Start -->
            {block name=mainContent}No Text{/block}
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
                Servertime: <span id='serverTime'>{$smarty.now|date_format:'%H:%M:%S'}</span> Uhr
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
{/block}