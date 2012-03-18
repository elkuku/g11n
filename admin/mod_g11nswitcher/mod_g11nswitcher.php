<?php
/**
 * @version SVN: $Id$
 * @package    G11nSwitcher
 * @subpackage Base
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 07-Dec-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

try
{
    if( ! jimport('g11n.language'))
    throw new Exception('g11n language library is required :(');

 #   g11n::cleanStorage();
    g11n::loadLanguage('mod_g11nswitcher');

 #   g11n::printEvents();
}
catch(Exception $e)
{
    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
    echo $e->getMessage();

    return;
}//try

//-- Include the helper file
#require_once dirname(__FILE__).DS.'helper.php';

//-- Get a parameter from the module's configuration
#$userCount = $params->get('usercount', 10);

//-- Get the items to display from the helper
#$items = Modg11nSwitcherHelper::getItems($userCount);

//-- Include the template for display
require JModuleHelper::getLayoutPath('mod_g11nswitcher');
