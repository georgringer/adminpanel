<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'Admin Panel',
	'description' => 'Manage functionality from the TYPO3 Frontend when logged in as a Backend User.',
	'category' => 'fe',
	'state' => 'stable',
	'clearCacheOnLoad' => 1,
	'author' => 'Benni Mack',
	'author_email' => 'benni@typo3.org',
	'author_company' => '',
	'version' => '7.2.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.2.0-7.9.99',
			'frontend' => '7.2.0-7.9.99',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);
