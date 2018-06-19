var element = $('#chat');
var myStorage = localStorage;

if (!myStorage.getItem('chatID')) {
    myStorage.setItem('chatID', createUUID());
}

setTimeout(function() {
    $('#chat').addClass('enter');

}, 100);
setTimeout(function() {
    $('#offers').addClass('enter');

}, 100);

$('#chat').click(openElement);

function openElement() {
    var messages = $('#chat').find('.messages');
    var textInput = $('#chat').find('.text-boxx');
    $('#chat').find('>i').hide();
    $('#chat').addClass('expand');
    $('#chat').find('.chat').addClass('enter');

    var strLength = textInput.val().length * 2;
    textInput.keydown(onMetaAndEnter).prop("disabled", false).focus();
    $('#chat').off('click', openElement);
    $('#chat').find('.header button').click(closeElement);
    $('#chat').find('#sendMessage').click(sendNewMessage);
    messages.scrollTop(messages.prop("scrollHeight"));
}

function closeElement() {
    $('#chat').find('.chat').removeClass('enter').hide();
    $('#chat').find('>i').show();
    $('#chat').removeClass('expand');
    $('#chat').find('.header button').off('click', closeElement);
    $('#chat').find('#sendMessage').off('click', sendNewMessage);
    $('#chat').find('.text-boxx').off('keydown', onMetaAndEnter).prop("disabled", true).blur();
    setTimeout(function() {
        $('#chat').find('.chat').removeClass('enter').show()
        $('#chat').click(openElement);
    }, 500);
}

function createUUID() {
    // http://www.ietf.org/rfc/rfc4122.txt
    var s = [];
    var hexDigits = "0123456789abcdef";
    for (var i = 0; i < 36; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }
    s[14] = "4"; // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "-";

    var uuid = s.join("");
    return uuid;
}

function sendNewMessage() {
    var userInput = $('.text-box');
    var newMessage = userInput.html().replace(/\<div\>|\<br.*?\>/ig, '\n').replace(/\<\/div\>/g, '').trim().replace(/\n/g, '<br>');

    if (!newMessage) return;

    var messagesContainer = $('.messages');

    messagesContainer.append([
        '<li class="self">',
        newMessage,
        '</li>'
    ].join(''));

    // clean out old message
    userInput.html('');
    // focus on input
    userInput.focus();

    messagesContainer.finish().animate({
        scrollTop: messagesContainer.prop("scrollHeight")
    }, 250);
}

function onMetaAndEnter(event) {
    if ((event.metaKey || event.ctrlKey) && event.keyCode == 13) {
        sendNewMessage();
    }
}