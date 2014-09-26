<?php
/**
 *
 * ApiMirrorMove
 * Created on 22 July 2014 by Nathan Larson
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

/**
 * A module that allows for mirroring page moves.
 *
 * @ingroup API
 */
class ApiMirrorMove extends ApiBase {
	public function execute() {
		global $wgNamespacesToTruncate;
		$user = $this->getUser();
		if ( !$user->isAllowed( 'mirrortools' ) ) {
			$this->dieUsage(
				'Access denied: This user does not have the mirrortools right' );
		}
		$params = $this->extractRequestParams();
		$dbw = wfGetDB( DB_MASTER );
		$pushTimestamp = wfTimestamp( TS_MW );
		// See if this data is already in the tables
		// Is it in the logging table?
		$conds = array ( 'log_id' => $params['logid'] );
		$res = $dbw->selectRow( 'logging', 'log_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Log id ' . $params['logid']
				. ' is already in the logging table' );
		}
		// Is it in the recentchanges table?
		$conds = array ( 'rc_id' => $params['rcid'] );
		$res = $dbw->selectRow( 'recentchanges', 'rc_id', $conds );
		if ( RecentChange::newFromId( $params['rcid'] ) ) {
			$this->dieUsage( 'Rc id ' . $params['rcid'] .
				' is already in the recentchanges table' );
		}
		// Get the target (i.e. move to) page title and namespace
		$logParams = unserialize( $params['logparams'] );
		$prefixedMoveTo = $logParams['4::target'];
		$noRedir = $logParams['5::noredir'] == '1' ? true : false;
		// Figure out what namespace it's being moved to; truncate any namespace prefix
		$moveToArr = MirrorTools::getMoveTo( 0, $prefixedMoveTo );
		$moveToNamespace = $moveToArr['namespace'];
		$moveToTitle = $moveToArr['title'];
		// If there's a merged history of local and/or mirrorpagedeleted revisions and
		// remote revisions at the source page, change the rev_page of the local
		// revisions to the page ID of the redirect or the next available page_id (if
		// the redirect was suppressed).

		// First, let's see if there is such a merged history.
		$localSourcePageExists = false;
		$res = $dbw->selectField(
			'revision',
			'rev_id',
			array(
				'rev_page' => $params['logpage'],
				'(rev_mt_push_timestamp=NULL OR rev_mt_ar_page_id<>0)'
			)
		);
		// Crappy hack to temporarily store and delete the current page to make way for
		// the new one; it'll be added back later
		if ( $res || !$noRedir ) {
			$tempRow = $dbw->selectRow(
				'page',
				'*',
				array( 'page_id' => $params['logpage'] )
			);
			$dbw->delete( 'page', array( 'page_id' => $params['logpage'] ) );
		}

		// If so, let's create that page, using the redirect page ID, if it's available.
		if ( $res ) {
			$localSourcePageExists = true;
			$conds = array(
				'page_id' => $params['redirectpageid'],
				'page_namespace' => $params['lognamespace'],
				'page_title' => $params['logtitle'],
				'page_counter' => 0,
				// TODO: Perhaps fix this using WikitextContent's
				// getRedirectTargetAndText()
				'page_is_redirect' => 0,
				#'page_is_new' => $dbw->affectedRows() > 1 ? 0 : 1,
				'page_random' => wfRandom(),
				'page_touched' => $pushTimestamp,
				'page_links_updated' => NULL,
				'page_latest' => 0,
				'page_len' => 0
			);
			$dbw->insert( 'page', $conds );
			$newSourcePageRevPage = $params['redirectpageid']
				? $params['redirectpageid']
				: $dbw->insertId();
			$res = $dbw->update(
				'revision',
				array(
					'rev_page' => $newSourcePageRevPage,
				),
				array(
					'rev_page' => $params['logpage'],
					'(rev_mt_push_timestamp=NULL OR rev_mt_ar_page_id<>0)'
				)
			);
			// To find the page_latest, sort descending by timestamp and then rev_id.
			$latestRow = $dbw->selectRow(
				'revision',
				array(
					'rev_id',
					'rev_content_model',
					'rev_len',
				),
				array( 'rev_page' => $newSourcePageRevPage ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp DESC, rev_id DESC' )
			);
			$dbw->update(
				'page',
				array(
					'page_id' => $newSourcePageRevPage,
					#'page_is_new' => $dbw->affectedRows() > 1 ? 0 : 1,
					'page_latest' => $latestRow->rev_id,
					'page_len' => $latestRow->rev_len,
					'page_content_model' => $latestRow->rev_content_model,
					'page_lang' => NULL
				)
			);
			$localSourcePageExists = true;
		}
		// Does a page already exist at this target page title and namespace?
		$alreadyExistingTargetPageId = $dbw->selectField(
			'page',
			'page_id',
			array( 
				'page_namespace' => $moveToNamespace,
				'page_title' => $moveToTitle
			)
		);
		if ( $alreadyExistingTargetPageId ) {
			// If there's only one remotely live revision at the target, and it's a
			// redirect, delete it.
			$numRevsRes = $dbw->select(
				'revision',
				'rev_id',
				array(
					'rev_page' => $alreadyExistingTargetPageId,
					'rev_mt_ar_page_id' => 0
				)
			);
			$numRevs = $dbw->numRows( $numRevsRes );
			if ( $numRevs == 1 ) {
				$numRevsRevId = $numRevsRes[1]->rev_id;
				$numRevsRev = Revision::newFromId( $numRevsRevId );
				$content = $numRevsRev->getContent();
				$text = ltrim( $content->getNativeData() );
				$prefixedMoveFrom =
					$wgNamespacesToTruncate[$params['lognamespace']]
					. $params['logtitle'];
				if ( MirrorTools::getRedirectTarget( $text )
					== $prefixedMoveFrom ) {
					$dbw->delete( 'revision',
					array( 'rev_id' => $numRevsRevId )
					);
				}
			}
			// A local page already exists at the target page title and namespace.
			// Make any remotely live revisions that are at that target page not
			// remotely live anymore. Change the rev_page of the revisions that
			// already exist at the target to the page ID of the mirrored page. Also
			// set the rev_mt_ar_page in case there's another mirrormove
			// reversing this mirrormove.

			// Take care of the remote revisions; these need a rev_mt_ar_page_id
			$dbw->update(
				'revision',
				array(
					'rev_page' => $params['logpage'],
					'rev_mt_ar_page_id' => $alreadyExistingTargetPageId
				),
				array(
				      'rev_page' => $alreadyExistingTargetPageId,
				      'rev_timestamp<>NULL'
				)
			);
			// Take care of the local revisions
			$dbw->update(
				'revision',
				array(
					'rev_page' => $params['logpage']
				),
				array(
				      'rev_page' => $alreadyExistingTargetPageId,
				      'rev_timestamp=NULL'
				)
			);
			// Delete the page table entry for the target page.
			$dbw->delete(
				'page',
				array( 'page_id' => $alreadyExistingTargetPageId )
			);
		}
		// Create null revision, if we have the null revision ID
		if ( $params['nullrevid'] ) {
			$dbw->insert(
				'text',
				array(
					'old_text' => $params['oldtext'],
					'old_flags' => 'utf-8'
				)
			);
			$textId = $dbw->insertId();
			$dbw->insert( 'revision', array(
				'rev_id' => $params['nullrevid'],
				'rev_page' => $params['logpage'],
				'rev_text_id' => $textId,
				'rev_comment' => $params['comment2'],
				'rev_user' => 0,
				'rev_user_text' => $params['logusertext'],
				'rev_timestamp' => $params['logtimestamp'],
				'rev_minor_edit' => 1,
				'rev_deleted' => 0,
				'rev_len' => strlen( $oldtext ),
				'rev_parent_id' => $params['nullrevparentid'],
				'rev_sha1' => Revision::base36Sha1( $oldtext ),
				#'rev_content_model' => $mostRecentRevisionRow->rev_content_model,
				#'rev_content_format' => $mostRecentRevisionRow->rev_content_format,
				'rev_mt_push_timestamp' => $pushTimestamp,
				'rev_mt_user' => $params['loguser']
			) );
		}
		// To find the page_latest, sort descending by timestamp and then rev_id.
		$latestRow = $dbw->selectRow(
			'revision',
			array(
				'rev_id',
				'rev_content_model',
				'rev_len',
			),
			array( 'rev_page' => $params['logpage'] ),
			__METHOD__,
			array( 'ORDER BY' => 'rev_timestamp DESC, rev_id DESC' )
		);
		// Change page namespace and title of mirrored source (move from) page
		if ( isset( $tempRow ) ) {
			// Crappy hack; we deleted it earlier to avoid a conflict, so now bring it
			// back
			$dbw->insert(
				'page',
				array(
					'page_id' => $params['logpage'],
					'page_namespace' => $moveToNamespace,
					'page_title' => $moveToTitle,
					'page_is_redirect' => $tempRow->page_is_redirect,
					'page_is_new' => $tempRow->page_is_new,
					'page_random' => $tempRow->page_random,
					'page_touched' => $tempRow->page_touched,
					'page_links_updated' => $tempRow->page_links_updated,
					'page_latest' => $latestRow->rev_id,
					'page_len' => $latestRow->rev_len,
					'page_content_model' => $tempRow->page_content_model,
					'page_lang' => $tempRow->page_lang,
				)
			);
		} else {
			$dbw->update(
				'page',
				array(
					'page_namespace' => $moveToNamespace,
					'page_title' => $moveToTitle,
					'page_latest' => $latestRow->rev_id,
					'page_len' => $latestRow->rev_len,
				),
				array(
					'page_id' => $params['logpage']
				)
			);
		}
		// Create a redirect, if applicable
		if ( !$noRedir && $params['redirectrevid'] && $params['redirectpageid'] ) {
			// If there's already local stuff remaining at the source page, then put
			// the redirect in the appropriate place in that page entry. This may or
			// may not be the latest revision, since who knows how far behind mirroring
			// is.
			$parentId = 0;
			$redirectPageId = 0;
			$redirRevIsLatestRev = false;
			$oldText2 = $params['oldtext2'];
			if( $localSourcePageExists ) {
				$redirectPageId = $revpage;
				// Find out whether the redirect revision is the latest revision
				$latestTimestamp = $dbw->selectField(
					'revision',
					array( 'rev_timestamp' ),
					array( 'rev_page' => $newSourcePageRevPage ),
					__METHOD__,
					array( 'ORDER BY' => 'rev_timestamp DESC' )
				);
				$latestTimestamp = $latestRow->rev_timestamp;
				if ( $latestTimestamp < $params['logtimestamp'] ) {
					$redirRevIsLatestRev = true;
				}
			} else {
				// Otherwise, create a new page entry for the redirect revision
				$conds = array(
					'page_namespace' => $params['lognamespace'],
					'page_title' => $params['logtitle'],
					'page_counter' => 0,
					// TODO: Perhaps fix this using WikitextContent's
					// getRedirectTargetAndText()
					'page_is_redirect' => 1,
					'page_is_new' => 1,
					'page_random' => wfRandom(),
					'page_touched' => $pushTimestamp,
					'page_links_updated' => NULL,
					'page_latest' => 0,
					'page_len' => strlen( $oldText2 ),
					'page_content_model' => $row->rev_content_model,
					'page_lang' => NULL,
					'page_mt_remotely_live' => 1
				);
				if ( $params['redirectpageid'] ) {
					$conds['page_id'] = $params['redirectpageid'];
				} elseif ( $newSourcePageRevPage ) {
					$conds['page_id'] = $newSourcePageRevPage;
				}
				$dbw->insert(
					'page',
					$conds
				);
				$redirectPageId = $conds['page_id'] ? $conds['page_id']
					: $dbw->insertId();
				// Since the page only has one revision (viz. the redirect
				// revision), the redirect revision must be the latest revision
				$redirRevIsLatestRev = true;
			}
			// Insert redirect text entry
			$dbw->insert(
				'text',
				array(
					'old_text' => $oldText2,
					'old_flags' => 'utf-8'
				)
			);
			$textId = $dbw->insertId();
			// Insert redirect revision entry
			$conds = array(
				'rev_id' => $params['redirectrevid'],
				'rev_page' => $params['redirectpageid'],
				'rev_text_id' => $textId,
				'rev_comment' => $params['comment2'],
				'rev_user' => 0,
				'rev_user_text' => $params['logusertext'],
				'rev_timestamp' => $params['logtimestamp'],
				'rev_minor_edit' => 0,
				'rev_deleted' => 0,
				'rev_len' => strlen( $oldText2 ),
				'rev_parent_id' => 0,
				'rev_sha1' => Revision::base36Sha1( $oldText2 ),
				'rev_mt_user' => $params['loguser'],
				'rev_mt_push_timestamp' => $pushTimestamp
			);
			$dbw->insert( 'revision', $conds );
			$redirectRevisionId = $params['redirectrevid'];
			// Set page_latest to the redirect revision entry, if that's the latest
			// revision
			if ( $redirRevIsLatestRev ) {
				$dbw->update(
					'page',
					array(
					      'page_latest' => $redirectRevisionId,
					      'page_is_redirect' => 1,
					      'page_content_model' => 'wikitext'
					),
					array( 'page_id' => $params['redirectpageid'] )
				);
			}
		}
		// Insert log entry
		$insertLoggingArray = array(
			'log_id' => $params['logid'],
			'log_type' => 'move',
			'log_action' => $params['logaction'],
			'log_timestamp' => $params['logtimestamp'],
			'log_user' => 0,
			'log_namespace' => $params['lognamespace'],
			'log_deleted' => $params['logdeleted'],
			'log_user_text' => $params['logusertext'],
			'log_title' => $params['logtitle'],
			'log_comment' => $params['logcomment'],
			'log_params' => $params['logparams'],
			'log_page' => $params['logpage'],
			'log_mt_user' => $params['loguser'],
			'log_mt_push_timestamp' => $pushTimestamp
		);
		$dbw->insert( 'logging', $insertLoggingArray );
		// Insert recentchanges entry
		$insertRecentchangesArray = array(
			'rc_id' => $params['rcid'],
			'rc_timestamp' => $params['logtimestamp'],
			'rc_user' => 0,
			'rc_user_text' => $params['logusertext'],
			'rc_namespace' => $params['lognamespace'],
			'rc_title' => $params['logtitle'],
			'rc_comment' => $params['logcomment'],
			'rc_minor' => 0,
			'rc_bot' => $params['rcbot'],
			'rc_new' => 0,
			'rc_cur_id' => $params['logpage'],
			'rc_this_oldid' => 0,
			'rc_last_oldid' => 0,
			'rc_type' => 3,
			'rc_source' => 'mw.log',
			'rc_patrolled' => $params['rcpatrolled'],
			#'rc_ip' => $params['rcip'], // Let's just let it go to default
			'rc_old_len' => NULL,
			'rc_new_len' => NULL,
			'rc_deleted' => $params['logdeleted'],
			'rc_logid' => $params['logid'],
			'rc_log_type' => 'move',
			'rc_log_action' => $params['logaction'],
			'rc_params' => $params['logparams'],
			'rc_mt_push_timestamp' => $pushTimestamp,
			'rc_mt_user' => $params['loguser']
		);
		$dbw->insert( 'recentchanges', $insertRecentchangesArray );
		// Insert tags entry
		if ( $params['tstags'] ) {
			$insertTagsummaryArray = array(
				'ts_log_id' => $params['logid'],
				'ts_rc_id' => $param['rcid'],
				'ts_tags' => $params['tstags']
			);
			$dbw->insert( 'tag_summary', $insertTagsummaryArray );
		}
		$r = array();
		$r['result'] = 'Success';
		$r['timestamp'] = $pushTimestamp;
		$this->getResult()->addValue( null, $this->getModuleName(), $r );
		return true;
	}

	public function getDescription() {
		return 'Mirror page moves.';
	}

	public function getAllowedParams() {
		return array(
			'logid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'logtimestamp' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_REQUIRED => true
			),
			'loguser' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'lognamespace' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'logdeleted' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0
			),
			'logusertext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'logtitle' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'logcomment' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'logparams' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'logpage' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'logaction' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'rcid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'rcbot' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'rcpatrolled' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'rcip' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => '',
			),
			'tstags' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'comment2' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'nullrevid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => NULL
			),
			'nullrevparentid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => NULL
			),
			'redirectrevid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => NULL
			),
			'redirectpageid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => NULL
			),
			'oldtext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => NULL
			),
			'oldtext2' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => NULL
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:MirrorTools/MirrorLogEntry';
	}

	public function mustBePosted() {
		return true;
	}
	
	public function needsToken() {
		return true;
	}
	
	public function getTokenSalt() {
		return '';
	}
}
