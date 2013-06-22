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

class g11nListViewg11n extends JView
{
    /**
     * g11n view display method.
     *
     * @return void
     **/
    public function display($tpl = null)
    {
        //-- Get the g11n
        $g11n = $this->get('Data');
        $isNew = ($g11n->id < 1);

        $text = $isNew ? jgettext('New') : jgettext('Edit');
        JToolBarHelper::title('g11n: <small><small>[ '.$text.' ]</small></small>');
        JToolBarHelper::save('g11n.save', jgettext('Save'));

        if($isNew)
        {
            JToolBarHelper::cancel('cancel', jgettext('Cancel'));
        }
        else//
        {
            //-- For existing items the button is renamed `close`
            JToolBarHelper::cancel('cancel', jgettext('Close'));
        }

        $this->assignRef('g11n', $g11n);

        parent::display($tpl);
    }
}
