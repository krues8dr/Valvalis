<?php
/*
	class FormHandler

	Created by:
	Bill Hunt (bill@krues8dr.com)

	Description:
	Does all of the form-handling work.

	ChangeLog:
	01.09.06 - bill@krues8dr.com
			 - Created from showForm() function and
			   ControlPanel class.
	01.11.06 - bill@krues8dr.com
			 - Merged error_object into class.
*/

require_once(FRAMEWORK_DIR.'template.inc.php');

class FormHandler {

	var $object;
	var $html_builder;
	var $error_object;
	var $request;

	var $form_method = 'post'; // post OR get
	var $form_action; // URL, defaults to PHP_SELF
	var $form_name = 'entry_form';

	var $form_footer;
	var $form_header;

	var $templates = array();
	var $template_class = 'Template';

	var $errors = array();
	var $error_field_list = array();
	var $error_messages = array(
		'header' => 'Please correct the following errors:',
		'required' => 'Please fill out all of the required fields.',
		'required_filefield' => 'Please upload a file for all of the required fields'
	);

	var $_filefield_types = array(
		'filefield',
		'filefield_plus',
		'image_field'
	);

	var $build_form_tags = true;

	var $submit_value = 'Submit';

	var $static = false;

	var $xhtml = false;

/*
	function FormHandler()

	Created by:
	Bill Hunt (bill@krues8dr.com)

	IMPORTANT:


	ChangeLog:
	01.09.05 - bill@krues8dr.com
			 - Created function.

*/


	function FormHandler($args) {
		$this->object = &$args['object'];
		$this->html_builder = &$args['html_builder'];
		$this->error_object = &$args['error_object'];


		if($args['template_class']) {
			$this->template_class = $args['template_class'];
		}
		if($args['templates']) {
			$this->templates = $args['templates'];
		}

		if($args['static']) {
			$this->static = $args['static'];
		}


		if(strlen($args['form_method'])) {
			$this->form_method = $args['form_method'];
		}

		if(strlen($args['form_action'])) {
			$this->form_action = $args['form_action'];
		}
		else {
			$this->form_action = $_SERVER['PHP_SELF'];
		}

		if(strlen($args['form_name'])) {
			$this->form_name = $args['form_name'];
		}
	}

	function execute(&$request, $default_values = null) {
		$this->request =& $request;

		if($this->formSubmitted($request)) {
			$request->set($this->form_method, $this->preformatData($request));

			$errors = $this->checkErrors($request);

			if($errors) {
				$errors = $this->showErrors($errors);

				$out = $this->showForm($request->fetch($this->form_method), $errors);
			}
			else {
				$out = $this->finish($request);
			}

		}
		else {
			$out = $this->showForm($default_values);
		}

		return $out;
	}

	function formSubmitted(&$request) {
		if($request->isSubmitted($this->form_method)) {
			$return_val = true;
		}
		else {
			$return_val = false;
		}
		return $return_val;
	}

	function preformatData(&$request) {
		$data =& $request->fetch($this->form_method);

		// Make a copy.
		$return_data = $data;
		foreach($this->object->fields as $field=>$info) {
			$args = array(
				'data' => $return_data,
				'field' => $field
			);

			$extra_args = $this->object->getExtraArgs(&$html_builder, $field, $return_data[$field]);

			$args = array_merge($extra_args, $args);

			$return_data = $this->html_builder->preformatData($this->object->fields[$field]['input_type'], $args);
		}

		foreach($return_data as $key=>$value) {
			$data[$key] = $value;
		}
		foreach(array_diff($data, $return_data) as $key=>$value) {
			unset($data[$key]);
		}

		return $data;
	}

	function showForm($data, $errors = null, $prompt = null) {
		$prompt = $this->promptDecorator($prompt);
		if($this->form_footer) {
			$args['form_footer'] = $this->form_footer;
		}
		if($this->form_args) {
			$args['form_args'] = $this->form_args;
		}
		$args['data'] = $data;

		$form = $this->buildForm($args);

		if($this->templates['form']) {
			$replacements = array(
				'form' => $form,
				'errors' => $errors,
				'prompt' => $prompt
			);

			$out = $this->showTemplate(
				array(
					'template_file' => $this->templates['form'],
					'replacements' => $replacements
				)

			);
		}
		else {
			$out = $prompt.$errors.$form;
		}

		return $out;
	}


	function showErrors($errors) {
		if(is_array($errors) && count($errors) > 0) {
			$out .= $this->error_messages['header'];
			foreach($errors as $error) {
				$error_list .= $this->html_builder->show('li', $error);
			}
			$out .= $this->html_builder->show('ul',
				array(
					'content' => $error_list,
					'class' => 'error_list'
				)
			);

			return $out;
		}
	}

	function addError($field, $message) {
		### TO DO: Handle arrays
		if(!in_array($message, $this->errors)) {
			$this->errors[] = $message;
		}

		if(!in_array($field, $this->error_field_list)) {
			$this->error_field_list[] = $field;
		}
	}

	function hasError($field) {
		$return_value = false;
		if(is_array($this->error_field_list) && in_array($field, $this->error_field_list)) {
			$return_value = true;
		}

		return $return_value;
	}

	function checkErrors(&$request) {
		foreach($this->object->fields as $field=>$info) {
			if($this->object->fields[$field]['required']) {
				if(!strlen($request->fetch($this->form_method, $field))) {
					if(in_array($this->object->fields[$field]['input_type'], $this->_filefield_types)) {
						if(!$request->fetchFiles($field)) {
							$this->addError($field, $this->error_messages['required_filefield']);
						}
					}
					else {
						$this->addError($field, $this->error_messages['required']);
					}
				}
			}
		}

		return $this->errors;
	}

	function finish($request) {
		$out = 'Form successfully submitted.';
		return $out;
	}


	function showTemplate($args = array()) {
		if(class_exists($this->template_class)) {
			$template = new $this->template_class();

			$out = $template->show(
				array(
					'template_file' => $args['template_file'],
					'data' => $args['replacements']
				)
			);
		}
		else {
			trigger_error('Template class "'.$this->template_class.'" used by '.get_class($this).'does not exist.', E_USER_WARNING);
		}

		return $out;
	}


/*
	function buildForm()

	Created by:
	Bill Hunt (bill@krues8dr.com)

	Description:
	Generic form-showing function. Uses the object's
	fields list in association with the error_object
	to display the form, with required and problem
	fields marked.

	IMPORTANT:


	Change Log:
	06.29.04 - bill@krues8dr.com
			- Added support for YesNo fields
			- Added support for Radio Groups
			- Added support for Custom fields
			- Added condition for row data to be
			  set directly, rather than through
			  the $html_builder->fn() construct.
			- Replaced if/else groups with switch/case
	07.12.04 - bill@krues8dr.com
			- Added support for Int fields.
			- Added support for Hidden fields.
			- Added support for Label fields.
	07.20.04 - bill@krues8dr.com
			- Added support for Textarea fields.
			- Added support for explicit Text fields.
	08.02.04 - bill@krues8dr.com
			- Added support for Date fields.
			- Added support for Time fields.
			- Added support for DateTime fields.
	11.11.04 - bill@krues8dr.com
			- Removed global declarations.
			- Abstracted form.
	11.17.04 - bill@krues8dr.com
			- Added support for File fields & uploads
	08.24.05 - bill@krues8dr.com
			- Abstracted form creation
			- Added footer argument for submit buttons.
			- Added form_args argument.
	01.09.06 - bill@krues8dr.com
			- Added max_file_size form argument.
			- Modified types to match new system.
			- Renamed function from showForm() to buildForm()
			  for consistency.
	01.11.06 - bill@krues8dr.com
			 - Replaced previous DAO field system with new one.
			 - Merged error_object functionality into function.
	01.16.06 - bill@krues8dr.com
			 - separated buildRow function
	01.30.06 - bill@krues8dr.com
			 - Added form_header variable.
	04.27.06 - bill@krues8dr.com
			 - Added wysiwyg_textarea type.


*/

	function buildForm($args = array()) {
		$data = $args['data'];
		$form_footer = $args['form_footer'];
		$form_args = $args['form_args'];

		$object = &$this->object;
		$html_builder = &$this->html_builder;

		if($this->form_header) {
			$trs .= $this->form_header;
		}

		foreach($object->fields as $key=>$field_info) {
			$value = $data[$key];
			unset($row);
			unset($fn);
			unset($element);
			unset($label);
			unset($hide);

			$label = $this->object->fields[$key]['label'];
			if($this->object->fields[$key]['required']) {
				$label = $this->requiredDecorator($label);
			}
			if(in_array($key, $this->error_field_list)) {
				$label = $this->errorDecorator($label);
			}

			$fn = $object->fields[$key]['input_type'];
			$args = array(
				  'name' => $key,
				  'value' => $value
			);

			//build header rows, if necesary.
			if($object->form_dividers[$key]) {
			  unset($header_content);

			  if($key != array_shift(array_keys($object->fields))) { $header_content .= ''; }
			  $header_content .= $this->html_builder->show('strong',
			  	$object->form_dividers[$key]
			  );

			  $trs .= $this->buildFormRow($header_content);
			}

			$extra_args = $object->getExtraArgs(&$html_builder, $key, $value);

			if(is_array($extra_args)) {
				$args = array_merge($extra_args, $args);
			}

			if(!$this->static) {
				switch($object->fields[$key]['input_type']) {
					case 'select_list' :
					  $args['values'] = $object->getSelectListContent($key);
					  break;
					case 'checkbox' :
						if($args['value']) {
							$args['checked'] = 1;
						}
						else {
							$args['checked'] = 0;
						}
						$args['value'] = 1;
						break;
					case 'checkbox_group' :
					  $args['value'] = $object->getCheckboxGroupDefault($key);
					  $args['values'] = $object->getCheckboxGroupContent($key, $args, $args['label_field']);
					  break;
					case 'radio_group' :
					  $args['values'] = $object->getRadioGroupContent($key);
					  break;
					case 'hidden' :
					  $hide = true;
					  $fn = '';
					  $footer .= $this->html_builder->show('hidden',
								   array(
									 'name'  => $key,
									 'value' => $value
									 )
								   );
					  break;
					case 'custom' :
					  $fn = '';
					  $row = $object->getCustomContent($html_builder, $key, &$this->request);
					  break;
					case 'int' :
					  $fn = 'textfield';
					  $args['size'] = 6;
					  break;
					case 'textarea' :
					  $args['cols'] = '40';
					  $args['rows'] = '10';
					  break;
					case 'wysiwyg_textarea' :
					  $args['cols'] = '60';
					  $args['rows'] = '15';
						break;
					case 'filefield' :
					case 'filefield_plus' :
					case 'image_field' :
						// Something special, but not right now.
						$uses_files = true;
						break;
					default :
						break;
				}
			}
			else {
				switch($object->fields[$key]['input_type']) {
					case 'select_list' :
					 	$fn = '';

					 	// Ignore blank rows.
					 	if($value) {
							$values = $object->getSelectListContent($key);
							$row = $values[$value];
						}
						$row .= '&nbsp;';

						break;

					case 'radio_group' :
					 	$fn = '';

					 	// Ignore blank rows.
					 	if($value) {
							$values = $object->getRadioGroupContent($key);
							$row = $values[$value];
						}
						$row .= '&nbsp;';

						break;

					case 'custom' :
					  $fn = '';
					  $row = $object->getCustomContent($html_builder, $key, $value);
					  $row .= '&nbsp;';
					  break;


					case 'yesno' :
						if(strlen($value)) {
							$noyes = array('No', 'Yes');
							$row = $noyes[$value];
						}
						$row .= '&nbsp;';
						break;

					case 'hidden' :
						$row = '';
						$fn = '';
						$hide = true;
						break;

					default:
						break;
				}
			}

			if(!$element && $fn) {
				$extra_args = $object->getExtraArgs(&$html_builder, $key, $value);
				if(is_array($extra_args)) {
					$args = array_merge($args, $extra_args);
				}

				$element = $this->html_builder->show($fn, $args);
				$extra_element = $this->html_builder->getExtraContent($fn, $args);

				$row = $element.$extra_element;
			}

			if(!$hide) {
			  if(!strlen($row)) { $row = '&nbsp;'; }
			  $trs .= $this->buildFormRow($label, $row);
			}
		}

		$form_footer = $this->form_footer;

		if(!$this->static) {
			if(!strlen($form_footer)) {
				$form_footer = $this->buildFormFooter($data);
			}
		}

		$trs .= $form_footer;

		$out .= $this->buildFormTable(
				   array(
					 'content'           => $trs,
					 'class'             => 'form_table'
					 )
				   );

		if($uses_files && !$this->static) {
			if($form_args['max_file_size']) {
				$size = $form_args['max_file_size'];
				unset($form_args['max_file_size']);
			}
			else {
				$size = '50000000';
			}

			// Max File Size has to be before any filefield elements.
			$out = $this->html_builder->show('hidden',
					  array(
						'name'  => 'MAX_FILE_SIZE',
						'value' => $size
						)
					  ) . $out;
			$this->form_enctype = 'multipart/form-data';
		}

		if($footer) { $out .= $footer; }


		if(!$this->static && $this->build_form_tags) {
			$out = $this->buildFormTags($out);
		}


		return $out;

	}

	function buildFormFooter($data) {
		$form_footer = $this->html_builder->show('submit',
									   array(
										 'name'   =>'submit',
										 'value'  => $this->submit_value
										 )
									   );

		$form_footer = $this->buildFormRow($form_footer);

		return $form_footer;
	}

	function buildFormTags($out) {
		$form_args['action'] = $this->form_action;
		$form_args['method'] = $this->form_method;
		$form_args['name'] = $this->form_name;
		$form_args['enctype'] = $this->form_enctype;

		$form_args['content'] = $out;

		$out = $this->html_builder->show('form', $form_args);

		return $out;
	}

	function buildFormRow($label, $row = null) {
		if(strlen($row)) {
			$out = $this->html_builder->show('tr',
							array(
								'content' => $this->html_builder->show('td',
									  array(
										'content' =>   $label,
										'class'   => 'label_row',
										'valign'  => 'top'
										)
									  ) .
								$this->html_builder->show('td',
									  array(
										'class' => 'input_cell',
										'content' => $row
										)
									  )
							)
						  )."\n";

		}
		else {
			$out = $this->html_builder->show('tr',
							array(
								'content' => $this->html_builder->show('td',
									  array(
										'colspan' => 2,
										'content' =>   $label,
										'class'   => 'header_label_row',
										'valign'  => 'top'
										)
									  )
							)
						  );
		}
		return $out;
	}
	function buildFormTable($args) {
		$out = $this->html_builder->show('table', $args);
		return $out;
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
		return $item;
	}

	function menuDecorator($items) {
		if($items) {
			$items = $this->html_builder->show('div',
				array(
					'class' => 'menu',
					'content' => $items

				)
			);
		}
		return $items;
	}

	function redirect($url = null) {
		if(!$url) {
			$url = $this->form_action;
		}

		session_write_close();
		header('Location: '.$url);
		exit;
	}

}

?>