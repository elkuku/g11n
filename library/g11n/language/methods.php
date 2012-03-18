<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Base
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 19-Sep-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * Small multilanguaging function =;).
 *
 * Also includes sprintf() functionality if more parameters are supplied.
 *
 * Additional @param $n @deprecated use printf, sprintf or vprintf for this purpose !
 * If additional paramaters are supplied, the function behaves like sprintf.
 *
 * @param string $original Text to translate.
 *
 * @return string Translated text or original if not found.
 */
function jgettext($original)
{
    $translation = g11n::translate($original);

    //-- Do we have additional arguments ?
    //-- @deprecated and marked for removal !
    if(func_num_args() > 1)
    {
        JFactory::getApplication()->enqueueMessage(
            'jgettext() has been called with more then ONE arguments..', 'error');

        echo '<pre>';
        debug_print_backtrace();
        echo '</pre>';

        //-- Treat it as sprintf
        $args = func_get_args();

        $args[0] = $translation;

        return call_user_func_array('sprintf', $args);
    }

    return $translation;
}//function

/**
 * Small multilanguaging pluralisation function =;).
 *
 * @param string $singular Singular form of text to translate.
 * @param string $plural Plural form of text to translate.
 * @param integer $count The number of items
 *
 * @return string Translated text.
 */
function jngettext($singular, $plural, $count)
{
    return g11n::translatePlural($singular, $plural, $count);
}//function
