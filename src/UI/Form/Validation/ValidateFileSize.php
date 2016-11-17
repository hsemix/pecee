<?php
namespace Pecee\UI\Form\Validation;

use Pecee\Http\Input\InputFile;

class ValidateFileSize extends ValidateFile {

    protected $error;
	protected $sizeMinKb;
    protected $sizeMaxKb;

	public function __construct($maxKb, $minKb = null) {
		$this->sizeMinKb = $minKb;
		$this->sizeMaxKb = $maxKb;
	}

	public function validates() {
		if(!($this->input instanceof InputFile)) {
			return true;
		}

		$validates = true;

		if($this->sizeMinKb !== null && (($this->sizeMinKb * 1024) <= $this->input->getSize())) {
            $this->error = lang('%s cannot be less than %sKB', $this->input->getName(), $this->sizeMinKb);
            $validates = false;
        }

        if(($this->sizeMaxKb * 1024) >= $this->input->getSize()) {
            $this->error = lang('%s cannot be greater than %sKB', $this->input->getName(), $this->sizeMaxKb);
            $validates = false;
        }

		return $validates;
	}

	public function getError() {
		return $this->error;
	}

}