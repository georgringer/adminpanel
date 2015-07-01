<?php
namespace TYPO3\CMS\Adminpanel\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Implements the logic for checking if the Admin Panel should be displayed
 * and which options are available
 */
class PanelController implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Admin panel is enabled - meaning that options stored in the Backend User can be used now
	 * @var bool
	 */
	protected $enableAdminPanel = FALSE;

	/**
	 * An instance of TSFE
	 * @var TypoScriptFrontendController
	 */
	protected $controller;

	/**
	 * An instance of the Fluid standalone template
	 * @var \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected $view;

	/**
	 * An instance of BE_USER
	 * @var \TYPO3\CMS\Backend\FrontendBackendUserAuthentication
	 */
	protected $backendUser;

	/**
	 * Hooks in right after a (possible) Backend User was initialized
	 *
	 * @param array $parameters given by the hook in TSFE->initializeBackendUser
	 * @param TypoScriptFrontendController $controller an instance of the frontend controller
	 */
	public function initialize($parameters, TypoScriptFrontendController $controller) {
		$this->controller = &$controller;
		$this->backendUser = &$parameters['BE_USER'];

		// Initialize admin panel since simulation settings are required at this point
		if ($this->controller->isBackendUserLoggedIn()) {
			$this->enableAdminPanel = TRUE;
			#$this->backendUser->initializeAdminPanel();
		}
	}

	/**
	 * hook to modify the values when identifying the page ID to fetch from
	 *
	 * @param array $parameters
	 * @param TypoScriptFrontendController $controller an instance of the frontend controller
	 */
	public function evaluateRenderingOptions($parameters, $controller) {
		// @todo: rework this functionality to use the configuration service
		// Backend user preview features:
		if ($this->enableAdminPanel) {
			$this->controller->fePreview = (bool)$this->backendUser->adminPanel->extGetFeAdminValue('preview');
			// If admin panel preview is enabled...
			if ($this->controller->fePreview) {
				if ($this->controller->fe_user->user) {
					$parameters['originalFrontendUser'] = $this->controller->fe_user->user;
				}
				$this->controller->showHiddenPage = (bool)$this->backendUser->adminPanel->extGetFeAdminValue('preview', 'showHiddenPages');
				$this->controller->showHiddenRecords = (bool)$this->backendUser->adminPanel->extGetFeAdminValue('preview', 'showHiddenRecords');
				// Simulate date
				$simTime = $this->backendUser->adminPanel->extGetFeAdminValue('preview', 'simulateDate');
				if ($simTime) {
					$GLOBALS['SIM_EXEC_TIME'] = $simTime;
					$GLOBALS['SIM_ACCESS_TIME'] = $simTime - $simTime % 60;
				}
				// simulate user
				$simUserGroup = $this->backendUser->adminPanel->extGetFeAdminValue('preview', 'simulateUserGroup');
				$this->controller->simUserGroup = $simUserGroup;
				if ($simUserGroup) {
					if ($this->controller->fe_user->user) {
						$this->controller->fe_user->user[$this->controller->fe_user->usergroup_column] = $simUserGroup;
					} else {
						$this->controller->fe_user->user = array(
							$this->controller->fe_user->usergroup_column => $simUserGroup
						);
					}
				}
				if (!$simUserGroup && !$simTime && !$this->controller->showHiddenPage && !$this->controller->showHiddenRecords) {
					$this->controller->fePreview = 0;
				}
			}
		}
	}

	/**
	 * Renders all options, and include the JS files and CSS files
	 *
	 */
	public function renderPanelAction() {
		$backendUserIsLoggedIn = (bool)$this->controller->beUserLogin;

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$this->view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
		$this->view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('adminpanel', 'Resources/Private/Templates/Panel.html'));

		$this->setDefaultArea();

		if ($backendUserIsLoggedIn) {
			$this->setInformationArea();
			$this->setEditAndPreviewArea();
		}

		$content = $this->view->render('Panel');

		$this->controller->content = str_ireplace('</body>', $content . '</body>', $this->controller->content);
	}

	/**
	 * information about anything, even when not logged in, checks also beLoginLinkIPlist
	 */
	protected function setDefaultArea() {

		$backendUserIsLoggedIn = (bool)$this->controller->beUserLogin;

		// Implementing functionality of "beLoginLinkIPList"
		$showLoginWhenNotLoggedIn = FALSE;
		if (!empty($this->controller->config['config']['beLoginLinkIPList'])) {
			if (GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $this->controller->config['config']['beLoginLinkIPList']) && !$backendUserIsLoggedIn) {
				$showLoginWhenNotLoggedIn = TRUE;
			}
		}

		$content = array(
			'isAdministrator' => $backendUserIsLoggedIn && $this->backendUser->isAdmin(),
			'currentPageLink' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
			'applicationVersion' => TYPO3_version,
			'pageId' => $GLOBALS['TSFE']->id,
			'displayLoginLink' => $showLoginWhenNotLoggedIn,
			'displayLogoutLink' => $backendUserIsLoggedIn,
			'form' => GeneralUtility::makeInstance(\TYPO3\CMS\Adminpanel\Domain\Model\Dto\Form::class),
			'urls' => array(
				'form' => BackendUtility::getAjaxUrl('AdminPanel::options'),
				'login' => $this->controller->absRefPrefix . TYPO3_mainDir . 'index.php?redirect_url=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')),
				'logout' => $this->controller->absRefPrefix . TYPO3_mainDir . 'logout.php?redirect=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL')),
			)
		);

		$this->view->assignMultiple($content);
	}

	/**
	 * Creates the content for the "info" section of the Admin Panel
	 */
	protected function setInformationArea() {

		if (empty($this->controller->fe_user->user['uid'])) {
			$loggedInGroups = FALSE;
			$loggedInUser = NULL;
		} else {
			$loggedInGroups = $this->controller->gr_list;
			$loggedInUser = $this->controller->fe_user->user;
		}

		$rootLine = array();
		foreach ($this->controller->rootLine as $pageOnRootLine) {
			$rootLine[] = $pageOnRootLine['title'];
		}
		$rootLine = array_reverse($rootLine);


		$content = array(
			'pageId' => $this->controller->id,
			'pageType' => $this->controller->type,
			'pageTitle' => $this->controller->page['title'],
			'rootLine' => implode(' / ', $rootLine),
			'scriptParseTime' => $this->controller->scriptParseTime,
			'loggedInUser' => $loggedInUser,
			'loggedInGroups' => $loggedInGroups,
			'isCached' => (bool)!$this->controller->no_cache,
			'nonCacheableObjects' => count($this->controller->config['INTincScript'])
		);
		$this->view->assignMultiple(array('information' => $content));
	}

	/**
	 * show edit & preview information
	 */
	public function setEditAndPreviewArea() {
		$content = array();
		$pageUid = (int)$this->controller->id;
		$languageUid = (int)$this->controller->sys_language_uid;

		// If another page module was specified, replace the default Page module with the new one
		$newPageModule = trim($this->backendUser->getTSConfigVal('options.overridePageModule'));
		$pageModule = BackendUtility::isModuleSetInTBE_MODULES($newPageModule) ? $newPageModule : 'web_layout';
		// @todo: $this->extNeedUpdate = TRUE;

		//  If mod.web_list.newContentWiz.overrideWithExtension is set, use that extension's create new content wizard instead:
		$perms = $this->backendUser->calcPerms($this->controller->page);
		$languageAllowed = $this->backendUser->checkLanguageAccess($languageUid);

		$returnUrl = GeneralUtility::getIndpEnv('REQUEST_URI');

		$content['history'] = array(
			'icon' => IconUtility::getSpriteIcon('actions-document-history-open', array('title' => 'Show History')),
			'url' => BackendUtility::getModuleUrl('record_history', array(
					'element' => 'pages:' . $pageUid,
					'returnUrl' => $returnUrl
				)) . '#latest'
		);

		if ($perms & Permission::CONTENT_EDIT && $languageAllowed) {
			$tsConfig = BackendUtility::getModTSconfig($pageUid, 'mod.web_list');
			$tsConfig = $tsConfig['properties']['newContentWiz.']['overrideWithExtension'];
			$newContentWizScriptPath = ExtensionManagementUtility::isLoaded($tsConfig) ? ExtensionManagementUtility::extRelPath($tsConfig) . 'mod1/db_new_content_el.php?' : BackendUtility::getModuleUrl('new_content_element');

			if ($languageUid > 0) {
				$newContentWizScriptPath .= '&sys_language_uid=' . $languageUid;
			}
			$content['newContent'] = array(
				'icon' => IconUtility::getSpriteIcon('actions-document-new', array('title' => 'Create New Content Element')),
				'url' => $newContentWizScriptPath . '&id=' . $pageUid . '&returnUrl=' . rawurlencode($returnUrl)
			);
		}

		if ($perms & Permission::PAGE_NEW) {
			$content['newPage'] = array(
				'icon' => IconUtility::getSpriteIcon('actions-page-new', array('title' => 'New Page')),
				'url' => BackendUtility::getModuleUrl('db_new', array(
					'id' => $pageUid,
					'pagesOnly' => 1,
					'returnUrl' => $returnUrl
				))
			);
		}

		if ($perms & Permission::PAGE_EDIT) {
			$content['movePage'] = array(
				'icon' => IconUtility::getSpriteIcon('actions-document-move', array('title' => 'Move Page')),
				'url' => BackendUtility::getModuleUrl('move_element', array(
					'table' => 'pages',
					'uid' => $pageUid,
					'returnUrl' => $returnUrl
				))
			);

			$content['editPageProperties'] = array(
				'icon' => IconUtility::getSpriteIcon('actions-document-open', array('title' => 'Edit Page Properties')),
				'url' => BackendUtility::getModuleUrl('record_edit', array(
					'edit[pages][' . $pageUid . ']' => 'edit',
					'noView' => 1,
					'returnUrl' => $returnUrl
				))
			);

			// show an item to edit the page translation
			if ($languageUid && $languageAllowed) {
				$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
					'uid,pid,t3ver_state',
					'pages_language_overlay',
					'pid=' . $pageUid . ' AND sys_language_uid=' . $languageUid .
					$this->controller->sys_page->enableFields('pages_language_overlay')
				);
				$this->controller->sys_page->versionOL('pages_language_overlay', $row);
				if (is_array($row)) {
					$content['editTranslation'] = array(
						'icon' => IconUtility::getSpriteIcon('mimetypes-x-content-page-language-overlay', array('title' => 'Edit Translation Record')),
						'url' => BackendUtility::getModuleUrl('record_edit', array(
							'edit[pages_language_overlay][' . $row['uid'] . ']' => 'edit',
							'noView' => 1,
							'returnUrl' => $returnUrl
						))
					);
				}
			}
		}

		if ($this->backendUser->check('modules', 'web_list')) {
			$content['list'] = array(
				'icon' => IconUtility::getSpriteIcon('actions-system-list-open', array('title' => 'Show list module')),
				'url' => BackendUtility::getModuleUrl('web_list', array(
					'id' => $pageUid,
					'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
				))
			);
		}

		// preview information
		// @todo: $this->extNeedUpdate = TRUE;
		// Simulate fe_user
		$content['frontendUserGroups'] = array(
			'0,-1' => ' '
		);
		$userGroups = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'fe_groups.uid, fe_groups.title',
			'fe_groups,pages',
			'pages.uid=fe_groups.pid AND pages.deleted=0 ' . BackendUtility::deleteClause('fe_groups') . ' AND ' . $this->backendUser->getPagePermsClause(1),
			'',
			'fe_groups.title ASC'
		);
		foreach ($userGroups as $group) {
			$content['frontendUserGroups'][$group['uid']] = $group['title'];
		}

		$this->view->assignMultiple(array('edit' => $content));
	}
}
