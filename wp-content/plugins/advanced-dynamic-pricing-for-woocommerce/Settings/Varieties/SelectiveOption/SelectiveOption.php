<?php

namespace ADP\Settings\Varieties\SelectiveOption;

use ADP\Settings\Abstracts\OriginOption;
use ADP\Settings\Exceptions\OptionValueFilterFailed;

use ADP\Settings\Varieties\SelectiveOption\Interfaces\SelectiveOptionInterface;


class SelectiveOption extends OriginOption implements SelectiveOptionInterface
{
    /**
     * @var OptionSelection[]
     */
    protected $selections = array();

    /**
     * SelectiveOption constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct($id);
    }

    /**
     * @param mixed $value
     * @param string $title
     *
     * @return bool
     */
    public function addSelection($value, string $title)
    {
        $selection = new OptionSelection($value, $title);

        if ($this->isSelectionExists($selection)) {
            return false;
        }

        $this->selections[] = $selection;

        if (count($this->selections) === 1) {
            $this->default = $selection->getValue();
        }

        return true;
    }

    /**
     * @param OptionSelection $selection
     *
     * @return boolean
     */
    protected function isSelectionExists(OptionSelection $selection)
    {
        foreach ($this->selections as $thisSelection) {
            if ($thisSelection->getValue() === $selection->getValue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValueExists($value)
    {
        foreach ($this->selections as $thisSelection) {
            if ($thisSelection->getValue() === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws OptionValueFilterFailed
     */
    protected function filter($value)
    {
        if ( ! $this->hasSelections()) {
            throw new OptionValueFilterFailed();
        }

        if ( ! $this->isValueExists($value)) {
            throw new OptionValueFilterFailed();
        }

        return $value;
    }

    protected function hasSelections()
    {
        return count($this->selections) > 0;
    }
}
