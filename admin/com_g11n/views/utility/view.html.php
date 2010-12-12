<?php
/**
 * @version SVN: $Id$
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
class g11nListViewG11n extends JView
{
    protected $languages = array();
    /**
     * g11nList view display method
     *
     * @return void
     **/
    public function display($tpl = null)
    {
        JToolBarHelper::title(jgettext('g11n Manager'), 'langs');

        $language = JFactory::getLanguage();

        $this->languages = $this->get('languages');

        $this->scopes = array('admin' => JPATH_ADMINISTRATOR, 'site' => JPATH_SITE);

        $items = $this->get('Data');

        $baseLink = 'index.php?option=com_g11n';

        foreach($items as $i => $item)
        {
            $scope =($items[$i]->scope) ? $items[$i]->scope : 'admin';

            $items[$i]->exists = g11nExtensionHelper::isExtension($item->extension, $scope);
            $items[$i]->editLink = $baseLink.'&task=g11n.edit&cid[]='.$item->id;

            $items[$i]->templateLink =($items[$i]->exists)
            ? $baseLink.'&task=g11n.createTemplate&extension='.$item->extension
            : '';

            $items[$i]->templateCommands = array();

            $items[$i]->updateLinks = array();

            foreach($this->scopes as $scope => $path)
            {
                try//
                {
                    $items[$i]->templateExists = g11nStorage::templateExists($item->extension, $scope);
                }
                catch(Exception $e)
                {
                    $items[$i]->templateCommands[$scope] = $e->getMessage();
                    $items[$i]->templateLink = '';
                    echo '';
                }//try

                foreach($this->languages[$scope] as $lang)
                {
                    if($lang['tag'] == 'xx-XX')
                    continue;

                    $exists = g11nExtensionHelper::findLanguageFile($lang['tag']
                    , $item->extension, $scope);

                    $link = $baseLink.'&task=g11n.updateLanguage';
                    $link .= '&extension='.$items[$i]->extension.'&scope='.$scope;
                    $link .= '&langTag='.$lang['tag'];

                    $items[$i]->lngExists[$scope][$lang['tag']] = $exists;

                  $items[$i]->updateLinks[$scope][$lang['tag']] = $link;
//                    $items[$i]->updateCommands[$scope][$lang['tag']] =($exists)
//                    ? jgettext('Update')
//                    : jgettext('Create');
                }//foreach
            }//foreach
        }//foreach

        $this->assignRef('items', $items);

        parent::display($tpl);
    }//function
}//class
