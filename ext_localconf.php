<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['postBeUser'][] = \TYPO3\CMS\Adminpanel\Controller\PanelController::class . '->initialize';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe'][] = \TYPO3\CMS\Adminpanel\Controller\PanelController::class . '->renderPanelAction';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3/Frontend/TypoScriptFrontendController']['determineIdPreview'][] = \TYPO3\CMS\Adminpanel\Controller\PanelController::class . '->evaluateRenderingOptions';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('AdminPanel::options', \TYPO3\CMS\Adminpanel\Service\ConfigurationService::class . '->save');
