<?php
/**
 * @version SVN: $Id$
 * @package    g11nSwitcher
 * @subpackage Tmpl
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 14-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

$language = JFactory::getLanguage();
$languages = $language->getKnownLanguages();

$default = g11n::getDefault();

$uri = JURI::getInstance();

$baseLink = (string)$uri;

$baseLink .=($uri->getQuery()) ? '&' : '?';

if(strpos($baseLink, 'lang='))
{
    $baseLink = substr($baseLink, 0, strpos($baseLink, 'lang='));
}

JHtml::_('stylesheet', 'administrator/modules/mod_g11nswitcheradmin/assets/css/default.css');
?>

<span class="g11nSwitcher">
    <ul>
        <?php
        foreach($languages as $language) :
            if($language['tag'] == 'xx-XX')
            continue;//J test lang


            $class =($language['tag'] == $default) ? 'selected' : '';
            ?>
                <li>
                	<a class="<?php echo $class; ?>"
                	href="<?php echo $baseLink.'lang='.$language['tag']; ?>"
                	title="<?php echo $language['name']; ?>">
                       <?php echo $language['tag']; ?>
                    </a>
            </li>
        <?php endforeach; ?>
        <li>
        	<a title="<?php echo jgettext('Clean the cache'); ?>"
        	style="color: red;"
        	href="index.php?option=com_g11n&task=deleteCache&scope=admin&retUri=<?php echo base64_encode((string)$uri); ?>">
        	CCC</a>
        </li>
    </ul>
</span>
