[![Build Status](https://travis-ci.org/elkuku/g11n.svg?branch=master)](https://travis-ci.org/elkuku/g11n)

# G11n

The G11n language library brings multilanguage functionality for "small to mid-sized" PHP projects.

## Installation

The library is installed using [Composer](http://getcomposer.org/)

```
"require": {
	"elkuku/g11n": "3.*"
}
```

## Usage example

At a very minimal do the following:

1. Create a composer project
1. Add the library
1. Create a `test.php` at the root of your repository (for demo purpose)
1. Create a language file `de-DE.testExtension.po` (see screen shot)

![2016-07-16-175321_1366x768_scrot](https://cloud.githubusercontent.com/assets/33978/16897693/bcf573dc-4b7e-11e6-8a4e-999349e1bb3f.png)

`test.php`
```php
#!/usr/bin/env php
<?php

namespace Test;

use ElKuKu\G11n\G11n;
use ElKuKu\G11n\Support\ExtensionHelper;

include 'vendor/autoload.php';

ExtensionHelper::setCacheDir('cache');
ExtensionHelper::addDomainPath('someName', 'languageDir');

G11n::setCurrent('de-DE');
G11n::loadLanguage('testExtension', 'someName');

echo g11n3t('Hello test');
```

`de-DE.testExtension.po`
```po
msgid ""
msgstr ""
"Language: de-DE\n"

msgid "Hello test"
msgstr "Hallo Test"
```

Run the script.<br />
The output should be: `Hallo Test`

**Note** that language files will be parsed and cached, so you have to run the command

```php
ExtensionHelper::cleanCache();
```

To see the changes.

## Use cases

* The Joomla! Tracker Application - https://github.com/joomla/jissues
    * There is also some documentation: https://github.com/joomla/jissues/blob/master/Documentation/Internationalisation/Localisation.md
