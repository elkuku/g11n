<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Install
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * g11n Main installer
 * @return bool
 */
function com_install()
{
    echo '<h2>'.JText::sprintf('%s Installer', 'g11n').'</h2>';
##ECR_MD5CHECK##

    /*
     * Custom install function
     *
     * If something goes wrong..
     */

    // return false;

    /*
     * otherwise...
     */

    return true;
}//function
##ECR_MD5CHECK_FNC##
