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
			$this->dieUsage( 'Access denied: This user does not have the mirrortools right' );
		}
		$params = $this->extractRequestParams();
		$dbw = wfGetDB( DB_MASTER );
		// See if this data is already in the tables
		$conds = array ( 'log_id' => $params['logid'] );
		$res = $dbw->selectRow( 'logging', 'log_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Log id ' . $params['logid'] . ' is already in the logging table' );
		}
		$logParams = unserialize( $params['logparams'] );
		$moveTo = $logParams['4::target'];
		$noRedir = $logParams['5::noredir'] == '1' ? true : false;
		$moveToNamespace = 0;
		$moveToTitle = $moveTo;
		foreach( $wgMirrorNamespaces as $mirrorNamespace ) {
			if ( substr( $moveTo, 0, strlen( $mirrorNamespace ) )
				== $namespaceToTruncate ) {
				$moveToTitle = substr( $moveTo,
				    strlen( $mirrorNamespace ),
				    strlen( $moveTo )
				    - strlen( $mirrorNamespace ) );
				$moveToNamespace = $mirrorNamespace;
				break;
			}
                }
		$conds = array(
			'page_namespace' => $moveToNamespace,
			'page_title' => $moveToTitle
		);
		$res = $dbw->selectRow( 'page', array( 'page_id' ), $conds );
		if ( $res ) {
			// Move revisions
		} else {
			// Change page namespace and title
			$dbw->update(
				'page',
				array(
					'page_namespace' => $moveToNamespace,
					'page_title' => $moveToTitle
				),
				array(
					'page_id' => $params['logpage']
				)
			);
		}
		$pushTimestamp = wfTimestamp( TS_MW );
		$insertLoggingArray = array(
			'log_id' => $params['logid'],
			'log_type' => 'move',
			'log_action' => 'move',
			'log_timestamp' => $params['logtimestamp'],
			'log_user' => $params['loguser'],
			'log_namespace' => $params['lognamespace'],
			'log_deleted' => $params['logdeleted'],
			'log_user_text' => $params['logusertext'],
			'log_title' => $params['logtitle'],
			'log_comment' => $params['logcomment'],
			'log_params' => $params['logparams'],
			'log_page' => $params['logpage'],
			'log_mt_push_timestamp' => $pushTimestamp
		);
		$dbw->insert( 'logging', $insertLoggingArray );
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
		$r['timestamp'] = wfTimestamp( TS_MW );
		$this->getResult()->addValue( null, $this->getModuleName(), $r );
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