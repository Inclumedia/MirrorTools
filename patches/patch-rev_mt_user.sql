ALTER TABLE /*$wgDBprefix*/revision
    ADD rev_mt_user bigint unsigned NOT NULL DEFAULT 0;