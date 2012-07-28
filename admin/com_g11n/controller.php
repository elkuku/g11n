<?php
/**
 * @package    g11n
 * @subpackage Base
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

jimport('joomla.application.component.controller');

/**
 * The g11n default Controller.
 *
 * @package    g11n
 * @subpackage Controllers
 */
class g11nListController extends JControllerLegacy
{
    public function cleanCache()
    {
        $scope = JRequest::getCmd('scope');
        $extension = JRequest::getCmd('extension');
        $file = JRequest::getCmd('file');

        try//
        {
            $path = g11nExtensionHelper::getScopePath($scope);

            $path .= '/cache/language/'.$extension.'/'.$file;

            $path = JPath::clean($path);

            if( ! JFile::exists($path))
            throw new Exception(jgettext('Invalid file'));

            if( ! JFile::delete($path))
            throw new Exception(jgettext('Can not delete the file'));

            JFactory::getApplication()->enqueueMessage(jgettext('The file has been deleted'));
        }
        catch(Exception $e)
        {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }//try

        parent::display();
    }//function

    public function deleteCache()
    {
        $scope = JRequest::getCmd('scope');
        $basePath = g11nExtensionHelper::getScopePath($scope);

        $retUri = JRequest::getVar('retUri', '', 'get', 'none', 'base64');

        $cachePath = g11nStorage::getCacheDir();

        $msgType = 'message';
        $message = 'Cache cleaned';

        if( ! JFolder::exists($basePath.DS.$cachePath))
        {
            $message = 'Invalid folder: '.$basePath.DS.$cachePath;
            $msgType = 'error';
        }
        else//
        {
            if( ! JFolder::delete($basePath.DS.$cachePath))
            {
                $message = 'Can not delete the cache folder';
                $msgType = 'error';
            }
        }

        $this->setRedirect(base64_decode($retUri), $message, $msgType);
    }//function
}//class
