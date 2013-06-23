<?php
/**
 * @package    g11nSwitcher
 * @subpackage Base
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 14-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

try
{
    if( ! jimport('g11n.language'))
    throw new Exception('g11n language library is required :(');

 #   g11n::cleanStorage();
#    g11n::loadLanguage('', '', 'po');

 #   g11n::printEvents();
}
catch(Exception $e)
{
    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

    return;
}

//-- Include the helper file
#require_once dirname(__FILE__).DS.'helper.php';

//-- Get a parameter from the module's configuration
#$userCount = $params->get('usercount', 10);

//-- Get the items to display from the helper
#$items = Modg11nSwitcherHelper::getItems($userCount);

//-- Include the template for display
require JModuleHelper::getLayoutPath('mod_g11nswitcher');
