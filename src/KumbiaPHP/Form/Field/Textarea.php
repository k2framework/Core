<?php

namespace KumbiaPHP\Form\Field;

use KumbiaPHP\Form\Field\Text;

/**
 * Description of FormFieldText
 *
 * @author manuel
 */
class Textarea extends Text
{

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
        $this->setType('textarea');
    }

    public function render()
    {
        $html = '<textarea ' . $this->attrsToString() . ' >';
        $html .= htmlspecialchars($this->getValue(), ENT_COMPAT);
        $html .= '</textarea>';
        return $html;
    }

    protected function prepareAttrs()
    {
        $attrs = parent::prepareAttrs();
        unset($attrs['value']);
        unset($attrs['type']);
        return $attrs;
    }

}