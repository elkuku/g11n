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

if( ! count($this->items)) :
    JError::raiseNotice(0, jgettext('Please create a new project'));
endif;
?>

<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo jgettext('ID'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value=""
				onclick="checkAll(<?php echo count($this->items); ?>);" />
			</th>
			<th>
				<?php echo jgettext('Extension'); ?>
			</th>
			<th>
				<?php echo jgettext('Scope'); ?>
			</th>
            <th style="background-color: #ffc;">
                <?php echo jgettext('Template'); ?>
            </th>
			<?php
            foreach($this->languages['all'] as $lang)
            {
                if($lang['tag'] == 'xx-XX')
                continue;


                echo '<th>';
                echo $lang['tag'];
                echo '</th>';
            }//foreach
            ?>
		</tr>
	</thead>
	<?php
    $k = 0;
    for($i = 0, $n = count($this->items); $i < $n; $i ++):
        $item = $this->items[$i];
        $checked = JHTML::_('grid.id', $i, $item->id);
        $checkDrawn = false;

        foreach($this->languages as $scope => $langs) :
            if('all' == $scope)
            continue;

            if($item->scope
            && $item->scope != $scope)
            continue;
        ?>
		<tr class="<?php echo "row$k"; ?>">
			<?php if($checkDrawn) : ?>
				<td colspan="3">&nbsp;</td>
			<?php else : ?>
			<td>
				<?php echo ($checkDrawn) ? $item->id : '&nbsp;'; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $item->editLink; ?>"><?php echo $item->extension; ?></a>
                <?php if( ! $item->exists) echo '<b style="color: red;">'.jgettext('Invalid extension').'</b>'; ?>
			</td>
			<?php $checkDrawn = true;?>
			<?php endif; ?>
			<td>
				<?php echo $scope; ?>
			</td>
			<?php
                    echo '<td style="text-align: center;">';

                    if($item->templateStatus[$scope])
                    {
                        $s = jgettext('Found');
                        $class = ' found';
                    }
                    else//
                    {
                        $s = jgettext('Not found');
                        $class = ' notfound';
                    }

                    if($item->scope != '' && $item->scope != $scope || ! $item->exists) :
                    else :
                        echo '<span class="status '.$class.'" alt="'.$s.'" title="'.$s.'" />';
                    endif;

                    echo '</td>';

                foreach ($langs as $lang) :
                    if($lang['tag'] == 'xx-XX')
                    continue;

                    echo '<td>';

                    if($item->scope != '' && $item->scope != $scope || ! $item->exists) :
                    else :
                        if($item->fileStatus[$scope][$lang['tag']])
                        {
                            $s = jgettext('Found');
                            $class = ' found';
                            echo '<span class="status '.$class.'" alt="'.$s.'" title="'.$s.'" />';

                            if($item->cacheStatus[$scope][$lang['tag']])
                            {
                                $s = jgettext('Cached');
                            }
                            else//
                            {
                                $s = jgettext('Not cached');
                                $class = ' notfound';
                            }

                            echo '<span class="status '.$class.'" alt="'.$s.'" title="'.$s.'" />';
                        }
                        else//
                        {
                            $s = jgettext('Not found');
                            echo '<span class="status notfound" alt="'.$s.'" title="'.$s.'" />';
                        }

                        echo '<br />';

                        endif;

                    echo '</td>';
                endforeach;
            endforeach;
            ?>
		</tr>
		<?php
        $k = 1 - $k;
    endfor;
    ?>
	</table>
</div>

<input type="hidden" name="option" value="com_g11n" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="g11n" />
</form>

<?php
