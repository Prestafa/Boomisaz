/*
 * Prestafa
 *
 * */
function tinySetup(config)
{
    if (typeof tinyMCE === 'undefined') {
        setTimeout(function() {
            tinySetup(config);
        }, 100);
        return;
    }

    if(!config)
        config = {};

    //var editor_selector = 'rte';
    if (typeof config.editor_selector != 'undefined')
        config.selector = '.'+config.editor_selector;

    var default_config_rtl = {
        plugins : "colorpicker link image paste pagebreak table contextmenu filemanager table code media autoresize textcolor anchor",
        toolbar1 : "code,|,bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,alignfull,formatselect,|,blockquote,colorpicker,pasteword,|,bullist,numlist,|,outdent,indent,|,link,unlink,|,anchor,|,media,image",
        language: iso,
        content_css : psf_plus_skin_tinymce
    };
    $.each(default_config_rtl, function(index, el)
    {
        if (config[index] === undefined )
            config[index] = el;
    });

    if (typeof changeToMaterial == 'function') {
        // Change icons in popups
        $('body').on('click', '.mce-btn, .mce-open, .mce-menu-item', function () {
            changeToMaterial();
        });
    }

    tinyMCE.init(config);
}