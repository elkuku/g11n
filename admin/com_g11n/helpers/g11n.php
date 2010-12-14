<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Helpers
 * @author     Nikolai Plath (elkuku) {@link http://www.nik-it.de NiK-IT.de}
 * @author     Created on 04-Dec-2010
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * g11n helper class.
 */
class g11nHelper
{
    /**
     * Configure the Linkbar.
     *
     * @param	string	The name of the active view.
     * @since	1.6
     */
    public static function addSubmenu()
    {
        $vName = JRequest::getCmd('view', 'g11nlist');

        JSubMenuHelper::addEntry(jgettext('Projects')
        , 'index.php?option=com_g11n',
        $vName == 'g11nlist'
        );

        JSubMenuHelper::addEntry(jgettext('Cache')
        , 'index.php?option=com_g11n&task=cache.display',
        $vName == 'cache'
        );

        JSubMenuHelper::addEntry(jgettext('Utility')
        , 'index.php?option=com_g11n&task=utility.display',
        $vName == 'utility'
        );

        //        if ($vName=='categories') {
        //            JToolBarHelper::title(
        //            JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE',JText::_('com_weblinks')),
        //				'weblinks-categories');
        //        }
    }//function
}//class
