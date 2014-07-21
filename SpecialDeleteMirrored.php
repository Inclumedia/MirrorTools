<?php
class SpecialDeleteMirrored extends SpecialPage {
	function __construct() {
		parent::__construct( 'DeleteMirrored' );
	}
 
	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
 
		# Get request data from, e.g.
		$param = $request->getText( 'param' );
 
		$wikitext = '';
		$dbw = wfGetDB( DB_MASTER );
		$vars = array( 'rev_mt_page', 'rev_page', 'rev_text_id' );
		$conds = array( "rev_mt_page<>''" );
		$res = $dbw->select( 'revision', $vars, $conds );
		if ( !$res || !$res->numRows() ) {
			$output->addWikiText( 'No mirrored rows were found!' );
			return true;
		}
		$pages = array();
		$texts = array();
		foreach( $res as $row ) {
			if ( !in_array( $row->rev_page, $pages ) ) {
				$pages[] = $row->rev_page;
			}
			$texts[] = $row->rev_text_id;
		}
		foreach ( $pages as $page ) {
			$wikitext .= "Deleting page id $page...\n";
			$dbw->delete(
				'recentchanges',
				array( 'rc_cur_id' => $page )
			);
			$dbw->delete(
				'page',
				array( 'page_id' => $page )
			);
		}
		foreach ( $texts as $text ) {
			$wikitext .= "Deleting text (old) id $text...\n";
			$dbw->delete(
				'text',
				array( 'old_id' => $text )
			);
		}
		$wikitext .= "Deleting revisions with rev_mt_page...\n";
		$dbw->delete(
			'revision',
			array( "rev_mt_page<>''" )
		);
		$wikitext .= "Done!";
		$output->addWikiText( $wikitext );
	}
}