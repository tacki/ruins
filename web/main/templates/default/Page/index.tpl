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
    {$openBox=false}
    {foreach $navMain as $entry}
        {if !$openBox && !$entry.url}
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

{block name=statsContent}
    {if isset($user)}
    <div class="statbox">
        <h3>Charakter</h3>

        <div class="item">
            <div class="statidentifier">Name:</div>
            <div class="statvalue">{$user->character->getDisplayname(true)}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Level:</div>
            <div class="statvalue">{$user->character->level}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Gesundheit:</div>
            <div class="statvalue">{$user->character->healthpoints}/{$user->character->lifepoints}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Stärke:</div>
            <div class="statvalue">{$user->character->strength}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Beweglichkeit:</div>
            <div class="statvalue">{$user->character->dexterity}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Konstitution:</div>
            <div class="statvalue">{$user->character->constitution}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Intelligenz:</div>
            <div class="statvalue">{$user->character->intelligence}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Weisheit:</div>
            <div class="statvalue">{$user->character->wisdom}</div>
        </div>

        <div class="item">
            <div class="statidentifier">Charisma:</div>
            <div class="statvalue">{$user->character->charisma}</div>
        </div>

    </div>
    <div class="statbox">
        <h3>Besitz</h3>

        <div class="item">
            <div class="statidentifier">Waffe:</div>
            <div class="statvalue">
            {if isset($weapon)}
                {$weapon->name}<br />
                {$weapon->showDamage(false)}
            {/if}
            </div>
        </div>

        <div class="item">
            <div class="statidentifier">Geld:</div>
            <div class="statvalue">
                {$money->getAllCurrenciesWithPic()}
            </div>
        </div>
    </div>
    {/if}
{/block}

{block name=charactersNear}
    {if isset($charactersNear)}
    <div class="statbox">
        <h3>Gerade hier</h3>

        <div class="item">
            <div class="statvalue">
                {foreach $charactersNear as $charactername}
                    {if $charactername@first}
                        {$charactername}
                    {else}
                        , {$charactername}
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
    {/if}
{/block}

{block name=characterList}
    {if isset($charactersOnline)}
    <div class="characterbox">
    <h3>{count($charactersOnline)} User Online</h3>
    {foreach $charactersOnline as $character}
        <div class="item"><div class= "character">
            {$character}
        </div></div>
    {/foreach}
    </div>
    {/if}
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
