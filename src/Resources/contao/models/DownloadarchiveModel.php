<?php


namespace GeorgPreissl\Downloads;

use Contao\Model;
use Contao\Database;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class DownloadarchiveModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_downloadarchive';


	/**
	 * Find multiple news archives by their IDs
	 *
	 * @param array $arrIds     An array of archive IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no news archives
	 */
	public static function findMultipleByIds($arrIds, array $arrOptions=array())
	{
		if (!is_array($arrIds) || empty($arrIds))
		{
			return null;
		}

		$t = static::$strTable;

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = Database::getInstance()->findInSet("$t.id", $arrIds);
		}

		return static::findBy(array("$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")"), null, $arrOptions);
	}


    /**
     * Find a published archive by its ID
     *
     * @param integer $intId      The archive ID
     * @param array   $arrOptions An optional options array
     *
     * @return \Model|null The model or null if there is no published page
     */
    public static function findPublishedById($intId, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.id=?");

        if (!System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) 
        {
            $time = time();
            $arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
        }

        return static::findOneBy($arrColumns, $intId, $arrOptions);
    }
}
