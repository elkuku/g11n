<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

use \g11n\g11n;

/**
 * Small multilanguaging function =;).
 *
 * @param   string  $original    Text to translate.
 * @param   array   $parameters  Replacement parameters.
 *
 * @return string Translated text or original if not found.
 */
function g11n3t($original, array $parameters = [])
{
	return g11n::translate($original, $parameters);
}

/**
 * Small multilanguaging pluralisation function =;).
 *
 * @param   string   $singular    Singular form of text to translate.
 * @param   string   $plural      Plural form of text to translate.
 * @param   integer  $count       The number of items
 * @param   array    $parameters  Replacement parameters.
 *
 * @return string Translated text.
 */
function g11n4t($singular, $plural, $count, array $parameters = [])
{
	return g11n::translatePlural($singular, $plural, $count, $parameters);
}
