<?php
/**
 * @version SVN: $Id$
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
 * g11n Controller.
 *
 * @package    g11n
 * @subpackage Controllers
 */
class g11nListControllerCache extends JController
{
    public function display($cachable = null, $urlparams = null)
    {
        $model = $this->getModel('g11nList', 'g11nListModel');

        $view = $this->getView('Cache', 'html', 'g11nListView');
        $view->setModel($model, true);
        $view->display($cachable, $urlparams);

        //-- this one is only for the submenu :|
        JRequest::setVar('view', 'cache');
    }//function

    public function clean()
    {
        echo 'RR';
        var_dump($_REQUEST);
    }//function

    public function cleanAllAdmin()
    {
        echo 'ALLLL admin';
        var_dump($_REQUEST);
    }//function

    public function cleanAllSite()
    {
        echo 'ALLLL site';
        var_dump($_REQUEST);
    }//function

    public function cache()
    {
        echo 'POOP';
    }//function
}//class
