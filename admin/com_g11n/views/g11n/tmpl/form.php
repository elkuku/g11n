<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Views
 * @author     EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}
 * @author     Nikolai Plath {@link http://www.easy-joomla.org}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="col100">
    	<fieldset class="adminform">
    		<legend><?php echo jgettext('Details'); ?></legend>

    		<table class="admintable">
    		<tr>
    			<td width="100" align="right" class="key">
    				<label for="extension">
    					<?php echo jgettext('Extension'); ?>:
    				</label>
    			</td>
    			<td>
    				<input class="text_area" type="text" name="extension" id="extension" size="32"
    				maxlength="250" value="<?php echo $this->g11n->extension;?>" />
    			</td>
    		</tr>
    		<tr>
    			<td width="100" align="right" class="key">
    				<label for="scope">
    					<?php echo jgettext('Scope'); ?>:
    				</label>
    			</td>
    			<td>
    				<input class="text_area" type="text" name="scope" id="scope" size="22"
    				maxlength="250" value="<?php echo $this->g11n->scope;?>" />
    			</td>
    		</tr>
    	</table>
    	</fieldset>
    </div>
    <div class="clr"></div>

    <input type="hidden" name="option" value="com_g11n" />
    <input type="hidden" name="id" value="<?php echo $this->g11n->id; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="controller" value="g11n" />
</form>