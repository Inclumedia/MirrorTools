ALTER TABLE /*$wgDBprefix*/page
    ADD page_mt_remotely_live TINYINT UNSIGNED NOT NULL DEFAULT 0;