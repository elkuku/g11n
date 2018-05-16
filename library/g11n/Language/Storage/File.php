<?php
/**
 * @copyright  since 2013 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace ElKuKu\G11n\Language\Storage;

use ElKuKu\G11n\Language\Storage;

/**
 * Class File
 *
 * @since  1
 */
abstract class File extends Storage
{
	/**
	 * The cache directory
	 * @var string
	 */
	public static $cacheDir = '/tmp';
}
