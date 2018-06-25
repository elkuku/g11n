[![Build Status](https://travis-ci.org/elkuku/g11n.svg?branch=master)](https://travis-ci.org/elkuku/g11n)

# G11n

The G11n language library brings multilanguage functionality for "small to mid-sized" PHP projects.

## Installation

The library is installed using [Composer](http://getcomposer.org/)


    composer require elkuku/g11n

## Usage example

At a very minimal do the following:

1. Create a composer project
1. Add the library
1. Create a `/test.php` file at the root of your repository (for demo purpose)
1. Create the directories `/someDir/someExtension/g11n/de-DE`
1. Create a language file `de-DE.someExtension.po` (see screen shot)
1. Create a `/cache` directory

![files-2](https://user-images.githubusercontent.com/33978/41873132-b88f4572-7889-11e8-92d3-d802d03e7e72.png)

#### `test.php`

```php
#!/usr/bin/env php
<?php

namespace Test;

use ElKuKu\G11n\G11n;
use ElKuKu\G11n\Support\ExtensionHelper;

include 'vendor/autoload.php';

ExtensionHelper::setCacheDir('cache');
ExtensionHelper::addDomainPath('someName', 'someDir');

G11n::setCurrent('de-DE');
G11n::loadLanguage('someExtension', 'someName');

echo g11n3t('Hello test');
```

#### `de-DE.someExtension.po`

```po
msgid ""
msgstr ""
"Language: de-DE\n"

msgid "Hello test"
msgstr "Hallo Test"
```

Run the script.

```
$ ./test.php
Hallo Test
```
The output should be: `Hallo Test`

**Note** that language files will be parsed and cached, so you have to run the command

```php
ExtensionHelper::cleanCache();
```

To see the changes.

## Use cases

* The Joomla! Tracker Application - https://github.com/joomla/jissues
    * There is also some documentation: https://github.com/joomla/jissues/blob/master/Documentation/Internationalisation/Localisation.md
