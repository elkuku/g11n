<?php
/**
 * @copyright  since 2010 Nikolai Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

namespace g11n\Language\Storage\Database;

use g11n\G11nException;
use g11n\Language\Storage;

/**
 * g11nStorageDB class
 *
 * @since  1
 */
class Mysql extends Storage\File
{
	/**
	 * Stores the strings into a storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 * @return boolean
	 */
	public function store($lang, $extension, $domain = '')
	{
		if (self::$storage == 'off')
		{
			return false;
		}

		$profiler = JProfiler::getInstance('LangDebug');
		$profiler->mark('store: ' . $extension);

		$strings = self::parseFile($fileName);

		switch (self::$storage)
		{
			case 'db':
				$jsonString = json_encode($strings);

				$query = $this->db->getQuery(true);

				$query->insert('`#__language_strings`');
				$query->set('extension = ' . $this->db->quote('system'));
				$query->set('lang = ' . $this->db->quote($lang));
				$query->set('scope = ' . $this->db->quote($this->scope));

				// To quote or not to quote..
//                #$query->set("strings = '".($encoded))."'";
				$query->set('strings = ' . $this->db->quote($jsonString));

				$this->db->setQuery($query);

				$this->db->query();

				if ($this->db->getError())
				{
					$this->setError($this->db->getError());

					$profiler->mark('<span style="color: red;">store db failed **********</span>: '
					. $extension
					);

					return false;
				}

				$profiler->mark('store query: ' . htmlentities($query));

				break;

			default:
				throw new G11nException('Undefined storage: ' . self::$storage);

				break;
		}

		$profiler->mark('store SUCCESS ++++: ' . $extension);

		return true;
	}

	/**
	 * Retrieve the storage content.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 * @return \g11n\Support\Store
	 */
	public function retrieve($lang, $extension, $domain = '')
	{
		if (self::$storage == 'off')
		{
			return false;
		}

		$profiler = JProfiler::getInstance('LangDebug');
		$profiler->mark('start: ' . $extension);

		$this->query->clear('where');

		$this->query->where('extension = ' . $this->db->quote($extension));
		$this->query->where('lang = ' . $this->db->quote($lang));
		$this->query->where('scope = ' . $this->db->quote($this->scope));

		$this->db->setQuery($this->query);

		$e = $this->db->loadObject();

		if (empty($e->strings))
		{
			$profiler->mark('<span style="color: red;">langload db failed ****</span>'
			. $this->query
			);

			$this->setError($this->db->getError());

			return false;
		}

		$strings = json_decode($e->strings, true);

		$profiler->mark('<span style="color: green;">*Loaded db*</span>');

		$this->strings = array_merge($this->strings, $strings);

		// Language overrides
		$this->strings = array_merge($this->strings, $this->override);

		$this->paths[$extension][$fileName] = true;

		return true;
	}

	/**
	 * Cleans the storage.
	 *
	 * @param   string  $lang       E.g. de-DE, es-ES etc.
	 * @param   string  $extension  E.g. joomla, com_weblinks, com_easycreator etc.
	 * @param   string  $domain     Must be 'admin' or 'site'.
	 *
	 * @throws G11nException
	 * @return void
	 */
	public function clean($lang, $extension, $domain = '')
	{
		// TODO: Implement clean() method.
	}
}
