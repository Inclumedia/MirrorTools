<?php
/**
 *
 *
 * Created on 4 September 2014
 *
 * By Nathan Larson, https://www.mediawiki.org/wiki/User:Leucosticte
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
 * A query action to mark revisions as remotely live, if they aren't already. It then returns two
 * arrays, one of revisions that have been successfully marked as remotely live, and the other
 * notifying that the revisions don't exist.
 *
 * @ingroup API
 */
class ApiMakeRemotelyLive extends ApiBase {
    public function execute() {
	$user = $this->getUser();
	if ( !$user->isAllowed( 'mirrortools' ) ) {
	    $this->dieUsage( 'Access denied: This user does not have the mirrortools right' );
	}
	$params = $this->extractRequestParams();
	$revIds = array();
	$badRevIds = array();
	$isFirst = true;
	$where = '';
	$dbw = wfGetDB( DB_MASTER );
	$revIdNumbers = $params['revidnumbers'];
	foreach( $revIdNumbers as $revIdNumber ) {
	    if ( !$isFirst ) {
		$where .= ' OR ';
	    }
	    $where .= "rev_id=" . $revIdNumber;
	    $isFirst = false;
	}
	$res = $dbw->select( 'revision', array( 'rev_id', 'rev_page' ), $where );
	$revPage = null;
	foreach( $res as $row ) {
	    $revIds[] = $row->rev_id;
	    $revPage = $row->rev_page;
	}
	if( $res && $dbw->numRows( $res ) ) {
	    // Make the revisions remotely live
	    $dbw->update( 'revision', 'rev_mt_remotely_live=1', $where );
	    // Make the page remotely live too
	    $dbw->update( 'page', 'page_mt_remotely_live=1', "page_id=$revPage" );
	}
	foreach( $res as $row ) {
	    $revIds[] = $row->rev_id;
	}
	$r = array();
	#$r['maderemotelylive'] = array();
	#$r['badrevids'] = array();
	foreach( $revIdNumbers as $revIdNumber ) {
	    if ( in_array( $revIdNumber, $revIds ) ) {
		$r['maderemotelylive'][] = $revIdNumber;
	    } else {
		$r['badrevids'][] = $revIdNumber;
	    }
	}
	$r['result'] = 'Success';
	$r['timestamp'] = wfTimestamp( TS_MW );
	$this->getResult()->addValue( null, $this->getModuleName(), $r );
	return true;
    }

    public function getAllowedParams() {
	return array(
	    'revidnumbers' => array(
		ApiBase::PARAM_ISMULTI => true,
		ApiBase::PARAM_TYPE => 'integer',
		ApiBase::PARAM_REQUIRED => true
	    )
	);
    }
}