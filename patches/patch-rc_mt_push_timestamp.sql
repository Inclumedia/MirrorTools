ALTER TABLE /*$wgDBprefix*/recentchanges
    ADD rc_mt_push_timestamp binary(14) NOT NULL default '';