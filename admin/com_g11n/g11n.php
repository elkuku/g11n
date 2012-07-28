<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Base
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

ini_set('error_reporting', -1);
jimport('joomla.application.component.controller');

defined('BR') || define('BR', '<br />');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

try
{
    if( ! jimport('g11n.language'))
    throw new Exception('g11n language library is required');

    //g11n::cleanStorage();
    //g11n::setDebug(true);

    g11n::loadLanguage();
}
catch(Exception $e)
{
    JError::raiseWarning(0, $e->getMessage());

    return;
}//try

//-- Add CSS
JHTML::stylesheet('default.css', 'administrator/components/com_g11n/assets/css/');
JHTML::stylesheet('icon.css', 'administrator/components/com_g11n/assets/css/');

//-- Import Helper class
JLoader::import('helpers.g11n', JPATH_COMPONENT);

//$controller = JController::getInstance('g11nList');

include 'controller.php';

$controller = new g11nListController;

$controller->execute(JRequest::getCmd('task'));

//-- Add Submenu
g11nHelper::addSubmenu(JRequest::getCmd('view'));

#g11n::debugPrintTranslateds();

$controller->redirect();
