<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\form\Element;


use Pyshnov\form\Element;

class Select extends Element
{

    protected $tag = 'select';

    /**
     * @var null|array
     */
    protected $options;

    /**
     * @var
     */
    protected $value;

    /**
     * @return array|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }


    public function render()
    {

        $html = '';

        $currentValue = $this->getValue();


        if (null !== $options = $this->getOptions()) {
            foreach ($options as $value => $text) {
                $html .= '
                <option value="' . $value . '"'
                    . ($currentValue == $value ? ' selected' : '') . '>'
                    . $text
                    . '</option>';
            }
        }

        if ($html) {
            $this->setContent($html);
        }

        return parent::render();
    }


}