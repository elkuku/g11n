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

foreach($this->data as $data) :
?>

<h1><?php echo $data->greeting; ?></h1>

<?php
endforeach;