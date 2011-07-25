/**
 * Refresh the page if the token-owner calculated the round, but the active user is still on the old one
 *
 * Runs every 12s
 * @param getBattleround_url URL to get the current battleround
 */
function refreshOnNewRound()
{
    var callURL			= {{basepath call='Json/Battle/CheckNewRound'}};
    var parameters		= "";
    var timerCycle		= '12s';
    var curbattleround 		= 1;
    var pageURL 		= unescape(window.location.href);


    $(document).ready(function() {
        $.getJSON(callURL, {}, function(json) {
            curbattleround = json;
        });
    });

    $(document).everyTime(timerCycle, function() {
        $.getJSON(callURL, {}, function(json) {
            if (json != curbattleround) {
                window.location.replace( pageURL );
            }
        });
    }, 0);
}

/**
 * Refresh the page if Battle Members choosed their action
 *
 * Runs every 5s
 * @param battleid Battle ID
 * @param tokenowner True if called by the tokenowner
 */
function checkBattleActionDone(battleid, tokenowner)
{
    var callURL			= {{basepath call='Json/Battle/CheckActionDone'}};
    var parameters		= "battleid="+battleid;
    var timerCycle		= '5s';
    var pageURL 		= unescape(window.location.href);
    var tokenowner 		= tokenowner || false;

    $(document).everyTime(timerCycle, function() {
        $.getJSON(callURL+"?"+parameters, {}, function(json) {
            if (tokenowner && json['battlemembers'].length > 1 && json['waitingfor'].length == 0) {
                // Refresh if this is called by the tokenowner
                // and there are more than 1 battlemember
                // and there are no more battlemembers, we are waiting for
                window.location.replace( pageURL );
            }

            // Fade out Battlemembers who made an action
            var actiondone = array_diff(json['battlemembers'], json['waitingfor']);
            jQuery.each(actiondone, function(key, charid) {
                $("#action_"+charid).fadeTo("slow", 0.5);
            });
        });
    }, 0);
}

/**
 * Get possible Targetlist for a given skill
 * @param battleid Battle ID
 * @param charid Character ID
 * @param skillselectform Name of the select-field which chooses the skills
 * @param targetselectform Name of the select-field where the targets should appear
 */
function getTargetList(battleid, charid, skillselectform, targetselectform)
{
    $(document).ready(function() {
        var callURL 		= {{basepath call='Json/Battle/GetTargetsForSkill'}};
        var parameters		= "battleid="+battleid+"&charid="+charid;
        var selectedskill 	= $("select[name="+skillselectform+"] option:selected").val();
        var url 		= callURL+"?"+parameters+"&skillname="+selectedskill;

        insertTargetsIntoForm(url, targetselectform);
        // Update Description
        $("#skilldescription").text($("select[name="+skillselectform+"] option:selected").attr('title'));

        $("select[name="+skillselectform+"]").change(function () {
            var newselected = $("select[name="+skillselectform+"] option:selected").val();
            var url = callURL+"?"+parameters+"&skillname="+newselected;

            insertTargetsIntoForm(url, targetselectform);
            // Update Description
            $("#skilldescription").text($("select[name="+skillselectform+"] option:selected").attr('title'));
        });
    });
}

/**
 * Insert the via Ajax-Call retrieved Data into the targetselectform
 * @param url
 * @param targetselectform
 */
function insertTargetsIntoForm(url, targetselectform)
{
    // Disable all Inputfields (including the Button) until the Query finished
    $("form[name=skillchooser]").find("input").attr("disabled", "disabled");

    $.getJSON(url, {}, function(json) {
        // Remove all existing Options
        $("select[name="+targetselectform+"] option").remove();

        jQuery.each(json, function(charid, charname) {
            $("select[name="+targetselectform+"]").append(
                    // Insert new Options
                    $('<option></option').val(charid).html(charname)
            );
        });

        // Re-enable the Form
        $("form[name=skillchooser]").find("input").removeAttr("disabled");
    });
}
