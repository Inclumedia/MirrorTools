ALTER TABLE /*$wgDBprefix*/revision
    ADD rev_mt_ar_page_id bigint unsigned NOT NULL DEFAULT 0;