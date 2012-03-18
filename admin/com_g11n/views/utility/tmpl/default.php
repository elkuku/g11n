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
    JFactory::getApplication()->enqueueMessage(jgettext('Please create a new project'));
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
#var_dump($item);
        $checkDrawn = false;

        foreach(array_keys($this->languages) as $scope)
        {
            if($scope == 'all')
            continue;

            if($item->scope
            && $item->scope != $scope)
            continue;

            ?>
		<tr class="<?php echo "row$k"; ?>">
			<?php if($checkDrawn) : ?>
				<td colspan="2">&nbsp;</td>
			<?php else : ?>
    			<td>
    				<?php echo $item->id; ?>
    			</td>
    			<td>
    				<?php echo $item->extension; ?>
                    <?php if( ! $item->exists) echo '<b style="color: red;">'.jgettext('Invalid extension').'</b>'; ?>
    			</td>
			<?php $checkDrawn = true; ?>
			<?php endif; ?>
			<td>
				<?php echo $scope; ?>
			</td>
			<?php
            $sS = '';

            foreach($this->languages[$scope] as $lang) :
                if($sS != $scope) :
                    echo '<td>';

                    if($item->scope != '' && $item->scope != $scope || ! $item->exists) :
                    else :
                        if($item->templateLink) :
                            if($item->templateExists[$scope]) :
                                $class = 'action update';
                                $s = jgettext('Update');
                            else :
                                $class = 'action create';
                                $s = jgettext('Create');
                            endif;

                            echo '<a class="'.$class.'" href="'.$item->templateLink.'&scope='.$scope.'">';
                            echo $s.'</a>';
                        else :
                            echo $item->templateCommands[$scope];
                        endif;
                    endif;

                    echo '</td>';
                    $sS = $scope;
                endif;

                    if($lang['tag'] == 'xx-XX')
                    continue;

                    echo '<td>';

                    if($item->scope != '' && $item->scope != $scope || ! $item->exists) :
                    else :
                            if($item->lngExists[$scope][$lang['tag']]) :
                                $class = 'action update';
                                $s = jgettext('Update');
                            else :
                                $class = 'action create';
                                $s = jgettext('Create');
                            endif;
                    echo '<a class="'.$class.'" href="'.$item->updateLinks[$scope][$lang['tag']].'">';
                        echo $s;
                        echo '</a>';
                    endif;

                    echo '</td>';
            endforeach;
            ?>
		</tr>
		<?php
        }//foreach
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
