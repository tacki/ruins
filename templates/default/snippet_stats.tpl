<div class="statbox">
    <h3>Charakter</h3>

    <div class="item">
        <div class="statidentifier">Name:</div>
        <div class="statvalue">{$displayname}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Level:</div>
        <div class="statvalue">{$level}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Gesundheit:</div>
        <div class="statvalue">{$healthpoints}/{$lifepoints}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Stärke:</div>
        <div class="statvalue">{$strength}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Beweglichkeit:</div>
        <div class="statvalue">{$dexterity}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Konstitution:</div>
        <div class="statvalue">{$constitution}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Intelligenz:</div>
        <div class="statvalue">{$intelligence}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Weisheit:</div>
        <div class="statvalue">{$wisdom}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Charisma:</div>
        <div class="statvalue">{$charisma}</div>
    </div>

</div>
<div class="statbox">
    <h3>Besitz</h3>

    <div class="item">
        <div class="statidentifier">Waffe:</div>
        <div class="statvalue">{$weaponname}<br />{$weapondamage}</div>
    </div>

    <div class="item">
        <div class="statidentifier">Geld:</div>
        <div class="statvalue">
            {$gold}<img src="{$commontemplatedir}/images/gold.gif" height="10" alt="g"> /
            {$silver}<img src="{$commontemplatedir}/images/silver.gif" height="10" alt="s"> /
            {$copper}<img src="{$commontemplatedir}/images/copper.gif" height="10" alt="c">
        </div>
    </div>
</div>

<div class="statbox">
    <h3>Gerade hier</h3>

    <div class="item">
        <div class="statvalue">
            {$characters_here}
        </div>
    </div>
</div>
