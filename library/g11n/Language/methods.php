<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n;

/**
 * Small multilanguaging function =;).
 *
 * Also includes sprintf() functionality if more parameters are supplied.
 * Additional @param $n @deprecated use printf, sprintf or vprintf for this purpose !
 *                            If additional paramaters are supplied, the function behaves like sprintf.
 *
 * @param   string  $original  Text to translate.
 *
 * @return string Translated text or original if not found.
 */
function jgettext($original)
{
	return g11n::translate($original);
}

/**
 * Small multilanguaging pluralisation function =;).
 *
 * @param   string   $singular  Singular form of text to translate.
 * @param   string   $plural    Plural form of text to translate.
 * @param   integer  $count     The number of items
 *
 * @return string Translated text.
 */
function jngettext($singular, $plural, $count)
{
	return g11n::translatePlural($singular, $plural, $count);
}
