ALTER TABLE /*$wgDBprefix*/revision
    ADD rev_mt_former_page bigint unsigned NOT NULL DEFAULT 0;