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

JHtml::_('stylesheet', 'modules/mod_g11nswitcher/assets/css/default.css');
?>

<div class="g11nSwitcher">
    <ul>
        <?php
        foreach($languages as $language) :
        if($language['tag'] == 'xx-XX')
        continue;//J test lang
        $class =($language['tag'] == $default) ? 'selected' : '';
        ?>
            <li>
            	<a class="<?php echo $class; ?>"
            	href="<?php echo JRoute::_('&lang='.$language['tag']); ?>"
            	title="<?php echo $language['name']; ?>">
                   <?php echo $language['tag']; ?>
                </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
