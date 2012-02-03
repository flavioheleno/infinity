<?php
/**
* Form creation helper
*
* @version 0.1
* @author Flávio Heleno <flaviohbatista@gmail.com>
* @link http://code.google.com/p/infinity-framework
* @copyright Copyright (c) 2010/2011, Flávio Heleno
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

	class FORM {
		//holds the instance of log class
		private $log = null;
		//holds the basename for the calling class
		private $name = '';
		//holds the form handler
		private $handler = array();
		//validation control
		public $validation = true;
		//autoclean control
		public $autoclean = true;
		//ajaxsubmit control
		public $ajaxsubmit = true;

		public function __construct($name) {
			$this->log = LOG::singleton();
			$this->name = strtolower($name);
		}

		//returns the css files needed by form
		public static function css() {
			$path = PATH::singleton();
			return array(
				$path->relative('css', 'core').'form.css',
				$path->relative('css', 'core').'msg.css'
			);
		}

		//returns the js files needed by validation script
		public static function js() {
			$path = PATH::singleton();
			return array(
				$path->relative('js', 'core').'jquery.js',
				$path->relative('js', 'core').'jquery.form.js',
				$path->relative('js', 'core').'jquery.maskedinput.js',
				$path->relative('js', 'core').'jquery.validate.js',
				$path->relative('js', 'core').'jquery.validate.additional-methods.js',
				$path->relative('js', 'core').'jquery.validate.messages_ptbr.js',
				$path->relative('js', 'core').'jquery.infinity.js'
			);
		}

		//creates a new form
		public function create($title, $id, $action, $method = 'post', $enctype = 'application/x-www-form-urlencoded') {
			$this->handler = array(
				'header' => array(
					'title' => htmlentities($title, ENT_QUOTES | ENT_IGNORE, 'UTF-8'),
					'id' => 'form_'.strtolower($id),
					'action' => htmlentities($action, ENT_QUOTES | ENT_IGNORE, 'UTF-8'),
					'method' => $method,
					'enctype' => $enctype
				),
				'input' => array()
			);
		}

		//cleans form structure
		public function clean() {
			$this->handler = array();
		}

		//loads a form structure from an xml file
		public function load($id, $fullid = false) {
			$path = PATH::singleton();
			if ($fullid)
				$file = $path->absolute('cfg', 'form').$id.'.xml';
			else
				$file = $path->absolute('cfg', 'form').$this->name.'_'.$id.'.xml';
			if ((file_exists($file)) && (is_file($file))) {
				$src = file_get_contents($file);
				$xml = new SimpleXMLElement($src);
				if ($xml === false) {
					$this->log->add('Invalid XML file ('.$file.')');
					return false;
				}
				$form['title'] = '';
				if (isset($xml['title']))
					$form['title'] = (string)$xml['title'];
				$form['id'] = '';
				if (isset($xml['id']))
					$form['id'] = (string)$xml['id'];
				$form['action'] = '';
				if (isset($xml['action']))
					$form['action'] = (string)$xml['action'];
				$form['method'] = 'post';
				if (isset($xml['method']))
					$form['method'] = (string)$xml['method'];
				$form['enctype'] = 'application/x-www-form-urlencoded';
				if (isset($xml['enctype']))
					$form['enctype'] = (string)$xml['enctype'];
				if (isset($xml['validation']))
					$this->validation = (((string)$xml['validation']) == 'false' ? false : true);
				if (isset($xml['autoclean']))
					$this->autoclean = (((string)$xml['autoclean']) == 'false' ? false : true);
				if (isset($xml['ajaxsubmit']))
					$this->ajaxsubmit = (((string)$xml['ajaxsubmit']) == 'false' ? false : true);
				$this->create($form['title'], $form['id'], $form['action'], $form['method'], $form['enctype']);
				foreach ($xml->fields->field as $item) {
					if ((isset($item['type'])) && (isset($item['id']))) {
						$field['label'] = (string)$item['id'];
						if (isset($item['label']))
							$field['label'] = (string)$item['label'];
						$field['value'] = '';
						if (isset($item['value']))
							$field['value'] = (string)$item['value'];
						if (isset($item->option)) {
							foreach ($item->option as $option)
								if ((isset($option['caption'])) && (isset($option['value'])))
									$field['value'][(string)$option['value']] = (string)$option['caption'];
						}
						$field['extra'] = array();
						foreach ($item->attributes() as $key => $value)
							if (!in_array((string)$key, array('type', 'id', 'label', 'value')))
								$field['extra'][(string)$key] = (string)$value;
						$field['rules'] = array();
						$field['alert'] = array();
						if (isset($item->rule)) {
							foreach ($item->rule as $rule) {
								if (isset($rule['id'])) {
									if (isset($rule['value']))
										$field['rules'][(string)$rule['id']] = (string)$rule['value'];
									else
										$field['rules'][] = (string)$rule['id'];
								}
								if (isset($rule['alert']))
									$field['alert'][(string)$rule['id']] = (string)$rule['alert'];
							}
						}
						switch ($item['type']) {
							case 'submit':
								$this->command($item['id'], $field['label'], $field['extra']);
								break;
							case 'reset':
								$this->cancel($item['id'], $field['label'], $field['extra']);
								break;
							case 'hidden':
								$this->hidden($item['id'], $field['value']);
								break;
							default:
								$this->input($item['type'], $field['label'], $item['id'], $field['value'], $field['extra'], $field['rules'], $field['alert']);
						}
						unset($field);
					}
				}
				if (isset($xml->submit)) {
					if (isset($xml->submit->before))
						$this->before_submit((string)$xml->submit->before);
					if (isset($xml->submit->on))
						$this->on_submit((string)$xml->submit->on);
					if (isset($xml->submit->after))
						$this->after_submit((string)$xml->submit->after);
				}
				return true;
			}
			$this->log->add('File not found: '.$file);
			return false;
		}

		//adds an input field to the form
		public function input($type, $label, $name, $value = '', array $properties = array(), array $rules = array(), array $messages = array()) {
			$this->handler['input'][] = array(
				'type' => strtolower($type),
				'label' => htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'),
				'name' => strtolower($name),
				'value' => (is_array($value) ? $value : htmlentities($value, ENT_QUOTES | ENT_IGNORE, 'UTF-8')),
				'properties' => $properties,
				'rules' => $rules,
				'messages' => $messages
			);
		}

		//sets an input field value
		public function set_input($type, $name, $value) {
			if (isset($this->handler['input']))
				foreach ($this->handler['input'] as &$item)
					if (($item['type'] == $type) && ($item['name'] == $name)) {
						$item['value'] = $value;
						break;
					}
		}

		public function update_input($type, $name, array $properties = array()) {
			if (isset($this->handler['input']))
				foreach ($this->handler['input'] as &$item)
					if (($item['type'] == $type) && ($item['name'] == $name)) {
						foreach ($properties as $propertie => $value)
							$item['properties'][$propertie] = $value;
						break;
					}
		}

		//adds a hidden input field to the form
		public function hidden($name, $value = '') {
			$this->handler['hidden'][] = array(
				'name' => strtolower($name),
				'value' => htmlentities($value, ENT_QUOTES | ENT_IGNORE, 'UTF-8')
			);
		}

		//sets a hidden field value
		public function set_hidden($name, $value) {
			if (isset($this->handler['hidden']))
				foreach ($this->handler['hidden'] as &$item)
					if ($item['name'] == $name) {
						$item['value'] = $value;
						break;
					}
		}

		//adds a command button to the form
		public function command($name, $value, array $properties = array()) {
			$this->handler['command'][] = array(
				'name' => strtolower($name),
				'value' => htmlentities($value, ENT_QUOTES | ENT_IGNORE, 'UTF-8'),
				'properties' => $properties
			);
		}

		//adds a cancel button to the form
		public function cancel($name, $value, array $properties = array()) {
			$this->handler['cancel'][] = array(
				'name' => strtolower($name),
				'value' => htmlentities($value, ENT_QUOTES | ENT_IGNORE, 'UTF-8'),
				'properties' => $properties
			);
		}

		//adds a text item to the form
		public function text($text) {
			$this->handler['text'][] = $text;
		}

		//javascript that will be executed before submitting the form
		public function before_submit($js) {
			$this->handler['submit']['pre'][] = $js;
		}

		//javascript that will be executed on form submit
		public function on_submit($js) {
			$this->handler['submit']['on'][] = $js;
		}

		//javascript that will be executed after submitting the form
		public function after_submit($js) {
			$this->handler['submit']['pos'][] = $js;
		}

		//adds javascript functions to validation code
		public function add_script($js) {
			$this->handler['script'][] = $js;
		}

		//renders the form
		public function render(array $message = array()) {
			if (!isset($this->handler['header'])) {
				$this->log->add('Trying to render an empty form');
				return false;
			}
			$bfr = '<div id="form">'."\n";
			$bfr .= '	<fieldset>'."\n";
			if ($this->handler['header']['title'] != '')
				$bfr .= '		<legend>'.$this->handler['header']['title'].'</legend>'."\n";
			$bfr .= '		<form';
			foreach ($this->handler['header'] as $key => $value)
				$bfr .= ' '.$key.'="'.$value.'"';
			$bfr .= '>'."\n";
			foreach ($this->handler['input'] as $item)
				switch ($item['type']) {
					case 'text':
					case 'password':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<input type="'.$item['type'].'" id="'.$item['name'].'" name="'.$item['name'].'"';
						if ((isset($item['value'])) && ($item['value'] != ''))
							$bfr .= ' value="'.$item['value'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= ' />'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						if ($this->validation) {
							$bfr .= '			<div id="error_'.$item['name'].'" class="error_container">'."\n";
							$bfr .='			</div>'."\n";
						}
						break;
					case 'textarea':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<textarea id="'.$item['name'].'" name="'.$item['name'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= '>';
						if ((isset($item['value'])) && ($item['value'] != ''))
							$bfr .= $item['value'];
						$bfr .= '</textarea>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						if ($this->validation) {
							$bfr .= '			<div id="error_'.$item['name'].'" class="error_container">'."\n";
							$bfr .='			</div>'."\n";
						}
						break;
					case 'checkbox':
					case 'radio':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<input type="'.$item['type'].'" id="'.$item['name'].'" name="'.$item['name'].'"';
						if ((isset($item['value'])) && ($item['value'] != ''))
							$bfr .= ' value="'.$item['value'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= ' />'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['name'].'">'.$item['label'].'</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						if ($this->validation) {
							$bfr .= '			<div id="error_'.$item['name'].'" class="error_container">'."\n";
							$bfr .='			</div>'."\n";
						}
						break;
					case 'select':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<select id="'.$item['name'].'" name="'.$item['name'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= '>'."\n";
						foreach ($item['value'] as $key => $value)
							if ((is_string($key)) && ($key == 'selected'))
								$bfr .= '						<option value="'.key($value).'" selected="selected">'.current($value).'</option>'."\n";
							else
								$bfr .= '						<option value="'.$key.'">'.$value.'</option>'."\n";
						$bfr .= '					</select>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						if ($this->validation) {
							$bfr .= '			<div id="error_'.$item['name'].'" class="error_container">'."\n";
							$bfr .='			</div>'."\n";
						}
						break;
					case 'file':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<input type="'.$item['type'].'" id="'.$item['name'].'" name="'.$item['name'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= ' />'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						if ($this->validation) {
							$bfr .= '			<div id="error_'.$item['name'].'" class="error_container">'."\n";
							$bfr .='			</div>'."\n";
						}
						break;
					default:
						$bfr .= '			<p><strong>Unsupported type: '.$item['type'].'</strong></p>'."\n";
				}
			$bfr .= '			<div id="fbb">'."\n";
			if (isset($this->handler['command']))
				foreach ($this->handler['command'] as $item) {
					$bfr .= '				<input type="submit" id="submit_'.$item['name'].'" name="submit_'.$item['name'].'" value="'.$item['value'].'"';
					foreach ($item['properties'] as $key => $value)
						$bfr .= ' '.$key.'="'.$value.'"';
					$bfr .= ' />'."\n";
				}
			if (isset($this->handler['cancel']))
				foreach ($this->handler['cancel'] as $item) {
					$bfr .= '				<input type="reset" id="reset_'.$item['name'].'" name="reset_'.$item['name'].'" value="'.$item['value'].'"';
					foreach ($item['properties'] as $key => $value)
						$bfr .= ' '.$key.'="'.$value.'"';
					$bfr .= ' />'."\n";
				}
			$bfr .= '			</div>'."\n";
			if (isset($this->handler['text']))
				foreach ($this->handler['text'] as $item) {
					$bfr .= '			<div class="fbc">'."\n";
					$bfr .= '				'.$item."\n";
					$bfr .= '			</div>'."\n";
				}
			if (isset($this->handler['hidden']))
				foreach ($this->handler['hidden'] as $item)
					$bfr .= '			<input type="hidden" id="hidden_'.$item['name'].'" name="hidden_'.$item['name'].'" value="'.$item['value'].'" />'."\n";
			$bfr .= '		</form>'."\n";
			if (count($message)) {
				$bfr .= '		<legend>Observa&ccedil;&atilde;o</legend>'."\n";
				$bfr .= '		<ol>'."\n";
				foreach ($message as $item)
					$bfr .= '			<li>'.$item.'</li>'."\n";
				$bfr .= '		</ol>'."\n";
			}
			$bfr .= '	</fieldset>'."\n";
			$bfr .= '</div>'."\n";
			if ($this->validation) {
				$bfr .= '<script type="text/javascript">'."\n";
				$bfr .= '	$(document).ajaxError(function(e, xhr, settings, exception) {'."\n";
				$bfr .= '		alert(\'( AJAX ERROR )\nurl: \'+settings.url+\'\nerror:\n\'+xhr.responseText);'."\n";
				$bfr .= '	});'."\n";
				$bfr .= '	$(document).ready(function() {'."\n";
				if (isset($this->handler['script']))
					foreach ($this->handler['script'] as $line)
						$bfr .= '		'.$line."\n";
				$bfr .= '		$(\'[id^=error_]\').hide();'."\n";
				if ($this->autoclean) {
					$bfr .= '		$(\'input:text, input:password\').each(function() {'."\n";
					$bfr .= '			if (!$(this).attr(\'readonly\')) {'."\n";
					$bfr .= '				var defval = this.value;'."\n";
					$bfr .= '				$(this).focus(function() {'."\n";
					$bfr .= '					if (this.value == defval)'."\n";
					$bfr .= '						this.value = \'\';'."\n";
					$bfr .= '				});'."\n";
					$bfr .= '				$(this).blur(function() {'."\n";
					$bfr .= '					if (this.value == \'\')'."\n";
					$bfr .= '						this.value = defval;'."\n";
					$bfr .= '				});'."\n";
					$bfr .= '			}'."\n";
					$bfr .= '		});'."\n";
				}
				$bfr .= '	});'."\n";
				$bfr .= '	$("#'.$this->handler['header']['id'].'").validate({'."\n";
				$r = array();
				foreach ($this->handler['input'] as $item) {
					if (count($item['rules'])) {
						$line = '			'.$item['name'].': {'."\n";
						$tmp = array();
						foreach ($item['rules'] as $key => $value)
							if (is_bool($value))
								$tmp[] = '				'.$key.': '.($value ? 'true' : 'false');
							else if (is_numeric($value))
								$tmp[] = '				'.$key.': '.intval($value);
							else if (is_array($value)) {
								if (is_numeric(current($value)))
									$tmp[] = '				'.key($value).': '.intval(current($value));
								else if (is_array(current($value)))
									$tmp[] = '				'.key($value).': ['.implode(',', current($value)).']';
								else
									$tmp[] = '				'.key($value).': "'.current($value).'"';
							} else if (is_numeric($key))
								$tmp[] = '				'.$value.': true';
							else
								$tmp[] = '				'.$key.': \''.$value.'\'';
						$line .= implode(', '."\n", $tmp)."\n";
						$line .= '			}';
						$r[] = $line;
					}
				}
				if (count($r)) {
					$bfr .= '		rules: {'."\n";
					$bfr .= implode(', '."\n", $r)."\n";
					$bfr .= '		},'."\n";
				}
				$m = array();
				foreach ($this->handler['input'] as $item) {
					if (count($item['messages'])) {
						$line = '			'.$item['name'].': {'."\n";
						$tmp = array();
						foreach ($item['messages'] as $key => $value)
							$tmp[] = '				'.$key.': \''.htmlentities($value, ENT_QUOTES | ENT_IGNORE, 'UTF-8').'\'';
						$line .= implode(', '."\n", $tmp)."\n";
						$line .= '			}';
						$m[] = $line;
					}
				}
				if (count($m)) {
					$bfr .= '		messages: {'."\n";
					$bfr .= implode(', '."\n", $m)."\n";
					$bfr .= '		},'."\n";
				}
				$bfr .= '		highlight: function(element, errorClass, validClass) {'."\n";
				$bfr .= '			$(element).addClass(\'invalid_field\').removeClass(\'valid_field\');'."\n";
				$bfr .= '			$(element.form).find(\'label[for=\'+element.id+\']\').addClass(\'invalid_label\');'."\n";
				$bfr .= '			$(\'#error_\'+element.id).show();'."\n";
				$bfr .= '		},'."\n";
				$bfr .= '		unhighlight: function(element, errorClass, validClass) {'."\n";
				$bfr .= '			$(element).removeClass(\'invalid_field\').addClass(\'valid_field\');'."\n";
				$bfr .= '			$(element.form).find(\'label[for=\'+element.id+\']\').removeClass(\'invalid_label\');'."\n";
				$bfr .= '			$(\'#error_\'+element.id).hide();'."\n";
				$bfr .= '		},'."\n";
				$bfr .= '		errorPlacement: function(error, element) {'."\n";
				$bfr .= '			error.appendTo(\'#error_\'+element.attr(\'name\'));'."\n";
				$bfr .= '		},'."\n";
				$bfr .= '		invalidHandler: function(form, validator) {'."\n";
				$bfr .= '			alert(\'Preencha todos os campos corretamente\');'."\n";
				$bfr .= '		},'."\n";
				$bfr .= '		submitHandler: function(form) {'."\n";
				$bfr .= '			if ($(form).valid()) {'."\n";
				if ($this->ajaxsubmit) {
					if (isset($this->handler['submit']['on']))
						foreach ($this->handler['submit']['on'] as $line)
							$bfr .=	'					'.trim($line)."\n";
					$bfr .= '				$(form).ajaxSubmit({'."\n";
					$bfr .= '					resetForm: true,'."\n";
					$bfr .= '					dataType: \'json\','."\n";
					if (isset($this->handler['submit']['pre'])) {
						$bfr .= '					beforeSubmit: function(form_data, jq_form, options) {'."\n";
						foreach ($this->handler['submit']['pre'] as $line)
							$bfr .=	'						'.trim($line)."\n";
						$bfr .= '					},'."\n";
					}
					if (isset($this->handler['submit']['pos'])) {
						$bfr .= '					success: function (response, status, xhr, jq_form) {'."\n";
						foreach ($this->handler['submit']['pos'] as $line)
							$bfr .=	'						'.trim($line)."\n";
						$bfr .= '					}'."\n";
					}
					$bfr .= '				});'."\n";
				} else {
					$bfr .= '				form.submit();'."\n";
					$bfr .= '				return true;'."\n";
				}
				$bfr .= '			}'."\n";
				$bfr .= '			return false;'."\n";
				$bfr .= '		}'."\n";
				$bfr .= '	});'."\n";
				$bfr .= '</script>'."\n";
			}
			return $bfr;
		}

	}
