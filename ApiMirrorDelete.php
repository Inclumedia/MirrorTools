<?php
/**
 *
 * ApiMirrorDelete
 * Created on 26 August 2014 by Nathan Larson
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
 * A module that allows for mirroring page deletions.
 *
 * @ingroup API
 */
class ApiMirrorDelete extends ApiBase {
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
		// Make any remotely live revisions that are at that deleted page not remotely
		// live anymore.
		$dbw->update(
			'revision',
			array(
				'rev_mt_remotely_live' => 0
			),
			array(
				'rev_page' => $params['pageid']
			)
		);
		if ( $dbw->affectedRows() ) {
			// Update the page table entry for the deleted page, to make it not remotely live.
			$dbw->update(
				'page',
				array( 'page_mt_remotely_live' => 0 ),
				array( 'page_id' => $sourcePageId )
			);
			// To find the most recent revision, sort descending by timestamp and then rev_id.
			$rowTimestamp = $dbw->selectField(
				'revision',
				'rev_timestamp',
				array(
				      'rev_page' => $params['pageid'],
				      "rev_timestamp<=" . $params['logtimestamp']
				),
				__METHOD__,
				array( 'ORDER BY' => 'rev_timestamp DESC' )
			);
			$mostRecentRevisionRow = $dbw->selectRow(
				'revision',
				array(
					'rev_id',
					'rev_content_model',
				), array(
					'rev_page' => $params['pageid'],
					'rev_timestamp' => $rowTimestamp
				),
				__METHOD__,
				array( 'ORDER BY' => 'rev_id DESC' )
			);
			// Set page_latest to the latest revision
			$dbw->update(
				'page',
				array( 'page_latest' => $mostRecentRevisionRow->rev_id ),
				array( 'page_id' => $params['pageid'] )
			);
		}
		// Insert log entry
		$insertLoggingArray = array(
			'log_id' => $params['logid'],
			'log_type' => 'delete',
			'log_action' => 'delete',
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
			'rc_log_type' => 'delete',
			'rc_log_action' => 'delete',
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
			'redirrevid' => array(
				ApiBase::PARAM_TYPE => 'integer',
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