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
$wgAutoloadClasses['ApiMirrorLogEntry'] = __DIR__ . '/ApiMirrorLogEntry.php';
$wgHooks['APIEditBeforeSave'][] = 'MirrorTools::onAPIEditBeforeSave';
$wgAPIModules['mirroredit'] = 'ApiMirrorEditPage';
$wgAPIModules['mirrorlogentry'] = 'ApiMirrorLogEntry';
$wgHooks['RevisionInsert'][] = 'MirrorTools::onRevisionInsert';
$wgHooks['UpdateRevisionOn'][] = 'MirrorTools::onUpdateRevisionOn';
$wgHooks['BeforeRecentChangeSave'][] = 'MirrorTools::onBeforeRecentChangeSave';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'MirrorTools::SchemaUpdates';
$wgMirrorEditParams = array();
$wgGroupPermissions['user']['mirrortools'] = true;
$wgSpecialPages[ 'DeleteMirrored' ] = 'SpecialDeleteMirrored';
$wgAutoloadClasses[ 'SpecialDeleteMirrored' ] = __DIR__ . "/SpecialDeleteMirrored.php";