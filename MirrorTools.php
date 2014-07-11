<?php
/**
* Initialization file for the MirrorTools extension.
*
* This extension facilitates real-time mirroring of page histories.
*
* @version 1.0.0 - 2014-01-21
*
* @link https://www.mediawiki.org/wiki/Extension:AllowInternetArchiver Documentation
* @link https://www.mediawiki.org/wiki/Extension_talk:AllowInternetArchiver Support
* @link https://github.com/Inclumedia/AllowInternetArchiver Source Code
*
* @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0 or later
* @author Nathon Larson (Leucosticte)
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
$wgAutoloadClasses['ApiMirrorCreateUser'] = __DIR__ . '/ApiMirrorCreateUser.php';
$wgHooks['APIEditBeforeSave'][] = 'MirrorTools::onAPIEditBeforeSave';
$wgAPIModules['mirroredit'] = 'ApiMirrorEditPage';
$wgAPIModules['mirrorcreateuser'] = 'ApiMirrorCreateUser';
#$wgHooks['PageContentSaveRevision'][] = 'MirrorTools::onPageContentSaveRevision';
$wgHooks['RevisionInsert'][] = 'MirrorTools::onRevisionInsert';
$wgHooks['UpdateRevisionOn'][] = 'MirrorTools::onUpdateRevisionOn';
$wgHooks['BeforeRecentChangeSave'][] = 'MirrorTools::onBeforeRecentChangeSave';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'MirrorTools::SchemaUpdates';
$wgMirrorEditParams = array();
$wgGroupPermissions['user']['mirrortools'] = true;