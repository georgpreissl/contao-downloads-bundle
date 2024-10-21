<?php


use Contao\Backend;
use Contao\BackendUser;
use Contao\StringUtil;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\DC_Table;
use Contao\Date;
use Contao\Config;

/**
 * Load tl_content language file
 */
$this->loadLanguageFile('tl_content');

/**
 * Table tl_downloadarchiveitems 
 */
$GLOBALS['TL_DCA']['tl_downloadarchiveitems'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
		'ptable'					  => 'tl_downloadarchive',
		'enableVersioning'            => true,
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
            )
        )
	),

	// List
	'list' => array
	(
		// 'sorting' => array
		// (
		// 	'mode'                    => 4,
		// 	'fields'                  => array('sorting'),
		// 	'panelLayout'             => 'search,limit',
		// 	'headerFields'            => array('title'),
		// 	'child_record_callback'   => array('tl_downloadarchiveitems', 'listFiles')
		// )
		'sorting' => array
		(
			'mode'                    => DataContainer::MODE_PARENT,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter;search,limit',
			'defaultSearchField'      => 'title',
			'headerFields'            => array('title'),
			'child_record_callback'   => array('tl_downloadarchiveitems', 'listFiles'),
			'renderAsGrid'            => true,
			'limitHeight'             => 160
		)		
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('protected','addImage')
		,'default'                     => '{title_legend},title,description;{file_legend:hide},singleSRC,createImage;'
										.'{image_legend:hide},addImage;'
										.'{protection_legend:hide},guests,protected;'
										.'{publish_legend},published,start,stop'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'protected'                   => 'groups',
		'addImage'                    => 'imgSRC,alt,size'
	),
	
	// Fields
	'fields' => array
	(
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'basicEntities'=>true, 'maxlength'=>255),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['description'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
            'sql'                     => "text NULL"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('mandatory'=>true,'files'=>true,'fieldType'=>'radio'),
            'sql'                     => "binary(16) NULL"
		),
		'protected' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['protected'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
		),
		'guests' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['guests'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
            'sql'                     => "char(1) NOT NULL default ''"
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true),
            'sql'                     => "blob NULL"
		),
		'addImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['addImage'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
		),
		'imgSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('mandatory'=>true,'files'=>true,'fieldType'=>'radio'),
            'sql'                     => "binary(16) NULL"
		),
		'createImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['createImage'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array(),
            'sql'                     => "char(1) NOT NULL default ''"
		),
		'size' => array
		(
			'inputType' => 'standardField',
			// 'label'                   => &$GLOBALS['TL_LANG']['tl_content']['size'],
			// 'exclude'                 => true,
			// 'inputType'               => 'imageSize',
			// 'options'                 => $GLOBALS['TL_CROP'],
			// 'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			// 'eval'                    => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
            // 'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'alt' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['alt'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>false, 'rgxp'=>'extnd', 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'caption' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['caption'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'extnd', 'maxlength'=>255, 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'floating' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['floating'],
			'exclude'                 => true,
			'inputType'               => 'radioTable',
			'options'                 => array('above', 'left', 'right'),
			'eval'                    => array('cols'=>3, 'tl_class'=>'w50'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
            'sql'                     => "varchar(32) NOT NULL default ''"
		),
		'imagemargin' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
			'exclude'                 => true,
			'inputType'               => 'trbl',
			'options'                 => array('px', '%', 'em', 'pt', 'pc', 'in', 'cm', 'mm'),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'useImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['useImage'],
			'exclude'                 => true,
			'default'                 => '0',
			'inputType'               => 'radio',
			'options'				  => array('0','1','2'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['useImageReference'],
			'eval'					  => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default '0'"
		),
		'published' => array
		(
			'toggle'                  => true,
			'filter'                  => true,
			'flag'                    => DataContainer::SORT_INITIAL_LETTER_DESC,
			'inputType'               => 'checkbox',
			'eval'                    => array('doNotCopy'=>true),
			'sql'                     => array('type' => 'boolean', 'default' => false)
		),	
		'start' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
		),
		'stop' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_downloadarchiveitems']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
		)
	)
);

/**
 * Class tl_downloadarchiveitems
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class tl_downloadarchiveitems extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$objUser = BackendUser::getInstance();
		$this->User = $objUser;		
		// $this->import('BackendUser', 'User');
	}
	

// 	public function listFiles($arrRow)
// 	{

//         $time = time();

//         $objFile = FilesModel::findByUuid($arrRow['singleSRC']);

// 		$key = ($arrRow['published'] && ($arrRow['start'] == '' || $arrRow['start'] < $time) && ($arrRow['stop'] == '' || $arrRow['stop'] > $time)) ? 'published' : 'unpublished';
		
// 		return '
// <div class="cte_type ' . $key . '"><strong>' . $arrRow['title'] . '</strong></div>
// <div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? '
// h52' : '') . 'block">
// '.$objFile->path.'<br /><br />
// '.$arrRow['description'].'
// </div>' . "\n";
// 	}

	public function listFiles($arrRow)
	{
		$key = $arrRow['published'] ? 'published' : 'unpublished';
		$date = Date::parse(Config::get('datimFormat'), $arrRow['tstamp']);

		return '
<div class="cte_type ' . $key . '">' . $date . '</div>
<div class="cte_preview">
<h2>' . $arrRow['title'] . '</h2>
</div>' . "\n";
	}

	
	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		// if (strlen($this->Input->get('tid')))
		// {
		// 	$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
		// 	$this->redirect($this->getReferer());
		// }

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_downloadarchiveitems::published', 'alexf'))
		{
			return '';
		}
		
		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
		{
			$icon = 'invisible.gif';
		}		

		// return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>image</a> ';
	}


	/**
	 * Disable/enable a user group
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions to edit
		$this->Input->setGet('id', $intId);
		$this->Input->setGet('act', 'toggle');

		$this->createInitialVersion('tl_downloadarchiveitems', $intId);
	
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_downloadarchiveitems']['fields']['published']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_downloadarchiveitems']['fields']['published']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_downloadarchiveitems SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);

		$this->createNewVersion('tl_downloadarchiveitems', $intId);

	}
}
