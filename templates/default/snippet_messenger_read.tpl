<div id="readmessageform">
    <form name="messageform" action="?{$target}" method="post">
        <input type="submit" value="Antworten" id="replybutton" />
        <div id="sender"><strong>Von:</strong> {$sender}</div>
        <div id="date"><strong>Datum:</strong> {$date}</div>
        <div id="subject"><strong>Betreff:</strong> {$subject}</div>
        <input type="submit" value="Weiterleiten" id="forwardbutton" />
        <div id="text">{$text}</div>
    </form>
</div>
