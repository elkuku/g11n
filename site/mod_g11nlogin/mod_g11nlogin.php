<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');

try
{
    if( ! jimport('g11n.language'))
    throw new Exception('g11n language library is required :(');

    #   g11n::cleanStorage('mod_g11nlogin');
    g11n::loadLanguage('mod_g11nlogin');
}
catch(Exception $e)
{
    JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

    return;
}

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$params->def('greeting', 1);

$type = modg11nLoginHelper::getType();
$return = modg11nLoginHelper::getReturnURL($params, $type);
$user = JFactory::getUser();
$avatar = '';

if( ! $user->guest)
{
    $pAvatar = new TalkPHP_Gravatar;

    $pAvatar->setEmail($user->email)
    ->setSize(80)
    ->setRatingAsPG()
    ->setDefaultImageAsIdentIcon();

    $avatar = '<img src="'.$pAvatar.'" alt="'.jgettext('Avatar').'" />';
}

require JModuleHelper::getLayoutPath('mod_g11nlogin', $params->get('layout', 'default'));

if(0)
{
    /* g11n: Definitions for JCore com_users */
    jgettext('COM_USERS_REGISTRATION_DEFAULT_LABEL');

    jgettext('Password:');

    # helloxx
    jgettext('Name:');

    //--fooo
    jgettext('Confirm Password:');

    jgettext('Enter your full name');

    jgettext('Username:');
    //@todo missing..
    jgettext('Register');
}
