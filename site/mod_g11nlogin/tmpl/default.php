<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');

JHtml::_('stylesheet', 'modules/mod_g11nlogin/assets/css/default.css');
JHtml::_('behavior.keepalive');
?>
<h3><?php echo jgettext('Login'); ?></h3>
<div class="g11nLogin">
<?php if ($type == 'logout') : ?>
<div class="avatar">
    <?php echo $avatar; ?>
</div>
<form action="index.php" method="post" name="form-login" id="login-form">
<?php if ($params->get('greeting')) : ?>
	<div class="login-greeting">
	<?php
    if($params->get('name') == 0) :
        echo sprintf(jgettext('Hello %s'), $user->get('name'));
    else :
        echo sprintf(jgettext('Hello %s'), $user->get('username'));
    endif; ?>
	</div>
<?php endif; ?>
	<div class="logout-button">
		<input type="submit" name="Submit" class="button" value="<?php echo jgettext('Log out'); ?>" />
	</div>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.logout" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php else : ?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>"
method="post" name="form-login" id="login-form" >
	<div>
	<div class="pretext">
	<?php echo $params->get('pretext'); ?>
	</div>
	<fieldset class="userdata">
	<p id="form-login-username">
		<label for="modlgn-username"><?php echo jgettext('User Name'); ?></label>
		<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" />
	</p>
	<p id="form-login-password">
		<label for="modlgn-passwd"><?php echo jgettext('Password'); ?></label>
		<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18"  />
	</p>
	<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
		<label for="modlgn-remember"><?php echo jgettext('Remember Me'); ?></label>
		<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo jgettext('Log in'); ?>" />
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</fieldset>
	<ul>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo jgettext('Forgot your password ?'); ?></a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo jgettext('Forgot your username ?'); ?></a>
		</li>
		<?php
        $usersConfig = JComponentHelper::getParams('com_users');
        if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo jgettext('Create an account'); ?></a>
		</li>
		<?php endif; ?>
	</ul>
	<div class="posttext">
	<?php echo $params->get('posttext'); ?>
	</div>
</div>
</form>
<?php endif; ?>
</div>
<?php
