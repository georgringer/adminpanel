<?php
namespace TYPO3\CMS\Adminpanel\Domain\Model\Dto;

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

class Form {

	/** @var int */
	protected $pageId = 0;

	/** @var boolean */
	protected $displayIcons = FALSE;

	/** @var boolean */
	protected $displayEditPanels = FALSE;

	/** @var boolean */
	protected $showHiddenPages = FALSE;

	/** @var boolean */
	protected $showHiddenRecords = FALSE;

	/** @var boolean */
	protected $noCaching = FALSE;

	/** @var int */
	protected $clearCacheLevels = 0;

	/** @var string */
	protected $simulateDate = '';

	/** @var string */
	protected $simulateTime = '';

	/** @var string */
	protected $simulateFrontendUserGroup = '';

	public function __construct() {
		if (is_object(($GLOBALS['BE_USER']))) {
			$data = $GLOBALS['BE_USER']->uc['adminPanel'];
			if (is_array($data)) {
				foreach ($data as $key => $value) {
					if (property_exists($this, $key)) {
						$this->$key = $value;
					}
				}
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function getDisplayIcons() {
		return $this->displayIcons;
	}

	/**
	 * @return boolean
	 */
	public function getDisplayEditPanels() {
		return $this->displayEditPanels;
	}

	/**
	 * @return boolean
	 */
	public function getShowHiddenPages() {
		return $this->showHiddenPages;
	}

	/**
	 * @return boolean
	 */
	public function getShowHiddenRecords() {
		return $this->showHiddenRecords;
	}

	/**
	 * @return string
	 */
	public function getSimulateDate() {
		return $this->simulateDate;
	}

	/**
	 * @return string
	 */
	public function getSimulateTime() {
		return $this->simulateTime;
	}

	/**
	 * @return string
	 */
	public function getSimulateFrontendUserGroup() {
		return $this->simulateFrontendUserGroup;
	}

	/**
	 * @return boolean
	 */
	public function getNoCaching() {
		return $this->noCaching;
	}

	/**
	 * @return int
	 */
	public function getClearCacheLevels() {
		return $this->clearCacheLevels;
	}

	/**
	 * @param int $clearCacheLevels
	 */
	public function setClearCacheLevels($clearCacheLevels) {
		$this->clearCacheLevels = $clearCacheLevels;
	}

	/**
	 * @return int
	 */
	public function getPageId() {
		return $this->pageId;
	}

	/**
	 * @param int $pageId
	 */
	public function setPageId($pageId) {
		$this->pageId = $pageId;
	}

}