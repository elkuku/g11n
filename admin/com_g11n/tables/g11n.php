<?php
/**
 * @package    g11n
 * @subpackage Tables
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

/**
 * The g11n Table class.
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
    }
}
