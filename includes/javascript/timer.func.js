/**
 * Java Timer Functions
 *
 * Handles Realtime-Timers on the page
 * @author Markus Schlegel <g42@gmx.net>
 * @author die-staemme.de (original version)
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: timer.func.js 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

var timers = new Array();
var timeDiff = null;
var timeStart = null;

$(document).ready(function(){

    var serverTime = getTime($('#serverTime'));
    timeDiff = serverTime-getLocalTime();
    timeStart = serverTime;

    // Nach span mit der Klasse timer und timer_replace suchen
    $('span.timer, span.timer_replace').each(function () {
        startTime = getTime($(this));

        if (startTime != -1) {
            addTimer($(this), serverTime+startTime, ($(this).hasClass('timer')));
        }
    });

    // Tickerfunktion wird alle 500ms aufgerufen um eine 'sauberere' Anzeige zu haben
    $(document).everyTime('500ms', 'timeTicker', function() {
        tickTime();

        for(timer=0; timer<timers.length; timer++){
            remove = tickTimer(timers[timer]);
            if(remove) {
                timers.splice(timer, 1);
            }
        }
    });
});

function getLocalTime() {
    var now = new Date();
    return Math.floor(now.getTime()/1000)
}

function addTimer(element, endTime, reload) {
    var timer = new Object();
    timer['element'] = element;
    timer['endTime'] = endTime;
    timer['reload'] = reload;
    timers.push(timer);
}

function tickTime() {
    var serverTime = $('#serverTime');

    if(serverTime != null) {
        time = getLocalTime()+timeDiff;
        formatTime(serverTime, time, true);
    }
}

function tickTimer(timer) {
    var time = timer['endTime']-(getLocalTime()+timeDiff);

    if(timer['reload'] && time < 0) {
        // Reload page
        document.location.href = document.location.href;
        formatTime(timer['element'], 0, false);
        return true;
    }

    if (!timer['reload'] && time <= 0)
    {
        // Timer ausblenden und Alternativ-Text anzeigen
        timer['element'].next().show(); // Nachfolger einblenden
        timer['element'].remove(); // Element entfernen

        return true;
    }

    formatTime(timer['element'], time, false);
    return false;
}

function getTime(element) {
    // Zeit auslesen
    if(element.text() == null) return -1;
    time = element.text();
    part = time.split(':');

    // Führende Nullen entfernen
    for(j=0; j<3; j++) {
        if(part[j].charAt(0) == "0")
            part[j] = part[j].substring(1, part[j].length);
    }

    // Zusammenfassen
    hours = parseInt(part[0]);
    minutes = parseInt(part[1]);
    seconds = parseInt(part[2]);
    time = hours*60*60+minutes*60+seconds;
    return time;
}

function formatTime(element, time, clamp) {
    // Wieder aufsplitten
    hours = Math.floor(time/3600);
    if(clamp) hours = hours%24;
    minutes = Math.floor(time/60) % 60;
    seconds = time % 60;

    var timeString = "";
    if(hours < 10)
        timeString = "0";
    timeString += hours + ":";
    if(minutes < 10)
        timeString += "0";
    timeString += minutes + ":";
    if(seconds < 10)
        timeString += "0";
    timeString += seconds;

    element.html(timeString);
}
