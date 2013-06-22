<?php
/**
 * @copyright  2010-2013 Nikolsi Plath
 * @license    GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

/**
 * Parse XML files.
 *
 * @package    g11n
 */
class g11nParserCodeXML
{
    /**
     * Attributes to search for in XML files.
     *
     * @var array
     */
    private $xmlSearchAttribs = array(
    'label'
    , 'title'
    , 'description'
    , 'group'
    );

    /**
     * FileInfo object.
     *
     * @var g11nFileInfo
     */
    private $fileInfo = null;

    /**
     * Set the language file format.
     *
     * @param string $langFormatIn The language format.
     *
     * @return void
     */
    public function setLangFormat($langFormatIn)
    {
    }

    /**
     * Parse a file.
     *
     * @param string $fileName File to parse
     *
     * @return object g11nFileInfo
     */
    public function parse($fileName)
    {
        $fileName = JPath::clean($fileName);

        $this->fileInfo = new g11nFileInfo;

        $this->fileInfo->fileName = $fileName;

        //--Search XML files
        if( ! $xml = JFactory::getXML($fileName))
        return $this->fileInfo;

        $this->parseXML($xml);

        return $this->fileInfo;
    }

    /**
     * Parse a XML string.
     *
     * @param object $xml A JXMLElement object
     *
     * @return void
     */
    private function parseXML($xml)
    {
        foreach($xml as $k => $v)
        {
            if( ! $v instanceof JXMLElement)
            continue;

            if($k == 'description')
            {
                if(trim((string)$v))
                $this->fileInfo->strings['description'][] = trim((string)$v);
            }

            if($k == 'param'
            && $v->attributes()->name == '@spacer'
            && $v->attributes()->default)
            {
                $this->fileInfo->strings['param-default'][] = (string)$v->attributes()->default;
            }

            foreach($this->xmlSearchAttribs as $attrib)
            {
                $x = trim((string)$v->attributes()->$attrib);

                if($x)
                $this->fileInfo->strings[$attrib][] = $x;
            }

            foreach($v->option as $option)
            {
                if((string)$option)
                $this->fileInfo->strings['opt'][] = trim((string)$option);
            }

            //-- Recurse...
            $this->parseXML($v);
        }
    }
}
