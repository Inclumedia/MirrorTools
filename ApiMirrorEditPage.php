<?php
/**
 *
 * ApiMirrorEditPage
 * Created on 17 July 2014 by Nathan Larson
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
 * A module that allows for mirroring edits.
 *
 * @ingroup API
 */
class ApiMirrorEditPage extends ApiBase {
	public function execute() {
		$user = $this->getUser();
		if ( !$user->isAllowed( 'mirrortools' ) ) {
			$this->dieUsage(
				'Access denied: This user does not have the mirrortools right' );
		}
		$params = $this->extractRequestParams();
		$params['rctitle'] = str_replace( ' ', '_', $params['rctitle'] );
		// Check sha1
		$sha1 = Revision::base36Sha1( $params['oldtext'] );
		$sha1 = wfBaseConvert( $sha1, 36, 16, 40 );
		if ( $sha1 !== $params['revsha1'] ) {
			$this->dieUsage( "sha1 does not match. Submitted: " . $params['revsha1']
				. "\nShould have been: $sha1" );
		}
		$dbw = wfGetDB( DB_MASTER );
		// See if this data is already in the tables
		$conds = array ( 'rev_id' => $params['revid'] );
		$res = $dbw->selectRow( 'revision', 'rev_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Rev id ' . $params['revid'] .
				' is already in the revision table' );
		}
		$conds = array ( 'rc_id' => $params['rcid'] );
		$res = $dbw->selectRow( 'recentchanges', 'rc_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Rc id ' . $params['rcid'] .
				' is already in the recentchanges table' );
		}
		$this->doMirrorEdit( $params );
	}

	public function doMirrorEdit( $params ) {
		$dbw = wfGetDB( DB_MASTER );
		$params['revsha1'] = Revision::base36Sha1( $params['oldtext'] );
		// See if this page title and namespace are in the page table
		$conds = array (
			'page_title' => $params['rctitle'],
			'page_namespace' => $params['rcnamespace'],
		);
		$res = $dbw->selectRow(
			'page',
			array( 'page_id', 'page_is_new', 'page_latest', 'page_is_redirect',
			      'page_mt_remotely_live' ),
			$conds
		);
		// If the page title and namespace are in the page table, then this page is now
		// under LocalWiki control
		if ( $res ) {
			$pageId = $params['revpage'];
			$pageIsNew = 0;
			$pageIsRedirect = $res->page_is_redirect;
			$readPageIsNew = $res->page_is_new;
			$readPageLatest = $res->page_latest;
			$readPageIsRedirect = $res->page_is_redirect;
			$readPageIsRemotelyLive = $res->page_mt_remotely_live;
			$pageLatest = $readPageLatest;
			// Move all the revisions presently at that page title to the RemoteWiki
			// page ID
			$dbw->update(
				'revision',
				array(
					'rev_page' => $pageId
				),
				array( 'rev_page' => $res->page_id )
			);
			// Change the LocalWiki page_id to the RemoteWiki page_id
			$dbw->update(
				'page',
				array( 'page_id' => $pageId ),
				array( 'page_id' => $res->page_id )
			);
		// If the page title and namespace are not in the page table, then add a new entry
		// to the page table
		} else {
			$pageIsNew = 1;
			$pageIsRedirect = MirrorTools::getRedirectTarget(
				$params['oldtext'] ) ? 1 : 0;
			$pageLatest = $params['revid'];
			$readPageIsNew = 1;
			$readPageIsRedirect = $pageIsRedirect;
			$readPageLatest = $pageLatest;
			$readPageIsRemotelyLive = 1;
			$insertPageArray = array(
				'page_id' => $params['revpage'],
				'page_namespace' => $params['rcnamespace'],
				'page_title' => $params['rctitle'],
				'page_counter' => 0,
				'page_is_redirect' =>  $pageIsRedirect,
				'page_is_new' => 1,
				'page_random' => wfRandom(),
				'page_touched' => $params['revtimestamp'],
				'page_links_updated' => NULL,
				'page_latest' => $params['revid'],
				'page_len' => $params['revlen'],
				'page_content_model' => $params['revcontentmodel'],
				'page_lang' => NULL,
				'page_mt_remotely_live' => 1
			);
			$dbw->insert( 'page', $insertPageArray );
			$pageId = $dbw->insertId();
		}
		$insertTextArray = array(
			'old_text' => $params['oldtext'],
			'old_flags' => $params['oldflags']
		);
		$dbw->insert( 'text', $insertTextArray );
		$oldId = $dbw->insertId();
		$pushTimestamp = wfTimestamp( TS_MW );
		// Insert the mirrored revision
		// TODO: Add MirrorTools::getContentModel() to ApiMirrorX.php
		// (not just to ApiMirrorEditPage.php).
		$params['revcontentmodel']
			= MirrorTools::getContentModel( $params['revcontentmodel'] );
		$insertRevisionArray = array(
			'rev_id' => $params['revid'],
			'rev_page' => $pageId,
			'rev_text_id' => $oldId,
			'rev_comment' => $params['revcomment'],
			'rev_user' => 0,
			'rev_user_text' => $params['revusertext'],
			'rev_timestamp' => $params['revtimestamp'],
			'rev_minor_edit' => $params['revminoredit'],
			'rev_deleted' => $params['revdeleted'],
			'rev_len' => $params['revlen'],
			'rev_parent_id' => $params['rclastoldid'],
			'rev_sha1' => $sha1,
			'rev_content_model' => $params['revcontentmodel'],
			#'rev_content_format' => $params['revcontentformat'],
			'rev_mt_user' => $params['revuser'],
			'rev_mt_push_timestamp' => $pushTimestamp,
		);
		$dbw->insert( 'revision', $insertRevisionArray );
		$revId = $dbw->insertId();
		// Update page_latest and/or page_is_new and/or page_is_redirect
		// Start by figuring out what the page_latest should be. This had to wait until
		// after the revision was inserted, because the latest revision could be the one
		// that was just inserted.
		if ( !$pageIsNew ) {
			$pageLatest = $dbw->selectField(
				'revision',
				'rev_id',
				array( 'rev_page' => $pageId ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp DESC' )
			);
			if ( $readPageLatest != $pageLatest ) {
				// The new revision must be the latest revision, so see if it's a
				// redirect
				$pageIsRedirect = MirrorTools::getRedirectTarget(
					$params['oldtext'] ) ? 1 : 0;
			}
		}
		// If there's any data in the page entry to update, then update it
		if ( $readPageLatest != $pageLatest
			|| $readPageIsNew != $pageisNew
			|| $readPageIsRedirect != $pageisRedirect
			|| !$readPageIsRemotelyLive
		) {
			$dbw->update(
				'page',
				array(
				      'page_latest' => $pageLatest,
				      'page_is_new' => $pageIsNew,
				      'page_is_redirect' => $pageIsRedirect,
				      'page_mt_remotely_live' => 1
				),
				array( 'page_id' => $pageId )
			);
		}
		// Insert recentchanges and tags entries, unless rcid param is set to zero
		if ( $params['rcid'] ) {
			$insertRecentchangesArray = array(
				'rc_id' => $params['rcid'],
				'rc_timestamp' => $params['revtimestamp'],
				'rc_user' => 0,
				'rc_user_text' => $params['revusertext'],
				'rc_namespace' => $params['rcnamespace'],
				'rc_title' => $params['rctitle'],
				'rc_comment' => $params['revcomment'],
				'rc_minor' => $params['revminoredit'],
				'rc_bot' => $params['rcbot'],
				'rc_new' => $params['rcnew'],
				'rc_cur_id' => $pageId,
				'rc_this_oldid' => $params['revid'],
				'rc_last_oldid' => $params['rclastoldid'],
				'rc_type' => $params['rcnew'] ? 1 : 0,
				'rc_source' => $params['rcsource'],
				'rc_patrolled' => $params['rcpatrolled'],
				'rc_ip' => $params['rcip'],
				'rc_old_len' => $params['rcoldlen'],
				'rc_new_len' => $params['revlen'],
				'rc_deleted' => $params['revdeleted'],
				'rc_logid' => 0,
				'rc_log_type' => NULL,
				'rc_log_action' => '',
				'rc_params' => '',
				'rc_mt_push_timestamp' => $pushTimestamp,
				'rc_mt_user' => $params['revuser']
			);
			$dbw->insert( 'recentchanges', $insertRecentchangesArray );
		}
		if ( $params['tstags'] ) {
			$insertTagsummaryArray = array(
				'ts_rc_id' => $params['rcid'],
				'ts_rev_id' => $params['revid'],
				'ts_tags' => $params['tags']
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
		return 'Mirror log entries.';
	}

	public function getAllowedParams() {
		return array(
			'revid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'revpage' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'revcomment' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'revuser' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'revusertext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'revtimestamp' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_REQUIRED => true
			),
			'revminoredit' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'revdeleted' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'revlen' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'revsha1' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'revcontentmodel' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'revcontentformat' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => NULL
			),
			'rcid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcnamespace' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rctitle' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcbot' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcnew' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rctype' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcsource' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'rcpatrolled' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcip' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'rclastoldid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0
			),
			'rcoldlen' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0
			),
			'oldtext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'oldflags' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 'utf-8'
			),
			'tstags' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
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