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
 * The g11n Model.
 *
 * @package    g11n
 * @subpackage Models
 */
class g11nModelg11n extends JModel
{
    /**
     * Gets the data.
     *
     * @return string The data to be displayed to the user
     */
    public function getData()
    {
        $db =& JFactory::getDBO();

        $query = 'SELECT * FROM #__g11n';
        $db->setQuery($query);
        $data = $db->loadObjectList();

        return $data;
    }
}
