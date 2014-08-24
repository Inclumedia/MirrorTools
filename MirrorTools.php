<?php
/**
* Initialization file for the MirrorTools extension.
*
* This extension facilitates real-time mirroring of page histories.
*
* @version 1.0.2 - 2014-07-18
*
* @link https://www.mediawiki.org/wiki/Extension:Mirrortools Documentation
* @link https://www.mediawiki.org/wiki/Extension_talk:MirrorTools Support
* @link https://github.com/Inclumedia/MirrorTools Source Code
*
* @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0 or later
* @author Nathan Larson (Leucosticte)
*/

/* Alert the user that this is not a valid entry point to MediaWiki if they try to access the
special pages file directly.*/

if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
		To install the MirrorTools extension, put the following line in LocalSettings.php:
		require( "$IP/extensions/MirrorTools/MirrorTools.php" );
EOT;
	exit( 1 );
}

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'MirrorTools',
	'author' => 'Leucosticte',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MirrorTools',
	'descriptionmsg' => 'mirrortools-desc',
	'version' => '1.2.1',
);
$wgExtensionMessagesFiles['MirrorTools'] = __DIR__ . '/MirrorTools.i18n.php';
$wgAutoloadClasses['MirrorTools'] = __DIR__ . '/MirrorTools.classes.php';
$wgAutoloadClasses['ApiMirrorEditPage'] = __DIR__ . '/ApiMirrorEditPage.php';
$wgAutoloadClasses['ApiMirrorMove'] = __DIR__ . '/ApiMirrorMove.php';
$wgAutoloadClasses['ApiMirrorLogEntry'] = __DIR__ . '/ApiMirrorLogEntry.php';
$wgHooks['APIEditBeforeSave'][] = 'MirrorTools::onAPIEditBeforeSave';
$wgAPIModules['mirroredit'] = 'ApiMirrorEditPage';
$wgAPIModules['mirrorlogentry'] = 'ApiMirrorLogEntry';
$wgAPIModules['mirrormove'] = 'ApiMirrorMove';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'MirrorTools::SchemaUpdates';
$wgMirrorEditParams = array();
$wgGroupPermissions['user']['mirrortools'] = true;
$wgSpecialPages[ 'DeleteMirrored' ] = 'SpecialDeleteMirrored';
$wgAutoloadClasses[ 'SpecialDeleteMirrored' ] = __DIR__ . "/SpecialDeleteMirrored.php";
$wgNamespacesToTruncate = array(
    1 => 'Talk:',
    2 => 'User:',
    3 => 'User talk:',
    4 => 'Wikipedia:',
    5 => 'Wikipedia talk:',
    6 => 'File:',
    7 => 'File talk:',
    8 => 'MediaWiki:',
    9 => 'MediaWiki talk:',
    10 => 'Template:',
    11 => 'Template talk:',
    12 => 'Help:',
    13 => 'Help talk:',
    14 => 'Category:',
    15 => 'Category talk:',
    100 => 'Portal:',
    101 => 'Portal talk:',
    108 => 'Book:',
    109 => 'Book talk:',
    118 => 'Draft:',
    119 => 'Draft talk:',
    446 => 'EducationProgram:',
    447 => 'EducationProgram talk:',
    710 => 'TimedText:',
    711 => 'TimedText talk:',
    828 => 'Module:',
    829 => 'Module talk:'
);
$wgHooks['EnhancedChangesListMainlineRecentChangesFlags'][] = 'MirrorTools::enhancedChangesListMainlineRecentChangesFlags';
$wgHooks['EnhancedChangesListSubentryRecentChangesFlags'][] = 'MirrorTools::enhancedChangesListSubentryRecentChangesFlags';
$wgHooks['OldChangesListRecentChangesFlags'][] = 'MirrorTools::oldChangesListRecentChangesFlags';
$wgHooks['HistoryLineFlags'][] = 'MirrorTools::historyLineFlags';
$wgHooks['ShowDiffPageOldMinor'][] = 'MirrorTools::showDiffPageOldMinor';
$wgHooks['ShowDiffPageNewMinor'][] = 'MirrorTools::showDiffPageNewMinor';
$wgHooks['RecentChangesFields'][] = 'MirrorTools::recentChangesFields';
$wgHooks['EnhancedChangesBlockLineFlags'][] = 'MirrorTools::enhancedChangesBlockLineFlags';
$wgHooks['RevisionAttribs'][] = 'MirrorTools::revisionAttribs';
$wgHooks['RevisionSelectFields'][] = 'MirrorTools::revisionSelectFields';
$wgHooks['OldChangesListLogFlags'][] = 'MirrorTools::oldChangesListLogFlags';
$wgMirrorToolsDynamicParentIDs = false;

$wgMessagesDirs['MirrorTools'] = __DIR__ . '/i18n';
$wgRecentChangesFlags = array_merge( array( 'mirrored' => array( 'letter' => 'mirroredletter',
	'title' => 'recentchanges-label-mirrored' ) ), $wgRecentChangesFlags );