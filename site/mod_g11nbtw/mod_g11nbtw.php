<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');

try
{
    if( ! jimport('g11n.language'))
    throw new Exception('g11n language library is required');

    #g11n::setDebug(true);
    #g11n::cleanStorage('mod_g11nbtw');
    g11n::loadLanguage('mod_g11nbtw');

}
catch (Exception $e)
{
    JError::raiseWarning(0, $e->getMessage());

    return;
}//try

require JModuleHelper::getLayoutPath('mod_g11nbtw', $params->get('layout', 'default'));