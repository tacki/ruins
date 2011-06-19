<div id="createmessageform">
    <form name="messageform" action="?{$target}" method="post">
        <input type="submit" value="Senden" id="sendbutton" />
        <textarea name="receivers" cols="51" rows="3" id="receiverlist">{$receiver}</textarea>
        <input type="submit" value="Adressbuch" id="addressbookbutton" />
        <input type="text" name="subject" size="45" maxlength="50" id="subject" value="{$subject}"/>
        <textarea name="text" cols="67" rows="15" id="text">{$text}</textarea>
    </form>
</div>
