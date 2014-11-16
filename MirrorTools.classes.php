<?php

class MirrorTools {
        public static function SchemaUpdates( $updater ) {
		// bigint updates
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
		// Remote wiki page ID
		$updater->addExtensionUpdate( array( 'addField', 'revision', 'rev_mt_ar_page_id',
                        dirname( __FILE__ ) . '/patches/patch-rev_mt_ar_page_id.sql', true ) );
		// Remote wiki user ID for log event
		$updater->addExtensionUpdate( array( 'addField', 'logging', 'log_mt_user',
                        dirname( __FILE__ ) . '/patches/patch-log_mt_user.sql', true ) );
		// Remote wiki user ID for recentchanges event
		$updater->addExtensionUpdate( array( 'addField', 'recentchanges', 'rc_mt_user',
                        dirname( __FILE__ ) . '/patches/patch-rc_mt_user.sql', true ) );
		// Remote wiki user ID for revision
		$updater->addExtensionUpdate( array( 'addField', 'revision', 'rev_mt_user',
                        dirname( __FILE__ ) . '/patches/patch-rev_mt_user.sql', true ) );
		// Does the page have revisions that are live on the remote wiki? (if not, then 0)
		$updater->addExtensionUpdate( array( 'addField', 'page', 'page_mt_remotely_live',
                        dirname( __FILE__ ) . '/patches/patch-page_mt_remotely_live.sql', true ) );
		// Timestamp the log entry was mirrorpushed
		$updater->addExtensionUpdate( array( 'addField', 'logging', 'log_mt_push_timestamp',
                        dirname( __FILE__ ) . '/patches/patch-log_mt_push_timestamp.sql', true ) );
		// Timestamp the recent change was mirrorpushed
		$updater->addExtensionUpdate( array( 'addField', 'recentchanges', 'rc_mt_push_timestamp',
                        dirname( __FILE__ ) . '/patches/patch-rc_mt_push_timestamp.sql', true ) );
		// Timestamp the revision was mirrorpushed
		$updater->addExtensionUpdate( array( 'addField', 'revision', 'rev_mt_push_timestamp',
                        dirname( __FILE__ ) . '/patches/patch-rev_mt_push_timestamp.sql', true ) );
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

	public static function enhancedChangesListMainlineRecentChangesFlags( $rcObj, &$flags ) {
		if ( $rcObj->mAttribs['rc_mt_push_timestamp'] ) {
			$flags['mirrored'] = true;
		}
		return true;
	}

	public static function enhancedChangesListSubentryRecentChangesFlags( $rcObj, &$flags ) {
		if ( $rcObj->mAttribs['rc_mt_push_timestamp'] ) {
			$flags['mirrored'] = true;
		}
		return true;
	}

	public static function oldChangesListRecentChangesFlags( $rcObj, &$flags ) {
		if ( $rcObj->mAttribs['rc_mt_push_timestamp'] ) {
			$flags['mirrored'] = true;
		}
		return true;
	}

	public static function oldChangesListLogFlags( $rcObj, &$flags ) {
		if ( $rcObj->mAttribs['rc_mt_push_timestamp'] ) {
			$flags['mirrored'] = true;
		}
		return true;
	}

	public static function historyLineFlags( $row, &$space, &$s ) {
		if ( $row->rev_mt_push_timestamp ) {
			$space = false;
			$s .= ChangesList::flag( 'mirrored' );
		}
		return true;
	}

	public static function showDiffPageOldMinor( $rev, &$oldminor ) {
		$attributes = $rev->getAttributes();
		if ( $attributes['rev_mt_push_timestamp'] ) {
			$oldminor = ChangesList::flag( 'mirrored' ) . $oldminor;
		}
		return true;
	}

	public static function showDiffPageNewMinor( $rev, &$newminor ) {
		$attributes = $rev->getAttributes();
		if ( isset( $attributes['rev_mt_push_timestamp'] )  &&
			$attributes['rev_mt_push_timestamp'] ) {
			$newminor = ChangesList::flag( 'mirrored' ) . $newminor;
		}
		return true;
	}

	public static function recentChangesFields( &$fields ) {
		$fields[] = 'rc_mt_push_timestamp';
		return true;
	}

	public static function enhancedChangesBlockLineFlags( $rcObj, &$flags ) {
		if ( $rcObj->mAttribs['rc_mt_push_timestamp'] ) {
			$flags['mirrored'] = true;
		}
		return true;
	}

	public static function revisionAttribs( $row, &$attribs ) {
		if ( isset( $row->rev_mt_push_timestamp ) ) {
			$attribs['rev_mt_push_timestamp'] = $row->rev_mt_push_timestamp;
		}
		return true;
	}

	public static function revisionSelectFields( &$fields ) {
		$fields[] = 'rev_mt_push_timestamp';
		return true;
	}

	public static function getRedirectTarget( $text ) {
		$redir = MagicWord::get( 'redirect' );
		if ( $redir->matchStartAndRemove( $text ) ) {
			$m = array();
			if ( preg_match( '!^\s*:?\s*\[{2}(.*?)(?:\|.*?)?\]{2}\s*!',
				$text, $m ) ) {
				if ( strpos( $m[1], '%' ) !== false ) {
					$m[1] = rawurldecode( ltrim( $m[1], ':' ) );
				}
				if ( $m[1] ) {
					return $m[1];
				} else {
					return false;
				}
			}
		}
	}

	public static function getContentModel( $text ) {
		if ( $text === 'wikitext' ) {
			$text = NULL;
		}
		return $text;
	}

	public static function onArticlePageDataBefore( $article, $fields ) {
		$fields[] = 'page_mt_remotely_live';
	}

	public static function onArticlePageDataAfter( $article, $row ) {
		global $wgMirrorToolsPageRemotelyLive;
		if ( isset( $row->page_mt_remotely_live ) ) {
			$wgMirrorToolsPageRemotelyLive = $row->page_mt_remotely_live;
		}
	}

	public static function onSkinTemplateNavigation( SkinTemplate &$sktemplate,
		array &$links ) {
		global $wgMirrorToolsEditRemoteWikiUrl,
			$wgMirrorToolsMoveRemoteWikiUrl,
			$wgMirrorToolsPageRemotelyLive;
		// Display the regular tabs if the page isn't remotely live
		if ( !$wgMirrorToolsPageRemotelyLive ) {
			return true;
		}
		$request = $sktemplate->getRequest();
		$action = $request->getText( 'action' );
		$links['views']['editremotely'] = array(
			'class' => ( $action == 'editremotely') ? 'selected' : false,
			'text' => wfMessage( 'editonremotewiki' )->plain(),
			'href' => str_replace( '$1', $sktemplate->getTitle()->getFullText(),
				$wgMirrorToolsEditRemoteWikiUrl )
		);
		$links['views']['moveremotely'] = array(
			'class' => ( $action == 'moveremotely') ? 'selected' : false,
			'text' => wfMessage( 'moveonremotewiki' )->plain(),
			'href' => str_replace( '$1', $sktemplate->getTitle()->getFullText(),
				$wgMirrorToolsMoveRemoteWikiUrl )
		);
		unset( $links['actions']['protect'] );
		unset( $links['views']['edit'] );
		return true;
	}

	public static function getMoveTo( $moveToNamespace, $moveToTitle ) {
		global $wgNamespacesToTruncate;
		foreach( $wgNamespacesToTruncate as $namespaceToTruncate ) {
			if ( substr( $prefixedMoveTo, 0, strlen( $namespaceToTruncate ) )
				== $namespaceToTruncate ) {
				$moveToTitle = substr( $prefixedMoveTo,
				    strlen( $namespaceToTruncate ),
				    strlen( $prefixedMoveTo )
				    - strlen( $namespaceToTruncate ) );
				$moveToNamespace = $namespaceToTruncate;
				break;
			}
                }
		return array(
			'namespace' => $moveToNamespace,
			'title' => $moveToTitle
		);
	}

	// Abort a locally-initiated page move if either the source or destination title is
	// remotely live
	public static function onAbortMove( Title $oldTitle, Title $newTitle, User $user,
		&$error, $reason ) {
		$dbw = wfGetDB( DB_MASTER );
		$oldRemotelyLive = $dbr->selectField(
			'page',
			'page_mt_remotely_live',
			array( 'page_id' => $oldTitle->getArticleID() )
		);
		if ( $oldRemotelyLive ) {
			$error = wfMessage( 'cant-move-from-remotely-live-page' );
			return false;
		}
		$newRemotelyLive = $dbr->selectField(
			'page',
			'page_mt_remotely_live',
			array(
			      'page_namespace' => $newTitle->getNamespace(),
			      'page_title' => $newTitle->getDBKey()
			)
		);
		if ( $newRemotelyLive ) {
			$error = wfMessage( 'cant-move-to-remotely-live-page' );
			return false;
		}
	}

	public static function onTitleMoveComplete( &$title, &$newtitle, &$user, $oldid, $newid,
		$reason ) {
		$pushTimestamp = wfTimestamp( TS_MW );
		// When a move is made from a page that has remotely deleted revisions, create a
		// new page entry for those revisions.
		$dbw = wfGetDB( DB_MASTER );
		// First see if there are remotely deleted revisions at that source page
		$options = $newid ? array( 'ORDER BY' => 'rev_timestamp DESC' ) : array();
		$latestTimestamp = $dbw->selectField(
			'revision',
			array( 'rev_timestamp' ),
			array(
			      'rev_page' => $oldid,
			      'rev_ar_page_id<>NULL'
			), __METHOD__,
			$options
		);

		if ( $latestTimestamp ) {
			// If there are indeed remotely deleted revision at that source page, and
			// if no redirect was created, then get the latest revision and use that
			// rev_id as the page_latest.
			if( !$newid ) {
				// Since we already have the timestamp, now sort descending by
				// rev_id.
				$latestRow = $dbw->selectRow(
					'revision',
					array(
						'rev_id',
						'rev_timestamp',
						'rev_text_id',
						'rev_content_model'
					), array(
						'rev_page' => $oldid,
						'rev_ar_page_id<>NULL',
						'rev_timestamp' => $latestTimestamp,
					), __METHOD__,
					array( 'ORDER BY' => 'rev_id DESC' )
				);
				// Get the text of the latest revision, to see if it's a redirect
				// TODO: Make this compatible with external storage
				$text = $dbw->selectField(
					'text',
					'old_text',
					array( 'old_id' => $latestRow->rev_text_id )
				);
				// Create the new page
				$dbw->insert(
					'page',
					array(
						'page_namespace' => $title->getNamespace(),
						'page_title' => $title->getDBKey(),
						'page_is_redirect' =>
							MirrorTools::getRedirectTarget( $text ),
						'page_is_new' => 1,
						'page_random' => wfRandom(),
						'page_touched' => $pushTimestamp,
						'page_links_updated' => NULL,
						'page_latest' => $latestRow->rev_id,
						'page_len' => strlen( $text ),
						'page_content_model' =>
							$latestRow->rev_content_model
					)
				);
				$insertId = $dbw->insertId();
			}
			// Set those mirrordeleted revisions' rev_id to either the insert ID or
			// the redirect page_id.
			$dbw->update(
				'revision',
				array( 'rev_page' => $newid ? $newid : $insertId ),
				array(
				      'rev_page' => $oldId,
				      'rev_ar_page_id<>NULL'
				)
			);
		}
	}
}