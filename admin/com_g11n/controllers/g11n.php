<?php
/**
 * @package    g11n
 * @subpackage Controllers
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

jimport('joomla.application.component.controller');

/**
 * The g11n Controller.
 *
 * @package    g11n
 * @subpackage Controllers
 */
class g11nListControllerg11n extends JController
{
    /**
     * constructor (registers additional tasks to methods).
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        //-- Register Extra tasks
        $this->registerTask('add', 'edit');
    }//function

    /**
     * display the edit form.
     *
     * @return void
     */
    public function edit()
    {
        JRequest::setVar('view', 'g11n');
        JRequest::setVar('layout', 'form');
        JRequest::setVar('hidemainmenu', 1);

        parent::display();
    }//function

    /**
     * Save a record (and redirect to main page).
     *
     * @return void
     */
    public function save()
    {
        $model = $this->getModel('g11n');
        $link = 'index.php?option=com_g11n';

        if($model->store())
        {
            $msg = jgettext('Record saved');
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = $model->getError();
            $this->setRedirect($link, $msg, 'error');
        }
    }//function

    /**
     * Remove record(s).
     *
     * @return void
     */
    public function remove()
    {
        $model = $this->getModel('g11n');
        $link = 'index.php?option=com_g11n';

        if($model->delete())
        {
            $msg = JText::_('Records deleted');
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = JText::sprintf('One or more records could not be deleted: ', $model->getError());
            $this->setRedirect($link, $msg, 'error');
        }
    }//function

    /**
     * Cancel editing a record.
     *
     * @return void
     */
    public function cancel()
    {
        $msg = JText::_('Operation Cancelled');
        $this->setRedirect('index.php?option=com_g11n', $msg, 'notice');
    }//function
}//class
