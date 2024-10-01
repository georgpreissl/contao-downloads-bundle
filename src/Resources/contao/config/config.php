<?php 


use Contao\ArrayUtil;

use GeorgPreissl\Downloads\ModuleDownloadarchive;
use GeorgPreissl\Downloads\DownloadarchiveModel;
use GeorgPreissl\Downloads\DownloadarchiveitemsModel;
use GeorgPreissl\Downloads\ContentDownloadarchive;

/**
 * Add back end modules
 */


ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['content'], 1, array
(
	'downloads' => array
	(
		'tables' => array('tl_downloadarchive', 'tl_downloadarchiveitems')
	)
));



/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['downloadarchive'] = array
(
	'downloadarchive'   => ModuleDownloadarchive::class
);





/**
 * Content Element
 */
$GLOBALS['TL_CTE']['files']['downloadarchive'] = ContentDownloadarchive::class;

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'downloadarchives';
$GLOBALS['TL_PERMISSIONS'][] = 'downloadarchivep';


/**
 * Register the model
 */
$GLOBALS['TL_MODELS']['tl_downloadarchive'] = DownloadarchiveModel::class;
$GLOBALS['TL_MODELS']['tl_downloadarchiveitems'] = DownloadarchiveitemsModel::class;
