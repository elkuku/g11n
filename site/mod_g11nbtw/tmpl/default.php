<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');
?>

<div class="g11nbtw<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
echo sprintf(jgettext('<b>BTW</b>: g11n means "%s"')
, sprintf('<a href="%s" class="external">'.jgettext('globalization').'</a>'
, 'http://en.wiktionary.org/wiki/g11n'));

?>
</div>
