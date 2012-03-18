<?php
/**
 * @package    g11n
 * @subpackage Install
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * The main uninstaller function
 * @return bool
 */
function com_uninstall()
{
    echo '<h2>'.JText::sprintf('%s Uninstaller', 'g11n').'</h2>';

    /*
     * Custom uninstall function
     *
     * If something goes wrong..
     */

    // return false;

    /*
     * otherwise...
     */

    return true;
}//function
