<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use g11n\G11n;

use PHPUnit_Framework_TestCase;

/**
 * Class G11nTest.
 *
 * @since  1.0
 */
class G11nTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		G11n::setCurrent('xx-XX');
		G11n::setCacheDir(TEST_ROOT . '/tests/cache');
		G11n::cleanCache();
		G11n::addDomainPath('testDomain', TEST_ROOT . '/tests/testLangDir');
		G11n::loadLanguage('testExtension', 'testDomain');
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testHello()
	{
		$this->assertThat(
			g11n3t('Hello test'),
			$this->equalTo('HHHH')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testNotFound()
	{
		$this->assertThat(
			g11n3t('not found'),
			$this->equalTo('not found')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testGetsomething()
	{
		$this->assertThat(
			G11n::get('lang'),
			$this->equalTo('xx-XX')
		);
	}

	/**
	 * Test method.
	 *
	 * @expectedException \UnexpectedValueException
	 *
	 * @return void
	 */
	public function testGetsomethingInvalid()
	{
		G11n::get('somethingInvalid');
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testParams()
	{
		$this->assertThat(
			g11n3t('Hello %test% case', ['%test%' => 'TEST']),
			$this->equalTo('Hällo TEST Foo')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testParamsNotFound()
	{
		$this->assertThat(
			g11n3t('Hello %test% Case', ['%test%' => 'TEST']),
			$this->equalTo('Hello TEST Case')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testGetDefault()
	{
		$this->assertThat(
			G11n::getDefault(),
			$this->equalTo('en-GB')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testSetDefault()
	{
		G11n::setDefault('yy-YY');

		$this->assertThat(
			G11n::getDefault(),
			$this->equalTo('yy-YY')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testGetCurrent()
	{
		$this->assertThat(
			G11n::getCurrent(),
			$this->equalTo('xx-XX')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testPluralExistent()
	{
		$this->assertThat(
			g11n4t('There is One fine plural test.', 'There are %d fine plural tests.', 0),
			$this->equalTo('Es gibt %d feine Pluraltests.')
		);
		$this->assertThat(
			g11n4t('There is One fine plural test.', 'There are %d fine plural tests.', 1),
			$this->equalTo('Es gibt Einen feinen Pluraltest.')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testPluralExistentWithParameters()
	{
		$this->assertThat(
			g11n4t(
				'There is One %param% plural test.',
				'There are %d %param% plural tests.',
				0,
				['%param%' => 'TEST']
			),
			$this->equalTo('Es gibt %d TEST Pluraltests.')
		);
		$this->assertThat(
			g11n4t(
				'There is One %param% plural test.',
				'There are %d %param% plural tests.',
				1,
				['%param%' => 'TEST']
			),
			$this->equalTo('Es gibt Einen TEST Pluraltest.')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testPlural0()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 0),
			$this->equalTo('Ho')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testPlural1()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 1),
			$this->equalTo('Hey')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testPlural2()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 2),
			$this->equalTo('Ho')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testPlural3()
	{
		$this->assertThat(
			g11n4t('Hey', 'Ho', 3),
			$this->equalTo('Ho')
		);
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testDetectLanguage()
	{
		G11n::setCurrent('');
		G11n::getCurrent();
	}

	/**
	 * Test method.
	 *
	 * @expectedException \g11n\G11nException
	 *
	 * @return void
	 */
	public function testInvalidCacheDir()
	{
		G11n::setCacheDir('INVALID');
	}

	/**
	 * Test method.
	 *
	 * @return void
	 */
	public function testGetJavaScript()
	{
		$js = "<!--
/* JavaScript translations */
g11n.debug = ''
g11n.loadLanguageStrings([]);
g11n.loadPluralStrings([]);
g11n.setPluralFunction(phpjs.create_function('n', 'plural = (n == 1 ? 0 : 1); return (plural <= 2)? plural : plural - 1;'))
-->";
		$this->assertThat(
			G11n::getJavaScript(),
			$this->equalTo($js)
		);
	}
}
