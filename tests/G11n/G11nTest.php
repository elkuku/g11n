<?php
/**
 * Part of the G11n Package.
 *
 * @copyright  since 2016 Nikolai Plath
 * @license    GNU General Public License version 2 or later
 */

namespace ElKuKu\G11n\Tests\G11n;

use g11n\g11n;

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
		g11n::setCurrent('xx-XX');
		g11n::setCacheDir(__DIR__ . '/../cache');
		g11n::cleanCache();
		g11n::addDomainPath('testDomain', __DIR__ . '/../testLangDir');
		g11n::loadLanguage('testExtension', 'testDomain');
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
			g11n::get('lang'),
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
		g11n::get('somethingInvalid');
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
			g11n::getDefault(),
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
		g11n::setDefault('yy-YY');

		$this->assertThat(
			g11n::getDefault(),
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
			g11n::getCurrent(),
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
		g11n::setCurrent('');
		g11n::getCurrent();
	}

	/**
	 * Test method.
	 *
	 * @expectedException \g11n\g11nException
	 *
	 * @return void
	 */
	public function testInvalidCacheDir()
	{
		g11n::setCacheDir('INVALID');
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
			g11n::getJavaScript(),
			$this->equalTo($js)
		);
	}
}
