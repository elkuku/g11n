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
class g11nListViewg11nList extends JView
{
    protected $languages = array();
    /**
     * g11nList view display method
     *
     * @return void
     **/
    public function display($tpl = null)
    {
        JToolBarHelper::title(jgettext('Projects Manager'), 'locale');
        JToolBarHelper::addNew('g11n.add', jgettext('New'));
        JToolBarHelper::editList('g11n.edit', jgettext('Edit'));
        JToolBarHelper::deleteList('', 'g11n.remove', jgettext('Delete'));

        $language = JFactory::getLanguage();

        $this->languages = $this->get('languages');

        $cachedFiles = $this->get('CachedFiles');

        $this->scopes = array('admin' => JPATH_ADMINISTRATOR, 'site' => JPATH_SITE);

        //-- Get data from the model
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

            $items[$i]->cacheLinks = array();

            $s = jgettext('Not cached');

            $extensionName = $item->extension;

            if(strpos($extensionName, '.'))
            $extensionName = substr($extensionName, 0, strpos($extensionName, '.'));

            foreach($this->scopes as $scope => $path)
            {
               # $items[$i]->cacheLinks[$scope] = array();

                try//
                {
                    $items[$i]->templateStatus[$scope] = g11nStorage::templateExists($item->extension, $scope);
                }
                catch(Exception $e)
                {
                    $items[$i]->templateStatus[$scope] = $e->getMessage();
                    echo '';
                }//try

                foreach($this->languages[$scope] as $lang)
                {
                    if($lang['tag'] == 'xx-XX')
                    continue;

                    $exists = g11nExtensionHelper::findLanguageFile($lang['tag']
                    , $item->extension, $scope);

                    $items[$i]->fileStatus[$scope][$lang['tag']] =($exists) ? true : false;
//                     g11nExtensionHelper::findLanguageFile($lang['tag']
//                    , $item->extension, $scope);

                    if( ! array_key_exists($extensionName, $cachedFiles)
                    || ! array_key_exists($scope, $cachedFiles[$extensionName]))
                    {
                        $items[$i]->cacheStatus[$scope][$lang['tag']] = false;//array(jgettext('Not cached'));
                        continue;
                    }

                    $s = jgettext('Not cached');
                    $items[$i]->cacheStatus[$scope][$lang['tag']] = false;

                    $fName = $lang['tag'].'.'.$item->extension;

                    foreach($cachedFiles[$extensionName][$scope] as $file)
                    {
                        if(strpos($file, $fName) === 0)
                        {
                            $s = jgettext('Cached');
                            $items[$i]->cacheStatus[$scope][$lang['tag']] = true;
                        }
                    }//foreach
                }//foreach
            }//foreach
        }//foreach

        $this->assignRef('items', $items);

        parent::display($tpl);
    }//function
}//class
