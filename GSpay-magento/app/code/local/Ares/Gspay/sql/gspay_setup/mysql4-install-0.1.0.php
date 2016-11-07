<?php

$installer = $this;
$installer->startSetup();

// Quote Data
$installer->addAttribute('quote', 'gsordid', array(
	'label'				=> 'gsOrdId',
	//'input'				=> 'select',
	'type'				=> 'varchar',
	'visible'			=> true,
	'visible_on_front'	=> true,
	'user_defined'		=> true,
	'required'			=> false
));


// Order Data
$installer->addAttribute('order', 'gsordid', array(
	'label'				=> 'gsOrdId',
	'type'				=> 'varchar',
	'visible'			=> true,
	'visible_on_front'	=> true,
	'user_defined'		=> true,
	'required'			=> false
));


$installer->endSetup(); 