$(document).ready(start)

function start(){
     $('.numeric').keydown(function(e) {
      $('#log').text('keyCode: ' + e.keyCode);
      if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 8 && e.keyCode != 9)
          e.preventDefault();
    });
}
