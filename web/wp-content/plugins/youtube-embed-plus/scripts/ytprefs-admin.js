function widen_ytprefs_wiz() {
    setTimeout(function () {
        jQuery("#TB_window").addClass('epyt-thickbox').animate({marginLeft: '-475px', width: '950px'}, 150, 'swing', function () {
            jQuery("#TB_window").get(0).style.setProperty('width', '950px', 'important');
        });
        
        jQuery("#TB_overlay").addClass('epyt-thickbox');

        jQuery("#TB_window iframe").animate({width: '950px'}, 150);
    }, 750);
}

jQuery(document).ready(function () {

    if (window.location.toString().indexOf('https://') === 0)
    {
        window._EPYTA_.wpajaxurl = window._EPYTA_.wpajaxurl.replace("http://", "https://");
    }
    // Create IE + others compatible event handler
    var epeventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var epeventer = window[epeventMethod];
    var epmessageEvent = epeventMethod === "attachEvent" ? "onmessage" : "message";
    // Listen to message from child window
    epeventer(epmessageEvent, function (e)
    {
        var embedcode = "";
        try
        {
            if (e.data.indexOf("youtubeembedplus") === 0)
            {
                embedcode = e.data.split("|")[1];
                if (embedcode.indexOf("[") !== 0)
                {
                    embedcode = "<p>" + embedcode + "</p>";
                }

                if (window.tinyMCE !== null && window.tinyMCE.activeEditor !== null && !window.tinyMCE.activeEditor.isHidden())
                {
                    if (typeof window.tinyMCE.execInstanceCommand !== 'undefined')
                    {
                        window.tinyMCE.execInstanceCommand(
                                window.tinyMCE.activeEditor.id,
                                'mceInsertContent',
                                false,
                                embedcode);
                    }
                    else
                    {
                        send_to_editor(embedcode);
                    }
                }
                else
                {
                    embedcode = embedcode.replace('<p>', '\n').replace('</p>', '\n');
                    if (typeof QTags.insertContent === 'function')
                    {
                        QTags.insertContent(embedcode);
                    }
                    else
                    {
                        send_to_editor(embedcode);
                    }
                }
                tb_remove();
            }
        }
        catch (err)
        {

        }
    }, false);


    jQuery('body').on('click.tbyt', "#ytprefs_wiz_button", function () {
        widen_ytprefs_wiz();
    });
    jQuery(window).resize(widen_ytprefs_wiz);
});