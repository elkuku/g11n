/**
 * @copyright  2010-2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

/**
 * Small multilanguaging function =;).
 *
 * Also includes sprintf() functionality if more parameters are supplied.
 *
 * @param string original The original string to translate
 * @param additionals [n] if additional paramaters are supplied, the function behaves like sprintf.
 *
 * @returns string The translated string or the original if not found.
 */
function jgettext(original) {
    var translation = g11n.translate(original);

    if (arguments.length > 1) {
        arguments[0] = translation;

        return phpjs.call_user_func_array(phpjs.sprintf, arguments);
    }

    return translation;
}

/**
 * Small multilanguaging pluralisation function =;).
 *
 * @param string $singular Singular form of text to translate.
 * @param string $plural Plural form of text to translate.
 *
 * @return string Translated text.
 */
function jngettext(singular, plural, count) {
    return g11n.translatePlural(singular, plural, count);
}
