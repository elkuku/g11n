<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

try
{
    if( ! jimport('g11n.language'))
    throw new Exception('g11n language library is required :(');

    #   g11n::cleanStorage('mod_g11nlogin');
    g11n::loadLanguage('mod_g11nsearch');
}
catch(Exception $e)
{
    JError::raiseWarning(0, $e->getMessage());

    return;
}//try

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

if($params->get('opensearch', 1))
{
	$doc = JFactory::getDocument();
	$app = JFactory::getApplication();

	$ostitle = $params->get('opensearch_title', jgettext('Search...').' '.$app->getCfg('sitename'));

	JFactory::getDocument()->addHeadLink(JURI::getInstance()
	->toString(array('scheme', 'host', 'port'))
	.JRoute::_('&option=com_search&format=opensearch'), 'search', 'rel'
	, array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml'));
}

$upper_limit = JFactory::getLanguage()->getUpperLimitSearchWord();

$button			= $params->get('button', '');
$imagebutton	= $params->get('imagebutton', '');
$button_pos		= $params->get('button_pos', 'left');
$button_text	= htmlspecialchars($params->get('button_text', jgettext('Search')));
$width			= intval($params->get('width', 20));
$maxlength		= $upper_limit;
$text			= htmlspecialchars($params->get('text', jgettext('Search...')));
$label			= htmlspecialchars($params->get('label', jgettext('Search...')));
$set_Itemid		= intval($params->get('set_itemid', 0));
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

if($imagebutton)
{
	$img = modg11nSearchHelper::getSearchImage($button_text);
}

$mitemid =($set_Itemid > 0) ? $set_Itemid : JRequest::getInt('Itemid');

require JModuleHelper::getLayoutPath('mod_g11nsearch', $params->get('layout', 'default'));
