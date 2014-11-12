<?php
/**
 *
 * ApiMirrorLogEntry
 * Created on 13 July 2014 by Nathan Larson
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
 * A module that allows for mirroring log entries.
 *
 * @ingroup API
 */
class ApiMirrorLogEntry extends ApiBase {
	public function execute() {
		$user = $this->getUser();
		if ( !$user->isAllowed( 'mirrortools' ) ) {
			$this->dieUsage( 'Access denied: This user does not have the mirrortools right' );
		}
		ApiMirrorLogEntry::doLogEntryEntry();
	}

	public function doLogEntry( $params = null, $pushTimestamp = null, $returnResult = false ) {
		if ( !$params ) {
			$params = $this->extractRequestParams();
		}
		if ( !$pushTimestamp ) {
			$pushTimestamp = wfTimestamp( TS_MW );
		}
		$dbw = wfGetDB( DB_MASTER );
		// See if this data is already in the tables
		$conds = array ( 'log_id' => $params['logid'] );
		$res = $dbw->selectRow( 'logging', 'log_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Log id ' . $params['logid'] . ' is already in the logging table' );
		}
		if ( $params['rcid'] ) {
			$conds = array ( 'rc_id' => $params['rcid'] );
			$res = $dbw->selectRow( 'recentchanges', 'rc_id', $conds );
			if ( $res ) {
				$this->dieUsage( 'Rc id ' . $params['rcid'] .
					' is already in the recentchanges table' );
			}
		}
		if ( $params['nullrevid'] ) {
			$res = $dbw->selectRow(
				'revision',
				'rev_id',
				array( 'rev_id' => $params['nullrevid'] )
			);
			if ( $res ) {
				$this->dieUsage( 'Rev id ' . $params['nullrevid'] .
					' is already in the revision table' );
			}
		}
		$insertLoggingArray = array(
			'log_id' => $params['logid'],
			'log_type' => $params['logtype'],
			'log_action' => $params['logaction'],
			'log_timestamp' => $params['logtimestamp'],
			'log_namespace' => $params['lognamespace'],
			'log_deleted' => $params['logdeleted'],
			'log_user_text' => $params['logusertext'],
			'log_title' => $params['logtitle'],
			'log_comment' => $params['logcomment'],
			'log_params' => $params['logparams'],
			'log_page' => $params['logpage'],
			'log_mt_push_timestamp' => $pushTimestamp,
			'log_mt_user' => $params['loguser'],
		);
		$dbw->insert( 'logging', $insertLoggingArray );
		// Insert recentchanges and tags entries, unless rcid param is set to zero
		if ( $params['rcid'] ) {
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
				'rc_source' => $params['rcsource'],
				'rc_patrolled' => $params['rcpatrolled'],
				'rc_ip' => $params['rcip'],
				'rc_deleted' => $params['logdeleted'],
				'rc_logid' => $params['logid'],
				'rc_log_type' => $params['logtype'],
				'rc_log_action' => $params['logaction'],
				'rc_params' => $params['logparams'],
				'rc_mt_push_timestamp' => $pushTimestamp,
				'rc_mt_user' => $params['loguser']
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
		// Insert null revision
		if ( $params['nullrevid'] && $params['oldtext'] ) {
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
				'rev_comment' => $params['nullrevcomment'],
				'rev_user' => 0,
				'rev_user_text' => $params['logusertext'],
				'rev_timestamp' => $params['logtimestamp'],
				'rev_minor_edit' => 1,
				'rev_deleted' => 0,
				'rev_len' => $params['nullrevsize'],
				'rev_parent_id' => $params['nullrevparentid'],
				'rev_sha1' => $params['nullrevsize'],
				'rev_mt_push_timestamp' => $pushTimestamp,
				'rev_mt_user' => $params['loguser'],
				'rev_content_model' => $params['contentmodel'],
				'rev_content_format' => $params['contentformat']
			) );
			// To find the page_latest, sort descending by timestamp and then rev_id.
			$latestRow = $dbw->selectRow(
				'revision',
				array(
					'rev_id',
					'rev_content_model',
					'rev_len'
				),
				array( 'rev_page' => $params['logpage'] ),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp DESC, rev_id DESC' )
			);
			// Only update it if this new row is the latest
			if ( $latestRow->rev_id === $params['nullrevid'] ) {
				$dbw->update(
					'page',
					array(
						'page_latest' => $latestRow->rev_id,
						'page_len' => $latestRow->rev_len,
						'page_content_model' => $latestRow->rev_content_model,
						'page_lang' => NULL
					),
					array( 'page_id' => $params['logpage'] )
				);
			}
		}
		$r = array();
		$r['result'] = 'Success';
		$r['timestamp'] = $pushTimestamp;
		$this->getResult()->addValue( null, $this->getModuleName(), $r );
		if ( $returnResult ) {
			return $r;
		}
		return true;
	}

	public function getDescription() {
		return 'Mirror log entries.';
	}

	public function getAllowedParams() {
		return array(
			'logid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'logtype' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'logaction' => array(
				ApiBase::PARAM_TYPE => 'string',
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
			'tstags' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => ''
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'rcbot' => array(
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
			'nullrevid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevparentid' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevtimestamp' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevsize' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevtimestamp' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevsha1' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevtimestamp' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'nullrevdeleted' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
			),
			'oldtext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'nullrevcomment' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'uploadincomplete' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
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