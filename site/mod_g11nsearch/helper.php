<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');

/**
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @since		1.5
 */
class modg11nSearchHelper
{
	/**
	 * Display the search button as an image.
	 *
	 * @param	string	$button_text	The alt text for the button.
	 *
	 * @return	string	The HTML for the image.
	 * @since	1.5
	 */
	public static function getSearchImage($button_text)
	{
		return JHtml::_('image','searchButton.gif', $button_text, NULL, true, true);
	}
}