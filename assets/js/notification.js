// load number of new messages at page loading
getMessageCount();

// load number of new messages in a loop
setInterval(getMessageCount, 60000);

// load and show new count of  messages
function getMessageCount() {
    var $newMessages = parseInt(0);

    // load data
    $.getJSON($('#badge-messages').data('request-url'), function(json) {
        // show or hide the badge for new messages
        $newMessages = parseInt(json);
        if ($newMessages == 0) {
            $('#badge-messages').css('display', 'none');
        }
        else {
            $('#badge-messages').empty().append($newMessages).fadeIn('fast');
        }
    });
}

$('#icon-messages').click(function() {

    // remove all <li> entries from dropdown
    $('#dropdown-messages').find('li').remove();

    // append title and loader to dropdown
    $('#dropdown-messages').append(
        '<li id="loader_messages"><div class="loader"><div class="sk-spinner sk-spinner-three-bounce">' +
        '<div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></li>'
    );

    // load newest notifications
    $.get($(this).data('request-url')).success(function(html) {
        $("#dropdown-messages").html(html);
        $('.time').timeago();
    });

});