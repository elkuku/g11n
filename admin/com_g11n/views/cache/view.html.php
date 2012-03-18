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
class g11nListViewCache extends JView
{
    protected $languages = array();
    /**
     * g11nList view display method
     *
     * @return void
     **/
    public function display($tpl = null)
    {
        //        //-- Get data from the model
        //        $model = $this->getModel('g11nlist');
        //        var_dump($model);
        //        $this->setModel($model, true);
        $items = $this->get('Data');
        #  parent::display();
        #       return;
        JToolBarHelper::title(jgettext('Cache Manager'), 'cache');
        JToolBarHelper::trash('cache.clean', jgettext('Clean cache'));

        //        $language = JFactory::getLanguage();
        //
        //        $this->languages['admin'] = $language->getKnownLanguages(JPATH_ADMINISTRATOR);
        //        $this->languages['site'] = $language->getKnownLanguages(JPATH_SITE);

        $this->languages = $this->get('languages');

        $cachedFiles = $this->get('CachedFiles');

        $this->scopes = array('admin' => JPATH_ADMINISTRATOR, 'site' => JPATH_SITE);

        $baseLink = 'index.php?option=com_g11n';

        foreach($items as $i => $item)
        {
            $scope =($items[$i]->scope) ? $items[$i]->scope : 'admin';

            $items[$i]->exists = g11nExtensionHelper::isExtension($item->extension, $scope);
            $items[$i]->editLink = $baseLink.'&controller=g11n&task=edit&cid[]='.$item->id;

            $items[$i]->cacheLinks = array();

            $s = jgettext('Not cached');

            foreach($this->scopes as $scope => $path)
            {
                $items[$i]->cacheLinks[$scope] = array();

                foreach($this->languages[$scope] as $lang)
                {
                    if($lang['tag'] == 'xx-XX')
                    continue;

                    $exists = g11nExtensionHelper::findLanguageFile($lang['tag']
                    , $item->extension, $scope);

                    $items[$i]->cacheStatus[$scope][$lang['tag']] = false;

                    if( ! array_key_exists($item->extension, $cachedFiles)
                    || ! array_key_exists($scope, $cachedFiles[$item->extension]))
                    {
                        $items[$i]->cacheLinks[$scope][$lang['tag']] = array(jgettext('Not cached'));
                        continue;
                    }

                    $s = jgettext('Not cached');

                    foreach($cachedFiles[$items[$i]->extension][$scope] as $file)
                    {
                        if(strpos($file, $lang['tag']) === 0)
                        {
                            $items[$i]->cacheStatus[$scope][$lang['tag']] = true;
                            $link = $baseLink.'&task=cleanCache&extension='.$items[$i]->extension;
                            $link .= '&scope='.$scope.'&file='.$file;
                            $s = '<a  class="action cleanCache" href="'.$link.'" title="'.sprintf(jgettext('Clean cached file: %s'), $file).'">'
                            .jgettext('Clean cache').'</a>';
                        }
                    }//foreach

                    $items[$i]->cacheLinks[$scope][$lang['tag']][] = $s;
                }//foreach
            }//foreach
        }//foreach

        $this->assignRef('items', $items);

        parent::display($tpl);
    }//function
}//class
