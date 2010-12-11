<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Views
 * @author     EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}
 * @author     Nikolai Plath {@link http://www.easy-joomla.org}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

jimport('joomla.application.component.view');

/**
 * HTML View class for the g11n Component.
 *
 * @package    g11n
 * @subpackage Views
 */

class g11nViewg11n extends JView
{
    /**
     * g11n view display method
     * @return void
     **/
    public function display($tpl = null)
    {
        $data = $this->get('Data');
        $this->assignRef('data', $data);

        parent::display($tpl);
    }//function
}//class
