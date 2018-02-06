<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n\Parser;

use ElKuKu\G11n\G11n;
use ElKuKu\G11n\Language\Parser\Language\Ini;

use ElKuKu\G11n\Support\FileInfo;
use PHPUnit\Framework\TestCase;

/**
 * Class IniTest
 *
 * @since  1
 */
class IniTest extends TestCase
{
	/**
	 * @var Ini
	 */
	private $object = null;

	/**
	 * @var FileInfo
	 */
	private $testObject = null;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->object = new Ini;

		$testObject = json_decode(file_get_contents(TEST_ROOT . '/tests/testLangDir/test/ini_good.json'));

		$fileInfo = new FileInfo;

		foreach ($testObject as $k => $v)
		{
			if (is_object($v))
			{
				$val = [];

				foreach ($v as $k1 => $v1)
				{
					$val[$k1] = $v1;
				}
			}
			else
			{
				$val = $v;
			}

			$fileInfo->$k = $val;
		}

		$this->testObject = $fileInfo;

		// Setup the G11n lib
		G11n::getDefault();
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testGenerate()
	{
		$this->assertThat(
			$this->object->generate($this->testObject, new \stdClass),
			$this->equalTo(file_get_contents(TEST_ROOT . '/tests/testLangDir/test/ini_good.ini'))
		);
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testParse()
	{
		$test = $this->object->parse(TEST_ROOT . '/tests/testLangDir/test/ini_good.ini');

		$test->fileName = str_replace(TEST_ROOT, '', $test->fileName);

		$this->assertThat(
			$test,
			$this->equalTo($this->testObject)
		);

		$this->assertThat(
			count($test->strings),
			$this->equalTo(3)
		);

		$this->assertThat(
			count($test->stringsPlural),
			$this->equalTo(0)
		);

		$this->assertThat(
			$test->strings['Hello test second line']->string,
			$this->equalTo('Hallo Test Zweite Zeile')
		);

		$this->assertThat(
			$test->head,
			$this->equalTo("# test ini file\n")
		);
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testEmpty1()
	{
		$test = $this->object->parse(TEST_ROOT . '/tests/testLangDir/test/{notfound}');
		$this->assertThat(
			count($test->strings),
			$this->equalTo(0)
		);
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testEmpty2()
	{
		$test = $this->object->parse(TEST_ROOT . '/tests/testLangDir/test/ini_empty.ini');

		$this->assertThat(
			count($test->strings),
			$this->equalTo(0)
		);
	}
}
