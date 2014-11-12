<?php
/**
 *
 * ApiMirrorUpload
 * Created on 28 September 2014 by Nathan Larson
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
 * A module that allows for mirroring uploads.
 *
 * @ingroup API
 */
class ApiMirrorUpload extends ApiBase {
	public function execute() {
		global $wgLocalFileRepo, $wgMirrorToolsImagesPath;
		$pushTimestamp = wfTimestamp( TS_MW );
		$user = $this->getUser();
		if ( !$user->isAllowed( 'mirrortools' ) ) {
			$this->dieUsage(
				'Access denied: This user does not have the mirrortools right' );
		}
		$params = $this->extractRequestParams();
		$request = $this->getMain()->getRequest();
		// Replace title spaces with underscores
		$params['logtitle'] = str_replace( ' ', '_', $params['logtitle'] );
		// Check sha1
		$sha = Revision::base36Sha1( $params['oldtext'] );
		if ( $params['oldtext'] ) {
			if ( $sha1 != $params['sha1'] ) {
				$this->dieUsage( "sha1 does not match. Submitted: "
					. $params['revsha1']
					. "Should have been: $sha1" );
			}
		}
		$dbw = wfGetDB( DB_MASTER );
		// See if this data is already in the tables
		$conds = array ( 'rev_id' => $params['nullrevid'] );
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
		$conds = array ( 'log_id' => $params['logid'] );
		$res = $dbw->selectRow( 'logging', 'log_id', $conds );
		if ( $res ) {
			$this->dieUsage( 'Log id ' . $params['logid']
				. ' is already in the logging table' );
		}
		// See if there's an existing file
		// TODO: Move any existing file out of the way
		// TODO: Save that file data to oldimage
		// TODO: Remove that file data from image
		// Figure out the appropriate path and filename to save the new file to
		$filename = $wgMirrorToolsImagesPath
			. ApiMirrorUpload::getHashPathForLevels( $params['logtitle'],
			$wgLocalFileRepo['hashLevels'] )  . $params['logtitle'];
		// Save the new file there
		$dirname = $wgMirrorToolsImagesPath
			. ApiMirrorUpload::getHashPathForLevels( $params['logtitle'],
			$wgLocalFileRepo['hashLevels'] );
		if ( !file_exists( $dirname ) ) {
		      $newdir = mkdir( $dirname, 0755, true );
		      if ( !$newdir ) {
			    $this->dieUsage( "Failure creating directory $dirname" );
		      }
		}
		// Create file
		$newf = fopen ( $filename, "ab" );
		if ( !$newf ) {
		      $this->dieUsage( "Failure creating file $filename\n" );
		}
		fwrite( $newf, base64_decode( $params['file'] ) );
		fclose( $newf );
		// Mime will be /-separated
		$mime = explode( '/', $params['mime'] );
		// Save that file data to image
		if ( !$params['uploadincomplete'] ) {
			$dbw->insert(
				'image',
				array(
					'img_name' => $params['logtitle'],
					'img_size' => $params['imgsize'],
					'img_width' => $params['imgwidth'],
					'img_height' => $params['imgheight'],
					'img_metadata' => $params['imgmetadata'],
					'img_bits' => $params['imgbits'],
					'img_media_type' => $params['imgmediatype'],
					'img_major_mime' => $mime[0],
					'img_minor_mime' => $mime[1],
					'img_description' => $params['logcomment'],
					'img_user' => $params['loguser'],
					'img_user_text' => $params['logusertext'],
					'img_timestamp' => $params['imgtimestamp'],
					'img_sha1' => $params['imgsha1']
				)
			);
			$that = new ApiMain();
			$apiMirrorLogEntry = new ApiMirrorLogEntry( $that );
			$r = $apiMirrorLogEntry->doLogEntry( $params, $pushTimestamp, true );
		} else {
			$r = array();
			$r['result'] = 'Success';
			$r['timestamp'] = $pushTimestamp;
			$r['length'] = strlen( $params['file'] );
		}
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
			'oldtext' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'imgmetadata' => array( // This'll be serialized
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'imgmediatype' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
			),
			'imgtimestamp' => array(
				ApiBase::PARAM_TYPE => 'timestamp',
				ApiBase::PARAM_REQUIRED => true,
			),
			'imgsize' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'imgwidth' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'imgheight' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'mime' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'imgbits' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'imgsha1' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
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
				ApiBase::PARAM_DFLT => '',
			),
			'nullrevcontentmodel' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '',
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
			'file' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'filename' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'uploadincomplete' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_DFLT => 0,
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
		#return 'csrf';
		return true;
	}

	public function getTokenSalt() {
		return '';
	}

	public function getHashPathForLevels( $name, $levels ) {
		if ( $levels == 0 ) {
			return '';
		} else {
			$hash = md5( $name );
			$path = '';
			for ( $i = 1; $i <= $levels; $i++ ) {
				$path .= substr( $hash, 0, $i ) . '/';
			}

			return $path;
		}
	}
}