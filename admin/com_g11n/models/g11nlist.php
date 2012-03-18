<?php
/**
 * @package    g11n
 * @subpackage Models
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

jimport('joomla.application.component.model');

/**
 * The g11nList Model.
 *
 * @package    g11n
 * @subpackage Models
 */
class g11nListModelg11nList extends JModel
{
    /**
     * g11nList data array
     *
     * @var array
     */
    private $_data;

    /**
     * Retrieves the hello data.
     *
     * @return array Array of objects containing the data from the database
     */
    public function getData()
    {
        //-- Lets load the data if it doesn't already exist
        if(empty($this->_data))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query);
        }

        return $this->_data;
    }//function

    public static function getCachedFiles()
    {
        $paths = array(JPATH_ADMINISTRATOR, JPATH_SITE);

        $cachedFiles = array();

        foreach($paths as $path)
        {
            $scope =($path == JPATH_ADMINISTRATOR) ? 'admin' : 'site';

            $cachePath = $path.'/'.g11nStorage::getCacheDir();

            if( ! JFolder::exists($cachePath))
            {
                $cachedFiles[$scope] = array();

                continue;
            }

            $extensions = JFolder::folders($cachePath);

            foreach($extensions as $extension)
            {
                $cachedFiles[$extension][$scope] = JFolder::files($cachePath.'/'.$extension);
            }//foreach
        }//foreach

        return $cachedFiles;
    }//function

    public static function getLanguages()
    {
        $languages = array();

        $language = JFactory::getLanguage();

        $languages['admin'] = $language->getKnownLanguages(JPATH_ADMINISTRATOR);
        $languages['site'] = $language->getKnownLanguages(JPATH_SITE);

        $languages['all'] = $languages['site'];

        if(count($languages['admin']) > count($languages['site']))
        {
            $languages['all'] = $languages['admin'];
        }

        return $languages;
    }//function

    /**
     * Returns the query.
     *
     * @return string The query to be used to retrieve the rows from the database
     */
    private function _buildQuery()
    {
        $query = ' SELECT * '
        . ' FROM #__g11n ';

        return $query;
    }//function
}//class
