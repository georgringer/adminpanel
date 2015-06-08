require.config({
    // By default load any module IDs from js/lib
    baseUrl: 'typo3/',
    paths: {
        jquery: 'contrib/jquery/jquery-1.11.2'
    }
});

// Start the main app logic.
requirejs(['jquery'], function ($) {
    var AdminPanel = {};

    AdminPanel.initialize = function() {

        // toggle the menu
        $('#t3-panel-wrap').on('click', '.t3-dropdown-toggle', function(evt) {
            if ($(this).siblings('.t3-dropdown-menu').is(':visible')) {
                $(this).siblings('.t3-dropdown-menu').hide();
            } else {
                $(this).siblings('.t3-dropdown-menu').show();
            }
            evt.preventDefault();
        });

        $('.t3-dropdown-menu').on('click', '.t3-btn', function(evt) {
            evt.preventDefault();
            var trgt = $(this).data('target');
            $(trgt).show().siblings('.t3-panel').hide();
        });

        $('#t3-panel-wrap').on('change keyup blur', 'select, input', function() {
            $('.t3-link-reload').css({display: 'inline-block'});
        });

        $('#t3-panel-wrap').on('click', '.t3-link-reload', function(evt) {
            AdminPanel.saveChanges();
            evt.preventDefault();
        });

    };


    AdminPanel.openEditWindow = function() {
        if (parent.opener && parent.opener.top && parent.opener.top.TS) {
            parent.opener.top.fsMod.recentIds["web"]=23;
            if (parent.opener.top.content && parent.opener.top.content.nav_frame && parent.opener.top.content.nav_frame.refresh_nav) {
                parent.opener.top.content.nav_frame.refresh_nav();
            }
//            parent.opener.top.goToModule("' . $pageModule . '");
            parent.opener.top.focus();
        } else {
//            vHWin=window.open(\'' . TYPO3_mainDir . BackendUtility::getBackendScript() . '\',\'' . md5(('Typo3Backend-' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'])) . '\',\'status=1,menubar=1,scrollbars=1,resizable=1\');
            vHWin.focus();
        }
        return false;
    };

    /**
     * save changes to the BE_USER
     */
    AdminPanel.saveChanges = function() {
        var $formEl = $('#t3-panel-wrap'),
            formUrl = $formEl.find('#t3-url-ajax').val();

        $.ajax(formUrl, {data: $formEl.serializeArray(), method: 'POST'}).done(function(res) {
            console.debug(res);
        });
    };

    AdminPanel.initialize();
});
