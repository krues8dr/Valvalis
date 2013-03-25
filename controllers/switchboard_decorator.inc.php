<?php
/*
	class SwitchboardDecorator
	
	Created by:
	Bill Hunt (bill@krues8dr.com)
	
	Description: 
	Extends Switchboard for global decorations.
	
	ChangeLog: 
	05.27.06 - bill@krues8dr.com
			 - Created object.
*/


require_once(FRAMEWORK_DIR.'switchboard.inc.php');

class SwitchboardDecorator extends Switchboard {

	function SwitchboardDecorator($args) {
		parent::Switchboard($args);
	}
	
	function requiredDecorator($label = null) {
		if(strlen($label)) {
			$label = $this->html_builder->show('strong', $label);
		}
		return $label;
	}
	
	function errorDecorator($error = null) {
		$error .= $this->html_builder->show('span', 
			array(
				'style' => 'color: #f00',
				'content' => '*'
			)
		);
		return $error;
	}
	
	function promptDecorator($prompt = null) {
		if(strlen($prompt)) {
			$prompt .= $this->html_builder->show('br');
		}
		return $prompt;
	}	
	
	function menuItemDecorator($item) {
		if(strlen($item)) {
			$item = $this->html_builder->show('li', $item);
		}
		return $item;
	}
	
	function menuDecorator($items) {
		$items = $this->html_builder->show('ul', 
			array(
				'content' => $items,
				'class' => 'switchboard_menu'
			)
		);
		return $items;
	}
	

}