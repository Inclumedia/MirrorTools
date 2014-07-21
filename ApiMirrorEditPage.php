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
 * A module that allows for creating users.
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
		$sha = Revision::base36Sha1( $params['oldtext'] );
		if ( $sha1 != $params['sha1'] ) {
			$this->dieUsage( "sha1 does not match. Submitted: " . $params['revsha1']
				. "Should have been: $sha1" );
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
		// See if this page title and namespace are in the page table
		$conds = array (
			'page_title' => $params['rctitle'],
			'page_namespace' => $params['rcnamespace'],
		);
		$res = $dbw->selectRow( 'page', array( 'page_id', 'page_is_new' )
			, $conds );
		$parentId = 0;
		$childId = 0;
		$readPageIsNew = 0;
		$pageIsNew = 0;
		if ( $res ) { // If so, get that page ID and find what will be the parent and
			// child revisions. First, the parent revision...
			$pageId = $res->page_id;
			$readPageIsNew = $res->page_is_new;
			$vars = array( 'maxrevtimestamp' => 'MAX(rev_timestamp)', 'rev_id', 'rev_len' );
			$conds = array(
				"rev_timestamp < " . $params['revtimestamp'],
				'rev_page' => $pageId
			);
			$res = $dbw->selectRow( 'revision', $vars, $conds );
			
			if ( $res ) {
				$parentId = $res->rev_id;
				$oldLen = $res->rev_len;
			}
			// Now, the child revision...
			$vars = array( 'minrevtimestamp' => 'MIN(rev_timestamp)', 'rev_id' );
			$conds = array(
				"rev_timestamp > " . $params['revtimestamp'],
				'rev_page' => $pageId
			);
			$res = $dbw->selectRow( 'revision', $vars, $conds );
			if ( $res ) {
				$childId = $res->rev_id;
			}
		} else { // If not, add a new entry to the page table
			$pageIsNew = 1;
			$readPageIsNew = 1;
			$insertPageArray = array(
				'page_id' => $params['revpage'],
				'page_namespace' => $params['rcnamespace'],
				'page_title' => $params['rctitle'],
				'page_counter' => 0,
				// TODO: Perhaps fix this using WikitextContent's
				// getRedirectTargetAndText()
				'page_is_redirect' => 0,
				'page_is_new' => 1,
				'page_random' => wfRandom(),
				'page_touched' => $params['revtimestamp'],
				'page_links_updated' => $params['revtimestamp'],
				'page_latest' => $params['revid'],
				'page_len' => $params['revlen'],
				'page_content_model' => $params['revcontentmodel'],
				'page_lang' => NULL
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
			'rev_parent_id' => $parentId,
			'rev_sha1' => $params['revsha1'],
			'rev_content_model' => $params['revcontentmodel'],
			'rev_content_format' => $params['revcontentformat'],
			'rev_mt_page' => $params['revpage'],
			'rev_mt_user' => $params['revuser']
		);
		$dbw->insert( 'revision', $insertRevisionArray );
		$revId = $dbw->insertId();
		// Change the child revision to point to this one
		if ( $childId ) {
			$dbw->update(
				'revision',
				array( 'rev_parent_id' => $revId ),
				array( 'rev_id' => $childId )
			);
		}
		// Update page_latest and/or page_is_new
		if ( $parentId && !$childId ) {
			$conds = array(
				'page_latest' => $params['revid'],
			);
			if ( $readPageIsNew ) {
				$conds['page_is_new'] = 0;
			}
			$dbw->update(
				'page',
				$conds,
				array( 'page_id' => $pageId )
			);
		// Update page_is_new
		} elseif ( $readPageIsNew && !$pageIsNew ) {
			$conds['page_is_new'] = 0;
			$dbw->update(
				'page',
				$conds,
				array( 'page_id' => $pageId )
			);
		}
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
			'rc_last_oldid' => $parentId,
			'rc_type' => $params['rctype'],
			'rc_source' => $params['rcsource'],
			'rc_patrolled' => $params['rcpatrolled'],
			'rc_ip' => $params['rcip'],
			'rc_old_len' => $oldLen,
			'rc_new_len' => $params['revlen'],
			'rc_deleted' => $params['revdeleted'],
			'rc_logid' => 0,
			'rc_log_type' => NULL,
			'rc_log_action' => '',
			'rc_params' => ''
		);
		$dbw->insert( 'recentchanges', $insertRecentchangesArray );
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
		$r['timestamp'] = wfTimestamp( TS_MW );
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
				ApiBase::PARAM_REQUIRED => true
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
				ApiBase::PARAM_REQUIRED => true
			),
			'revcontentmodel' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'revcontentformat' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
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
			'oldtext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
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