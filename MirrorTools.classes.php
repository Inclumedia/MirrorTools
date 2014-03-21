<?php

class MirrorTools {
        public static function SchemaUpdates( $updater ) {
		// log_id
		$updater->addExtensionUpdate( array( 'modifyField', 'logging', 'log_id',
                        dirname( __FILE__ ) . '/patches/patch-log_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'change_tag', 'ct_log_id',
                        dirname( __FILE__ ) . '/patches/patch-ct_log_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'log_search', 'ls_log_id',
                        dirname( __FILE__ ) . '/patches/patch-ls_log_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_logid',
                        dirname( __FILE__ ) . '/patches/patch-rc_logid-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'tag_summary', 'ts_log_id',
                        dirname( __FILE__ ) . '/patches/patch-ts_log_id-bigint-unsigned.sql', true ) );
                // page_id
		$updater->addExtensionUpdate( array( 'modifyField', 'page', 'page_id',
                        dirname( __FILE__ ) . '/patches/patch-page_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'archive', 'ar_page_id',
                        dirname( __FILE__ ) . '/patches/patch-ar_page_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'categorylinks', 'cl_from',
                        dirname( __FILE__ ) . '/patches/patch-cl_from-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'hitcounter', 'hc_id',
                        dirname( __FILE__ ) . '/patches/patch-hc_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'imagelinks', 'il_from',
                        dirname( __FILE__ ) . '/patches/patch-il_from-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'iwlinks', 'iwl_from',
                        dirname( __FILE__ ) . '/patches/patch-iwl_from-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'langlinks', 'll_from',
                        dirname( __FILE__ ) . '/patches/patch-ll_from-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'logging', 'log_page',
			dirname( __FILE__ ) . '/patches/patch-log_page-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'pagelinks', 'pl_from',
                        dirname( __FILE__ ) . '/patches/patch-pl_from-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'page_props', 'pp_page',
                        dirname( __FILE__ ) . '/patches/patch-pp_page-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'page_restrictions', 'pr_page',
                        dirname( __FILE__ ) . '/patches/patch-pr_page-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_cur_id',
                        dirname( __FILE__ ) . '/patches/patch-rc_cur_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'redirect', 'rd_from',
                        dirname( __FILE__ ) . '/patches/patch-rd_from-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'revision', 'rev_page',
                        dirname( __FILE__ ) . '/patches/patch-rev_page-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'searchindex', 'si_page',
                        dirname( __FILE__ ) . '/patches/patch-si_page-bigint-unsigned.sql', true ) );
		// rc_id
		$updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_id',
                        dirname( __FILE__ ) . '/patches/patch-rc_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'change_tag', 'ct_rc_id',
                        dirname( __FILE__ ) . '/patches/patch-ct_rc_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'tag_summary', 'ts_rc_id',
                        dirname( __FILE__ ) . '/patches/patch-ts_rc_id-bigint-unsigned.sql', true ) );
		// rev_id
		$updater->addExtensionUpdate( array( 'modifyField', 'revision', 'rev_id',
                        dirname( __FILE__ ) . '/patches/patch-rev_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'archive', 'ar_rev_id',
                        dirname( __FILE__ ) . '/patches/patch-ar_rev_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'archive', 'ar_parent_id',
                        dirname( __FILE__ ) . '/patches/patch-ar_parent_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'change_tag', 'ct_rev_id',
                        dirname( __FILE__ ) . '/patches/patch-ct_rev_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'page', 'page_latest',
                        dirname( __FILE__ ) . '/patches/patch-page_latest-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_this_oldid',
                        dirname( __FILE__ ) . '/patches/patch-rc_this_oldid-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_last_oldid',
                        dirname( __FILE__ ) . '/patches/patch-rc_last_oldid-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'revision', 'rev_parent_id',
                        dirname( __FILE__ ) . '/patches/patch-rev_parent_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'tag_summary', 'ts_rev_id',
                        dirname( __FILE__ ) . '/patches/patch-ts_rev_id-bigint-unsigned.sql', true ) );
		// user_id
		$updater->addExtensionUpdate( array( 'modifyField', 'user', 'user_id',
                        dirname( __FILE__ ) . '/patches/patch-user_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'archive', 'ar_user',
                        dirname( __FILE__ ) . '/patches/patch-ar_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'filearchive', 'fa_deleted_user',
                        dirname( __FILE__ ) . '/patches/patch-fa_deleted_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'filearchive', 'fa_user',
                        dirname( __FILE__ ) . '/patches/patch-fa_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'image', 'img_user',
                        dirname( __FILE__ ) . '/patches/patch-img_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'ipblocks', 'ipb_user',
                        dirname( __FILE__ ) . '/patches/patch-ipb_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'ipblocks', 'ipb_by',
                        dirname( __FILE__ ) . '/patches/patch-ipb_by-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'logging', 'log_user',
                        dirname( __FILE__ ) . '/patches/patch-log_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'oldimage', 'oi_user',
                        dirname( __FILE__ ) . '/patches/patch-oi_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'protected_titles', 'pt_user',
                        dirname( __FILE__ ) . '/patches/patch-pt_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_user',
                        dirname( __FILE__ ) . '/patches/patch-rc_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'revision', 'rev_user',
                        dirname( __FILE__ ) . '/patches/patch-rev_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'user_former_groups', 'ufg_user',
                        dirname( __FILE__ ) . '/patches/patch-ufg_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'user_groups', 'ug_user',
                        dirname( __FILE__ ) . '/patches/patch-ug_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'user_newtalk', 'user_id',
                        dirname( __FILE__ ) . '/patches/patch-user_newtalk-user_id-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'user_properties', 'up_user',
                        dirname( __FILE__ ) . '/patches/patch-up_user-bigint-unsigned.sql', true ) );
		$updater->addExtensionUpdate( array( 'modifyField', 'watchlist', 'wl_user',
                        dirname( __FILE__ ) . '/patches/patch-wl_user-bigint-unsigned.sql', true ) );
		// Auto-increment one quadrillion
		$updater->addExtensionUpdate( array( 'modifyField', 'logging', 'log_id',
                        dirname( __FILE__ ) . '/patches/patch-log_id-one-quadrillion.sql', true ) );
                $updater->addExtensionUpdate( array( 'modifyField', 'page', 'page_id',
                        dirname( __FILE__ ) . '/patches/patch-page_id-one-quadrillion.sql', true ) );
                $updater->addExtensionUpdate( array( 'modifyField', 'recentchanges', 'rc_id',
                        dirname( __FILE__ ) . '/patches/patch-rc_id-one-quadrillion.sql', true ) );
                $updater->addExtensionUpdate( array( 'modifyField', 'revision', 'rev_id',
                        dirname( __FILE__ ) . '/patches/patch-rev_id-one-quadrillion.sql', true ) );
                $updater->addExtensionUpdate( array( 'modifyField', 'user', 'user_id',
                        dirname( __FILE__ ) . '/patches/patch-user_id-one-quadrillion.sql', true ) );
		$dbw = wfGetDB( DB_MASTER );
		$keys = array(
			'populate rev_parent_id',
			'populate rev_len and ar_len',
		);
		foreach ( $keys as $key ) {
			$dbw->insert( 'updatelog', array( 'ul_key' => $key ), __METHOD__, 'IGNORE' );
		}
                return true;
        }

        // All this need do is collect data from the API function and put it in a global variable
        // for use by other functions
        public static function onAPIEditBeforeSave( $editPage, $text, &$resultArr, $params ) {
                global $wgMirrorEditParams;
                $wgMirrorEditParams = $params;
                return true;
        }

        // When inserting a revision, change a few fields
        public static function onRevisionInsert( &$this, $data, $flags, &$row ) {
                global $wgMirrorEditParams;
                if ( $wgMirrorEditParams ) {
                        $row['rev_timestamp'] = $wgMirrorEditParams['timestamp'];
                        $row['rev_id'] = $wgMirrorEditParams['revid'];
                        $row['rev_user'] = $wgMirrorEditParams['userid'];
                        $row['rev_user_text'] = $wgMirrorEditParams['user'];
                        // TODO: Add code for figuring out parent ID, if necessary
                        // See getPreviousRevisionId if you need to
                }
		return true;
        }

        // TODO: Do something to enable this to handle laggy situations, in which a revision is made
        // to the local wiki before it's made to the remote wiki. Right now it does nothing.
        public static function onUpdateRevisionOn( $dbw, $revision, $lastRevision,
                $lastRevIsRedirect, &$row, &$conditions, &$result ) {
                return true;
        }

        // Use the recent change ID
        public static function onBeforeRecentChangeSave( &$attribs ) {
                global $wgMirrorEditParams;
                if ( $wgMirrorEditParams ) {
                        $attribs['rc_id'] = $wgMirrorEditParams['rcid'];
                }
        }

}
