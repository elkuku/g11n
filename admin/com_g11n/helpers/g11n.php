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
 * The g11n helper class.
 */
class g11nHelper
{
    /**
     * Configure the Linkbar.
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

        JSubMenuHelper::addEntry(jgettext('g11n')
        , 'index.php?option=com_g11n&task=utility.display',
        $vName == 'utility'
        );
    }//function
}//class
