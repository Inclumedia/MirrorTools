ALTER TABLE /*$wgDBprefix*/recentchanges
    ADD rc_mt_user bigint unsigned NOT NULL DEFAULT 0;