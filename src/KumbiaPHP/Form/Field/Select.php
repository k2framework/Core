<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\AbstractChoice;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Select extends AbstractChoice
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('select');
    }

    /**
     * Reescritura del método para generar un select html.
     * 
     * @param string $separator
     * @return string 
     */
    public function render()
    {
        $html = '<select ' . $this->attrsToString() . ' >' . PHP_EOL;
        foreach ($this->getOptions() as $value => $label) {
            $html .= '<option value="' . htmlspecialchars($value, ENT_COMPAT) . '" ';
            if (in_array($value, (array) $this->getValue())) {
                $html .= ' selected="selected" ';
            }
            $html .= ' >' . htmlspecialchars($label, ENT_COMPAT) . '</option>' . PHP_EOL;
        }
        $html .= '</select>' . PHP_EOL;
        return $html;
    }

    protected function prepareAttrs()
    {
        if (isset($this->attrs['multiple'])) {
            $this->attrs['name'] = $this->formName . '[' . $this->getFieldName() . '][]';
        } else {
            $this->attrs['name'] = $this->formName . '[' . $this->getFieldName() . ']';            
        }
    }

}