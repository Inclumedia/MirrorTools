ALTER TABLE /*$wgDBprefix*/revision
    ADD rev_mt_remotely_live tinyint UNSIGNED NOT NULL DEFAULT 0;