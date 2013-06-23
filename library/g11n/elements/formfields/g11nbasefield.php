<?php
class g11nBaseFormField extends JFormField
{
    protected function getInput()
    {
//         return parent::getInput();
    }

    protected function getLabel() {
        // Initialise variables.
        $label = '';

        if ($this->hidden)
        return $label;

        $text = $this->element['label']
        ? (string) $this->element['label']
        : (string) $this->element['name'];

        if(JFactory::getLanguage()->hasKey($text))
        {
            $text = $this->translateLabel ? JText::_($text) : $text;
        }
        else
        {
            try
            {
                if(jimport('g11n.language'))
                {
                    $module = $this->form->getValue('module');
                    //		        $a = $module->get('module');

                    $extension = $module.'.config';

                    g11n::loadLanguage($extension, 'site');

                    $text = $this->translateLabel ? jgettext($text) : $text;
                }
            }
            catch (Exception $e)
            {
                //-- do nothing..
            }//catch
        }

        // Build the class for the label.
        $class = !empty($this->description) ? 'hasTip' : '';
        $class = $this->required == true ? $class.' required' : $class;

        // Add the opening label tag and main attributes attributes.
        $label .= '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="'.$class.'"';

        // If a description is specified, use it to build a tooltip.
        if( ! empty($this->description))
        {
            $label .= ' title="'.htmlspecialchars(trim($text, ':').'::' .
            ($this->translateDescription ? JText::_($this->description) : $this->description), ENT_COMPAT, 'UTF-8').'"';
        }

        // Add the label text and closing tag.
        $label .= '>'.$text;

        if($this->required)
        $label .= '<span class="star">&#160;*</span>';

        $label .= '</label>';

        return $label;
    }

}
