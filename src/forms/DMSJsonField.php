<?php

/**
 * Combines form inputs into a key-value pair
 */
class DMSJsonField extends CompositeField
{
    public function __construct($name, $children = null)
    {
        $this->setName($name);

        if ($children instanceof FieldList || is_array($children)) {
            foreach ($children as $child) {
                $this->setChildName($child);
            }
        } else {
            $children = is_array(func_get_args()) ? func_get_args() : array();
            if (!empty($children)) {
                array_shift($children);
            }
            foreach ($children as $child) {
                $this->setChildName($child);
            }
        }
        parent::__construct($children);
    }

    /**
     * Sets the name of the child object
     *
     * @param FormField $child
     */
    private function setChildName($child)
    {
        $child->setName("{$this->getName()}[{$child->getName()}]");
    }

    public function hasData()
    {
        return true;
    }

    /**
     * Override parent's behaviour as it's no longer required
     *
     * @param array $list
     * @param bool  $saveableOnly
     */
    public function collateDataFields(&$list, $saveableOnly = false)
    {
    }

    /**
     * Recursively removed empty key-value pairs from $haystack
     *
     * @param $haystack
     *
     * @return mixed
     */
    public function arrayFilterEmptyRecursive($haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->arrayFilterEmptyRecursive($haystack[$key]);
            }
            if (empty($haystack[$key])) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }

    /**
     * Overrides parent behaviour to remove empty elements
     *
     * @return mixed|null|string
     */
    public function dataValue()
    {
        $result = null;
        if (is_array($this->value)) {
            $this->value = $this->arrayFilterEmptyRecursive($this->value);
            $result = (!empty($this->value)) ? Convert::array2json($this->value) : $result;
        } else {
            $result = parent::dataValue();
        }

        return $result;
    }

    /**
     * Sets the value
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        if (is_string($value) && !empty($value)) {
            $value = Convert::json2array($value);
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $pattern = "/^{$this->getName()}\[(.*)\]$/";
        foreach ($this->children as $c) {
            $title = $c->getName();
            preg_match($pattern, $title, $matches);
            if (!empty($matches[1]) && isset($value[$matches[1]])) {
                $c->setValue($value[$matches[1]]);
            }
        }

        return $this;
    }
}
