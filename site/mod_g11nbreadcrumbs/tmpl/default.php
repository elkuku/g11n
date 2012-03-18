<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_breadcrumbs
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');
?>

<div class="breadcrumbs<?php echo $params->get('moduleclass_sfx'); ?>">

<?php
if($params->get('showHere', 1))
echo jgettext('You are here: ');

for($i = 0; $i < $count; $i ++)
{
    // If not the last item in the breadcrumbs add the separator
    if($i < $count - 1)
    {
        if( ! empty($list[$i]->link))
        {
            $class =(isset($list[$i]->class) && $list[$i]->class) ? ' '.$list[$i]->class : '';

            echo '<a href="'.$list[$i]->link.'" class="pathway'.$class.'">'.jgettext($list[$i]->name).'</a>';
        }
        else
        {
            echo '<span>';
            echo jgettext($list[$i]->name);
            echo '</span>';
        }

        if($i < $count - 2)
        echo ' '.$separator.' ';
    }
    else if($params->get('showLast', 1))
    {
        if($i > 0)
        echo ' '.$separator.' ';

        echo '<span>';
        echo jgettext($list[$i]->name);
        echo '</span>';
    }
}//for
?>

</div>
