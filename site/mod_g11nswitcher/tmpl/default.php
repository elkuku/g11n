<?php
/**
 * @package    g11n
 * @subpackage Modules
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 14-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

$default = g11n::getDefault();

JHtml::_('stylesheet', 'modules/mod_g11nswitcher/assets/css/default.css');
?>

<div class="g11nSwitcher">
    <ul>
        <?php
        foreach(JFactory::getLanguage()->getKnownLanguages() as $lang) :

        if($lang['tag'] == 'xx-XX')
        continue;//J test lang

        $url = JRoute::_('&lang='.$lang['tag']);

        $attribs = array();
        $attribs['class'] =($lang['tag'] == $default) ? 'selected' : '';
        $attribs['title'] = $lang['name'];
        ?>
            <li>
            	<?php echo JHtml::link($url, $lang['tag'], $attribs); ?>
        	</li>
        <?php endforeach; ?>
    </ul>
</div>
