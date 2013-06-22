<?php
/**
 * @package    g11n
 * @subpackage Views
 * @author     Nikolai Plath {@link http://nik-it.de}
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
    }
}
