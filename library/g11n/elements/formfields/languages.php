<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

require_once 'g11nbasefield.php';

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldLanguages extends g11nBaseFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'Languages';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribue to enable multiselect.
     *
     * @return  string  The field input markup.
     * @since   11.1
     */
    protected function getInput()
    {
        $html = array();

        $options = $this->element->children();

        if( ! $options)
        return;

        foreach(JFactory::getLanguage()->getKnownLanguages() as $lang)
        {
            $html[] = '<label style="background-color: #eee; width: 100%">'.$lang['tag'].'</label>';

            foreach ($options as $option)
            {
                $fieldName = (string)$option->attributes()->value;
                $label = jgettext((string)$option);

                $v =($this->value && isset($this->value[$lang['tag']][$fieldName]))
                ? $this->value[$lang['tag']][$fieldName] : '';

                $id = $this->name.$lang['tag'].$fieldName;

                $html[] = '<label for="'.$id.'">'.$label.'</label>';
                $html[] = '<input id="'.$id.'" type="text" name="'.$this->name.'['.$lang['tag'].']['.$fieldName.']"'// id="'.$this->id.'"'
                .' value="'.htmlspecialchars($v, ENT_COMPAT, 'UTF-8').'">';
            }
        }

        return implode("\n", $html);
    }
}
