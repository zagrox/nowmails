<?php
namespace NowMails;

class i18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'nowmails',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
} 