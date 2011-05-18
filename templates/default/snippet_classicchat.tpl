{literal}
<script type="text/javascript">
    $(function() {
{/literal}
        setColorPreview('{$chatform}_chatline', '{$chatform}_chatpreview');
{literal}
    });
</script>
{/literal}

<a
    id="chatid_{$chatname}_hide"
    target="#"
    onclick="
                $('#chatid_{$chatname}').fadeOut('slow');
                $('#chatid_{$chatname}_show').show();
                $('#chatid_{$chatname}_hide').hide();

                updateSettings('character', 'chat_{$chatname}_visibility', 0);
            "
    style="font-size: 9px; margin: 0px; padding: 0px; display: {$visibility}"
    class="floatright button">Chat verstecken</a>
<a
    id="chatid_{$chatname}_show"
    target="#"
    onclick="
                $('#chatid_{$chatname}').fadeIn('slow');
                $('#chatid_{$chatname}_show').hide();
                $('#chatid_{$chatname}_hide').show();

                updateSettings('character', 'chat_{$chatname}_visibility', 1);
            "
    style="font-size: 9px; margin: 0px; padding: 0px; display: {$visibility_inv}"
    class="floatright button">Chat anzeigen</a>

<div class="floatleft" id="chatid_{$chatname}" style='display: {$visibility}'>
    <table class='chat'>
        {$chat_rows}
    </table>
    <div id='{$chatform}_chatpreview'></div>

    <form name='{$chatform}' action='?{$target}' method='post' >
        <input type='hidden' name='{$chatform}_op' value='addLine'/>
        <input type='hidden' name='{$chatform}_section' value='{$chatname}'/>
        <textarea class='floatleft textarea' name='{$chatform}_chatline' id='{$chatform}_chatline' cols='60' rows='5'></textarea>

        <input type='submit' value='Hinzufügen' class='button' style='margin: 2px'/>
    </form>

    <form action='?{$target}' method='post' >
        <input type='hidden' name='{$chatform}_op' value='editLine'/>
        <input type='hidden' name='{$chatform}_section' value='{$chatname}'/>
        <input type='submit' value='Edit' class='button' style='margin: 2px' />
    </form>

    <form action='?{$target}' method='post' >
        <input type='submit' value='Aktualisieren' class=' button' style='margin: 2px'/>
    </form>


    <span class='chat_pages' style='margin: 2px'>
        Seiten: {$chat_pages}
    </span>
</div>
<div class="floatclear"></div>
