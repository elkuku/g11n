<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Tables
 * @author     EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}
 * @author     Nikolai Plath {@link http://www.easy-joomla.org}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * g11n Table class.
 *
 * @package    g11n
 * @subpackage Tables
 */
class Tableg11n extends JTable
{
    /**
     * Constructor
     *
     * @param object $db Database connector object
     */
    public function __construct(& $db)
    {
        parent::__construct('#__g11n', 'id', $db);
    }//function
}//class
