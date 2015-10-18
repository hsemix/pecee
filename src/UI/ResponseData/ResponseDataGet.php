<?php
namespace Pecee\UI\ResponseData;
use Pecee\Str;

class ResponseDataGet extends ResponseData {
	public function __construct() {
		parent::__construct();
		if(isset($_GET)) {
			foreach($_GET as $i=>$get) {
				if(is_array($get)) {
					foreach($get as $k=>$g) {
						$get[$k] = Str::HtmlEntitiesDecode($g);
					}
					$this->data[strtolower($i)] = $get;
				} else {
					$this->data[strtolower($i)] = Str::HtmlEntitiesDecode($get);
				}
			}
		}
	}

	public static function GetFormName() {
		foreach($_GET as $key=>$p) {
			if(strpos($key, '_') > 0) {
				$form = explode('_', $key);
				return $form[0];
			}
		}
		return null;
	}
}