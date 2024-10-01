<?php


namespace GeorgPreissl\Downloads;

use Contao\Model;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class DownloadarchiveitemsModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_downloadarchiveitems';

    /**
     * Find all published files by their parent IDs
     *
     * @param integer $intPid      The archive ID
     * @param array   $arrOptions An optional options array
     *
     * @return \Model|null The model or null if there is no published page
     */
    public static function findPublishedByPid($intPid, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.pid=?");


        if (!System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) 
        {
            $time = time();
            $arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
        }

        return static::findBy($arrColumns, $intPid, $arrOptions);
    }
}
