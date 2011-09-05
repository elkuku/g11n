<?php
/**
 * @version SVN: $Id$
 * @package    g11n
 * @subpackage Controllers
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 23-Nov-2010
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

jimport('joomla.application.component.controller');

/**
 * The g11n Controller.
 *
 * @package    g11n
 * @subpackage Controllers
 */
class g11nListControllerUtility extends JController
{
    private $g11nView = null;

    public function __construct()
    {
        parent::__construct();

        $model = $this->getModel('g11nList', 'g11nListModel');

        $this->g11nView = $this->getView('Utility', 'html', 'g11nListView');
        $this->g11nView->setModel($model, true);
    }//function
    public function display($cachable = null, $urlparams = null)
    {
        $this->g11nView->display($cachable, $urlparams);

        //-- this one is only for the submenu :|
        JRequest::setVar('view', 'utility');
    }//function

    public function updateLanguage()
    {
        $extension = JRequest::getCmd('extension');
        $scope = JRequest::getCmd('scope');
        $lang = JRequest::getCmd('langTag');

        try//
        {
            $languageFile = g11nExtensionHelper::findLanguageFile($lang, $extension, $scope);
            $templateFile = g11nStorage::getTemplatePath($extension, $scope);

            if(false == $languageFile)
            {
                $scopePath = g11nExtensionHelper::getScopePath($scope);
                $extensionPath = g11nExtensionHelper::getExtensionLanguagePath($extension);

                $path = $scopePath.'/'.$extensionPath.'/'.$lang;

                if( ! JFolder::exists($path))
                {
                    if( ! JFolder::create($path))
                    throw new Exception('Can not create the language folder');
                }

                $fileName = $lang.'.'.$extension.'.po';

                $input = '--input='.$templateFile;
                $output = '--output='.$path.'/'.$fileName;

                $noWrap = '--no-wrap';

                $locale = '--locale='.$lang;

                $cmd = "msginit $input $output $locale $noWrap 2>&1";

                echo '<h3>'.$cmd.'</h3>';

                ob_start();

                system($cmd);

                $msg = ob_get_clean();

                $msg = str_replace("\n", BR, $msg);

                if( ! JFile::exists($templateFile))
                throw new Exception('Can not copy create the language file');

                JFactory::getApplication()->enqueueMessage(jgettext('The language file has been created<br />').$msg);
            }
            else//
            {
                $msg = '';

                $msg .= jgettext('Updating language file...');

                $update = '--update';
                $backup = '--backup=numbered';
                $noFuzzy = '--no-fuzzy-matching';
                $verbose = '--verbose';
                $noWrap = '--no-wrap';

                $cmd = "msgmerge $update $noFuzzy $backup $verbose $noWrap $languageFile $templateFile  2>&1";

                echo '<h3>'.$cmd.'</h3>';

                ob_start();

                system($cmd);

                $msg .= ob_get_clean();

                JFactory::getApplication()->enqueueMessage($msg);
            }
        }
        catch(Exception $e)
        {
            JError::raiseWarning(0, $e->getMessage());
        }//try

        # JRequest::setVar('view', 'g11n');
        $this->g11nView->display();

        //-- this one is only for the submenu :|
        JRequest::setVar('view', 'utility');
        #   parent::display();
    }//function

    public function createTemplate()
    {
        $model = $this->getModel('g11nList', 'g11nListModel');

        $extensions = $model->getData();

        $extension = JRequest::getCmd('extension');
        $scope = JRequest::getCmd('scope');

        try
        {
            $found = false;

            foreach($extensions as $e)
            {
                if($e->extension == $extension)
                {
                    $found = true;

                    break;
                }
            }//foreach

            if( ! $found)
            {
                JError::raiseWarning(0, 'Invalid extension');

                return;
            }

            $headerData = '';
            $headerData .= ' --copyright-holder="NiK(C)"';
            $headerData .= ' --package-name="'.$extension.' - '.$scope.'"';
            $headerData .= ' --package-version="123.456"';
            $headerData .= ' --msgid-bugs-address="info@nik.it.de"';

            if(($scope != 'admin')
            && ($scope != 'site'))
            throw new Exception('Scope must be "admin" or "site"');

            $base = g11nExtensionHelper::getScopePath($scope);
            $templatePath = g11nStorage::getTemplatePath($extension, $scope);

            $comments = ' --add-comments=TRANSLATORS:';

            $keywords = ' -k --keyword=jgettext --keyword=jngettext:1,2';
            $forcePo = ' --force-po --no-wrap';

            /*
             * KEYS="-k --keyword=jgettext --keyword=jngettext:1,2"

             find "$WORK_PATH/." -type f -iname "*.php" | xgettext $KEYS -o $WORK_PATH/language/$FNAME.pot -f -
             */

            $extensionDir = g11nExtensionHelper::getExtensionPath($extension);

            if( ! JFolder::exists($base.DS.$extensionDir))
            throw new Exception('Invalid extension');

            $dirName = dirname($templatePath);

            if( ! JFolder::exists($dirName))
            {
                if( ! JFolder::create($dirName))
                throw new Exception(jgettext('Can not create the language template folder'));
            }

            $subType = '';

            if(strpos($extension, '.'))
            {
                $subType = substr($extension, strpos($extension, '.') + 1);
            }

            $buildOpts = '';

            $excludes = array(
            '/editarea_0_8_1_1/'
            , '/highcharts-2.0.5/'
            , '/php2js.js'
            );

            switch($subType)
            {
                case '':
                    $search = 'php';
                    break;

                case 'js':
                    $search = 'js';
                    $buildOpts .= ' -L python';
                    break;

                case 'config':
                    //                    $search = 'xml';
                    //                    $buildOpts .= ' -L Glade';
                    //                    $keywords = ' -k --keyword=description --keyword=label';

                    $excludes[] = '/templates/';
                    $excludes[] = '/scripts/';
                    break;

                default:
                    break;
            }//switch

            $files = JFolder::files($base.DS.$extensionDir, '.'.$search.'$', true, true);

            if( ! $files)
            throw new Exception(jgettext('No files found'));

            $cleanFiles = array();

            foreach($files as $file)
            {
                $found = false;

                foreach($excludes as $exclude)
                {
                    if(strpos($file, $exclude))
                    $found = true;
                }//foreach

                if( ! $found)
                $cleanFiles[] = $file;
            }//foreach

            if('config' == $subType)
            {
                defined('NL') || define('NL', "\n");
                $parser = g11n::getParser('code', 'xml');
                $potParser = g11n::getParser('language', 'pot');

                $options = new JObject;

                $outFile = new g11nFileInfo;

                foreach($cleanFiles as $fileName)
                {
                    $fileInfo = $parser->parse($fileName);

                    if( ! count($fileInfo->strings))
                    continue;

                    $relPath = str_replace(JPATH_ROOT.DS, '', $fileName);

                    foreach($fileInfo->strings as $key => $strings)
                    {
                        foreach($strings as $string)
                        {
                            if(array_key_exists($string, $outFile->strings))
                            {
                                if(strpos($outFile->strings[$string]->info, $relPath.':'.$key) !== false)
                                continue;

                                $outFile->strings[$string]->info .= '#: '.$relPath.':'.$key.NL;
                                continue;
                            }

                            $t = new g11nTransInfo;
                            $t->info .= '#: '.$relPath.':'.$key.NL;
                            $outFile->strings[$string] = $t;
                        }//foreach
                    }//foreach
                }//foreach

                $buffer = $potParser->generate($outFile, $options);

                if( ! JFile::write($templatePath, $buffer))
                throw new Exception('Unable to write the output file', $code);
            }
            else//
            {
                $fileList = implode("\n", $cleanFiles);

                $command = $keywords.$buildOpts.' -o '.$templatePath.$forcePo.$comments.$headerData;

                echo '<h3>'.$command.'</h3>';

                ob_start();

                system('echo "'.$fileList.'" | xgettext '.$command.' -f - 2>&1');

                $result = ob_get_clean();

                echo '<pre>'.$result.'</pre>';
            }

            if( ! JFile::exists($templatePath))
            throw new Exception('Could not create the template');

            //-- Manually strip the JROOT path - ...
            $contents = JFile::read($templatePath);
            $contents = str_replace(JPATH_ROOT.DS, '', $contents);

            JFile::write($templatePath, $contents);

            JFactory::getApplication()->enqueueMessage(jgettext('Your template has been created'));
        }
        catch(Exception $e)
        {
            JError::raiseWarning(0, $e->getMessage());
        }//try

        $this->g11nView->display();

        //-- this one is only for the submenu :|
        JRequest::setVar('view', 'utility');
//        parent::display();
    }//function
}//class
