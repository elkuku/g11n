<?php
/**
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

    #g11n::cleanStorage('mod_g11nbreadcrumbs');
    g11n::loadLanguage('mod_g11nbreadcrumbs');
}
catch(Exception $e)
{
    echo $e->getMessage();

    return;
}

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

// Get the breadcrumbs
$list	= modg11nBreadCrumbsHelper::getList($params);
$count	= count($list);

// Set the default separator
$separator = modg11nBreadCrumbsHelper::setSeparator($params->get('separator'));

require JModuleHelper::getLayoutPath('mod_g11nbreadcrumbs', $params->get('layout', 'default'));
