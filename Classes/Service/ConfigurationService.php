<?php
namespace TYPO3\CMS\Adminpanel\Service;

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

/**
 * Handles the logic for reading, updating and saving the options for the Admin Panel
 *
 * Generally there is the following priority applied to the configuration options
 *
 * --- In the Frontend the option:
 *    config.admPanel = 1
 * has to be enabled. Please note that this does not matter on certain occasions and could be enabled anyways all the time.
 *
 * --- For the Backend User Options:
 *
 * The following userTSconfig is available:
 *    admPanel.preview = 1
 *    admPanel...
 *
 * Once changes to the options within the admin panel are made, the $BE_USER->uc['adminPanel'] stores the current state
 * of open or collapsed items and selected checkboxes.
 */
class ConfigurationService {

	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $backendUser;

	/**
	 * @param \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser
	 */
	public function __construct($backendUser = NULL) {
		$this->backendUser = $backendUser ?: $GLOBALS['BE_USER'];
	}

	/**
	 * Called directly by AJAX in the backend interface to store data
	 *
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $requestHandler
	 */
	public function save($parameters, $requestHandler) {
		$input = GeneralUtility::_GP('edit');

		$mapping = array(
			'displayIcons' => 'edit_displayFieldIcons',
			'displayEditPanels' => 'edit_displayIcons',
			'showHiddenPages' => 'preview_showHiddenPages',
			'showHiddenRecords' => 'preview_showHiddenRecords',
			'simulateDate' => 'preview_simulateDate',
			'simulateFrontendUserGroup' => 'preview_simulateUserGroup',
			'noCaching' => 'cache_noCache',
		);

		if (is_array($input)) {
			$new = array();

			$this->backendUser->uc['adminPanel'] = array_merge(is_array($this->backendUser->uc['adminPanel']) ? $this->backendUser->uc['adminPanel'] : array(), $input);
			foreach ($input as $k => $v) {
				if (isset($mapping[$k])) {
					$input['TSFE_adminConfig'][$mapping[$k]] = $v;
					$this->backendUser->uc['TSFE_adminConfig'][$mapping[$k]] = $v;
				}
			}
			$this->backendUser->writeUC();
			$requestHandler->setContent(array('success' => TRUE));
		} else {
			$requestHandler->setContent(array('error' => TRUE));
		}
		$requestHandler->setContent($input);
		$requestHandler->setContentFormat('json');

	}
}
