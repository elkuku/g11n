<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n\Parser;

use ElKuKu\G11n\G11n;
use ElKuKu\G11n\Language\Parser\Language\Po;

use ElKuKu\G11n\Support\FileInfo;
use PHPUnit\Framework\TestCase;

/**
 * Class PoTest
 *
 * @since  1
 */
class PoTest extends TestCase
{
	/**
	 * @var Po
	 */
	private $object;

	/**
	 * @var string
	 */
	private $testFile = TEST_ROOT . '/tests/testLangDir/testExtension/g11n/xx-XX/xx-XX.testExtension.po';

	/**
	 * @var FileInfo
	 */
	private $testObject;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->object = new Po;

		$testObject = json_decode(file_get_contents(TEST_ROOT . '/tests/testLangDir/test/po_good.json'));

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

		G11n::getDefault();
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testParse()
	{
		$test = $this->object->parse($this->testFile);

		$test->fileName = str_replace(TEST_ROOT, '', $test->fileName);

		$this->assertThat(
			$test,
			$this->equalTo($this->testObject)
		);

		$this->assertThat(
			\count($test->strings),
			$this->equalTo(3)
		);

		$this->assertThat(
			\count($test->stringsPlural),
			$this->equalTo(2)
		);

		$this->assertThat(
			$test->strings['Hello test second line']->string,
			$this->equalTo('Hallo Test Zweite Zeile'
			)
		);

		$this->assertThat(
			$test->head,
			$this->equalTo('msgid ""
msgstr ""
"Plural-Forms: nplurals=2; plural=(n != 1);\n"
"Language: xx_XX\n"
'
			)
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
		$test = $this->object->parse(TEST_ROOT . '/tests/testLangDir/test/po_empty.po');

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
	public function testGenerate()
	{
		$this->assertThat(
			$this->object->generate($this->object->parse($this->testFile), new \stdClass),
			$this->equalTo(file_get_contents(TEST_ROOT . '/tests/testLangDir/test/po_good.po'))
		);
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testGetExt()
	{
		$this->assertThat(
			$this->object->getExt(),
			$this->equalTo('po')
		);
	}

	/**
	 * Test method
	 *
	 * @return void
	 */
	public function testToString()
	{
		$this->assertThat(
			(string) $this->object,
			$this->equalTo(\ElKuKu\G11n\Language\Parser\Language::class)
		);
	}
}
