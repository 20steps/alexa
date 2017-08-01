$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});

$("input, textarea").focus(function(){  $(document.body).addClass('when-keyboard-showing');     });
$("input, textarea").blur( function(){  $(document.body).removeClass('when-keyboard-showing');  });