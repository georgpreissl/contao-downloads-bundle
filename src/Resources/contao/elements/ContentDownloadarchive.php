<?php


namespace GeorgPreissl\Downloads;

use Contao\ContentElement;
use Contao\System;
use Contao\BackendTemplate;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Input;
use Contao\Image;
use Contao\File;
use Contao\Controller;
use Contao\Environment;
use Symfony\Component\HttpFoundation\Request;
use GeorgPreissl\Downloads\DownloadarchiveModel;
use GeorgPreissl\Downloads\DownloadarchiveitemsModel;

class ContentDownloadarchive extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_downloadarchive';
	
	/**
	 * Download-archives
	 * @var string
	 */
	protected $arrDownloadarchives = array();

    protected $arrDownloadfiles = array();


	/**
	 * Return if the file does not exist
	 * @return string
	 */
	public function generate()
	{
		$this->arrDownloadarchives = unserialize($this->downloadarchive);
		
		if( $this->downloadarchive != null && !is_array($this->arrDownloadarchives) )
		{
			$this->arrDownloadarchives = array($this->downloadarchive);
		}
		
		// Return if there are no categories
		if (count($this->arrDownloadarchives) < 1)
		{
			return '';
		}


		if (System::getContainer()->get('contao.routing.scope_matcher')
		->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))
		)
		{
			$title = array();
			foreach($this->arrDownloadarchives as $archive)
			{
				$objDownloadarchivee = DownloadarchiveModel::findByPk($archive);
			
				$title[] = $objDownloadarchivee->title;
			}

            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD']['downloadarchive'][0] . ' - ' . implode(", ",$title) . ' ###';

            return $objTemplate->parse();

		}

		$this->checkForPublishedArchives();

		$user = System::getContainer()->get('security.helper')->getUser(); 
		$hasFrontendUser = System::getContainer()->get('contao.security.token_checker')->hasFrontendUser();
		$hasBackendUser = System::getContainer()->get('contao.security.token_checker')->hasBackendUser();
		$rootDir = System::getContainer()->getParameter('kernel.project_dir');


        // $this->import('FrontendUser', 'User');

        foreach($this->arrDownloadarchives as $archive)
        {

            $objFiles = DownloadarchiveitemsModel::findPublishedByPid($archive);

            if($objFiles === null) continue;

            while($objFiles->next())
            {
                $objFile = FilesModel::findByUuid($objFiles->singleSRC);



                if(!file_exists($rootDir . '/' . $objFile->path) || ($objFiles->guests && $hasFrontendUser) || ($objFiles->protected == 1 && !$hasFrontendUser && !$hasBackendUser))
                {
                    continue;
                }

                $arrGroups = StringUtil::deserialize($objFiles->groups);

                if ($objFiles->protected == 1 && is_array($arrGroups) && count(array_intersect($this->User->groups, $arrGroups)) < 1 && !$hasBackendUser)
                {
                    continue;
                }

                $allowedDownload = StringUtil::trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

                if (!in_array($objFile->extension, $allowedDownload))
                {
                    continue;
                }

                $arrFile = $objFiles->row();

                $filename = $objFile->path;

                $arrFile['filename'] = $filename;

                $this->arrDownloadfiles[$archive][$filename] = $arrFile;
            }
        }

        $file = Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file != '' && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file)))
        {
            foreach ($this->arrDownloadfiles as $k=>$archive)
            {
                if(array_key_exists($file,$archive))
                {
                    Controller::sendFileToBrowser($file);
                }
            }
        }


		return parent::generate();
	}


	/**
	 * Generate content element
	 */
	protected function compile()
	{
		global $objPage;
        // $this->import('StringUtil');
		
		$arrDownloadFiles = array();
		
		$time = time();


		foreach($this->arrDownloadfiles as $k=>$archive)
		{
			
			$objArchive = DownloadarchiveModel::findByPk($k);
			
            $strLightboxId = 'lightbox[' . substr(md5($objArchive->title . '_' . $objArchive->id), 0, 6) . ']';
			
			foreach($archive as $f => $arrFile)
			{
                #$objFile = \FilesModel::findByUuid($arrFile['singleSRC']);
                $objFile = new File($f, true);
// printf('<pre>%s</pre>', print_r($objFile,true));

				$arrFile['extension'] = $objFile->extension;

                // Clean the RTE output
                if ($objPage->outputFormat == 'xhtml')
                {
                    $arrFile['description'] = StringUtil::toXhtml($arrFile['description']);
                }
                else
                {
                    // $arrFile['description'] = StringUtil::toHtml5($arrFile['description']);
                    $arrFile['description'] = $arrFile['description'];
                }

                $arrFile['description'] = StringUtil::encodeEmail($arrFile['description']);
				$arrFile['css'] = ( $objArchive->class != "" ) ? $objArchive->class . ' ' : '';

				$arrFile['ctime'] = $objFile->ctime;
				$arrFile['ctimeformated'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $objFile->ctime);
                $arrFile['mtime'] = $objFile->mtime;
				$arrFile['mtimeformated'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $objFile->mtime);
                $arrFile['atime'] = $objFile->mtime;
				$arrFile['atimeformated'] = date($GLOBALS['TL_CONFIG']['dateFormat'], $objFile->atime);


                // Add an automatically generated image
                if ( $arrFile['createImage'] && in_array($arrFile['extension'],array('jpg','png','gif')) )
                {
                	// $objModel = \FilesModel::findByUuid($arrFile['filename']);
                     if (is_file($rootDir . '/' . $arrFile['filename']))
                    {
                		$arrFile['imgSRC'] = Image::get($arrFile['filename'], 150, '', '');
                		$arrFile['addImage'] = '1';
                	}
				}

                // Add an image selected by the user
                if ($arrFile['addImage'] && $arrFile['imgSRC'] != '')
                {
                    $objModel = FilesModel::findByUuid($arrFile['imgSRC']);

                    if (is_file($rootDir . '/' . $objModel->path))
                    {
                        $size = deserialize($arrFile['size']);
                       
                        
                        // $arrFile['imgSRC'] = $arrFile['imgSrc'] = \Image::get($objModel->path,$size[0],$size[1],$size[2]);
                        $arrFile['imgSRC'] = $arrFile['imgSrc'] = Image::get($objModel->path, 150, '', '');

                        // Image dimensions
                        if (($imgSize = @getimagesize($rootDir .'/'. rawurldecode($arrFile['imgSRC']))) !== false)
                        {
                            $arrFile['arrSize'] = $imgSize;
                            $arrFile['imageSize'] = ' ' . $imgSize[3];
                        }

                        $arrFile['imgHref'] = $objModel->path;
                        $arrFile['alt'] = specialchars($arrFile['alt']);
                        $arrFile['imagemargin'] = $this->generateMargin(deserialize($arrFile['imagemargin']), 'padding');
                        $arrFile['floating'] = in_array($arrFile['floating'], array('left', 'right')) ? sprintf(' float:%s;', $arrFile['floating']) : '';
                        $arrFile['addImage'] = true;

                        $arrFile['lightbox'] = ($objPage->outputFormat == 'xhtml' || VERSION < 2.11) ? ' rel="' . $strLightboxId . '"' : ' data-lightbox="' . substr($strLightboxId, 9, -1) . '"';

                    }
                }

				$arrFile['size'] = $this->getReadableSize($objFile->filesize);

				$src = 'assets/contao/images/' . $objFile->icon;
				
				if (($imgSize = @getimagesize($rootDir . '/' . $src)) !== false)
				{
					$arrFile['iconSize'] = ' ' . $imgSize[3];
				}
		
				$arrFile['icon'] = $src;
				// $arrFile['href'] = $this->Environment->request . (stristr($this->Environment->request,'?') ? '&' : '?') . 'file=' . $this->urlEncode($f);
				$arrFile['href'] = Environment::get('requestUri') . (stristr(Environment::get('requestUri'),'?') ? '&' : '?') . 'file=' . $this->urlEncode($f);
				
				$arrFile['archive'] = $objArchive->title;
				
				$strSorting = str_replace(array(' ASC',' DESC'),'',$this->downloadSorting);
				
				$arrDownloadFiles[$arrFile[$strSorting]][] =  $arrFile;
				
			}
		}
		
		if(stristr($this->downloadSorting,'DESC')) krsort($arrDownloadFiles);
		else ksort($arrDownloadFiles);
		
		$arrFiles = array();
		
		foreach($arrDownloadFiles as $row)
		{
			foreach($row as $file)
			{
				$arrFiles[] = $file;
			} 
		}
		
		if($this->downloadNumberOfItems > 0)
		{
			$arrFiles = array_slice($arrFiles,0,$this->downloadNumberOfItems);
		}
		
		$i=0;
		$length = count($arrFiles);
		
		if($this->perPage > 0)
		{
			
			// Get the current page
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;

			if ($page > ($length/$this->perPage))
			{
				$page = ceil($length/$this->perPage);
			}
			
			$offset = ((($page > 1) ? $page : 1) - 1) * $this->perPage;
			
			$arrFiles = array_slice($arrFiles,$offset,$this->perPage);
			
			// Add pagination menu
			$objPagination = new Pagination($length, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
			
			$length = count($arrFiles);
			
		}
		
		foreach($arrFiles as $file)
		{
			$class = "";
			if($i++ == 0) $class = "first ";
			$class .= ( $i % 2 == 0 ) ? "even" : "odd";
			if($i == $length) $class .= " last";
			
			$arrFiles[$i-1]['css'] .= $class;
		}
		
		
		if(count($arrFiles) < 1)
		{
			$this->Template->arrFiles = $GLOBALS['TL_LANG']['MSC']['keinDownload'];
		}
		else 
		{
			$this->Template->showMeta = $this->downloadShowMeta ? true : false;
			$this->Template->hideDate = $this->downloadHideDate ? true : false;
			$this->Template->arrFiles = $arrFiles;
		}
	}
	
	protected function checkForPublishedArchives()
	{
		$arrNew = array();
		foreach($this->arrDownloadarchives as $archive)
		{
			$objDownloadarchive = DownloadarchiveModel::findPublishedById($archive);
			
			if($objDownloadarchive !== null) $arrNew[] = $objDownloadarchive->id;
			
		}
		
		$this->arrDownloadarchives = $arrNew;
		
	}
}

?>