<?php
/**
 *
 * ApiMirrorRenameUser
 * Created on 10 July 2014 by Nathan Larson
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
class ApiMirrorCreateUser extends ApiBase {
	public function execute() {
		$user = $this->getUser();
		if ( !$user->isAllowed( 'mirrortools' ) ) {
			$this->dieUsage( 'Access denied: This user does not have the mirrortools right' );
		}
		$params = $this->extractRequestParams();
		$dbw = wfGetDB( DB_MASTER );
		// See if this data is already in the tables
		$conds = array ( 'user_text' => $params['renamedusertext'] );
		$res = $dbw->selectRow( 'user', 'user_text', $conds );
		if ( $res ) {
			$this->dieUsage( 'User ' . $params['renamedusertext']
				. ' is already in the user table' );
		}
		$conds = array ( 'log_id' => $params['logid'] );
		$res = $dbw->selectRow( 'logging', 'log_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Log id ' . $params['logid'] . ' is already in the logging table' );
		}
		$insertUserArray = array(
			'user_id' => $params['userid'],
			'user_name' => $params['username'],
			'user_real_name' => $params['userrealname'],
			'user_password' => $params['userpassword'],
			'user_newpassword' => $params['usernewpassword'],
			'user_newpass_time' => $params['usernewpasstime'],
			'user_email' => $params['useremail'],
			'user_touched' => $params['usertouched'],
			'user_token' => MWCryptRand::generateHex( USER_TOKEN_LENGTH ),
			'user_email_authenticated' => $params['useremailauthenticated'],
			'user_email_token' => $params['useremailtoken'],
			'user_email_token_expires' => $params['useremailtokenexpires'],
			'user_registration' => $params['userregistration'],
			'user_editcount' => $params['usereditcount'],
			'user_password_expires' => $params['userpasswordexpires']
		);
		$insertLoggingArray = array(
			'log_id' => $params['logid'],
			'log_type' => $params['logtype'],
			'log_action' => $params['logaction'],
			'log_timestamp' => $params['logtimestamp'],
			'log_user' => $params['loguser'],
			'log_namespace' => $params['lognamespace'],
			'log_deleted' => $params['logdeleted'],
			'log_user_text' => $params['loguser'],
			'log_title' => $params['logtitle'],
			'log_comment' => $params['logcomment'],
			'log_params' => $params['logparams'],
			'log_page' => $params['logpage']
		);
		$dbw->begin();
		$dbw->insert( 'user', $insertUserArray );
		$dbw->insert( 'logging', $insertLoggingArray );
		$dbw->commit();
		$r = array();
		$r['result'] = 'Success';
		$r['timestamp'] = wfTimestamp( TS_MW );
		$this->getResult()->addValue( null, $this->getModuleName(), $r );
		return true;
	}

	public function getDescription() {
		return 'Mirror the creation of users.';
	}

	public function getAllowedParams() {
		return array(
			'userid' => array(
				ApiBase::PARAM_TYPE => 'integer',
                                ApiBase::PARAM_REQUIRED => true
			),
                        'username' => array(
				ApiBase::PARAM_TYPE => 'string',
                                ApiBase::PARAM_REQUIRED => true
			),
			'userrealname' => array(
				ApiBase::PARAM_TYPE => 'string',
                                ApiBase::PARAM_DFLT => ''
			),
			'userpassword' => array(
				ApiBase::PARAM_TYPE => 'string',
                                ApiBase::PARAM_REQUIRED => true,
			),
			'usernewpassword' => array(
				ApiBase::PARAM_TYPE => 'string',
                                ApiBase::PARAM_DFLT => '',
			),
			'usernewpasstime' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_DFLT => null
			),
			'useremail' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'usertouched' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_REQUIRED => true
			),
			'user_token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => MWCryptRand::generateHex( USER_TOKEN_LENGTH )
			),
			'useremailauthenticated' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_DFLT => null
			),
			'useremailtoken' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => null
			),
			'useremailtokenexpires' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_DFLT => null
			),
			'userregistration' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_REQUIRED => true
			),
			'usereditcount' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0
			),
			'userpasswordexpires' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => null
			),
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
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true
			),
			'logcomment' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'logparams' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'logpage' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

        // TODO: Examples. Get them from ApiEditPage.php

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:MirrorTools/MirrorCreateUser';
	}

	public function mustBePosted() {
		return true;
	}
}