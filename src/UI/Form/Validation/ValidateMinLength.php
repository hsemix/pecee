<?php
namespace Pecee\UI\Form\Validation;

class ValidateMinLength extends ValidateInput
{
    protected $minimumLength;

    public function __construct($minimumLength = 5)
    {
        $this->minimumLength = $minimumLength;
    }

    public function validates()
    {
        return (strlen($this->input->getValue()) > $this->minimumLength);
    }

    public function getError()
    {
        return lang('%s has to minimum %s characters long', $this->input->getName(), $this->minimumLength);
    }

}