// enable popovers
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});

// close popover on click outside
$('body').on('click', function (e) {
    //did not click a popover toggle, or icon in popover toggle, or popover
    if ($(e.target).data('toggle') !== 'popover'
        && $(e.target).parents('[data-toggle="popover"]').length === 0
        && $(e.target).parents('.popover.in').length === 0) {
        $('[data-toggle="popover"]').popover('hide');
    }
});

// hide footer on keyboard up
$("input, textarea").focus(function(){  $(document.body).addClass('when-keyboard-showing');     });
$("input, textarea").blur( function(){  $(document.body).removeClass('when-keyboard-showing');  });

// reset changes entered in form by user
function resetForm() {
    $("form")[0].reset();
};

// goto start on logo click
$("#logo-container-top").click(function() {
    window.location.href = "/";
});

// goto start on logo click
$("#logo-container").click(function() {
    window.location.href = "/";
});