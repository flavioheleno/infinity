<?php

	class FORM {

		//returns the css files needed by form
		public function css() {
			return array(
				'css/core/form.css'
			);
		}

		//returns the js files needed by validation script
		public function js() {
			return array(
				'js/core/jquery.js',
				'js/core/jquery.form.js',
				'js/core/jquery.maskedinput.js',
				'js/core/jquery.validate.js',
				'js/core/jquery.validate.additional-methods.js',
				'js/core/jquery.validate.messages_ptbr.js',
				'js/core/msg.js'
			);
		}

		//creates a new form
		public function create($title, $id, $action, $method = 'post', $enctype = 'application/x-www-form-urlencoded') {
			return array(
				'header' => array(
					'title' => $title,
					'id' => 'form_'.$id,
					'action' => $action,
					'method' => $method,
					'enctype' => $enctype
				),
				'input' => array()
			);
		}

		//adds an input field to given handle
		public function input(&$handle, $type, $label, $name, $value, array $properties = array(), array $rules = array(), array $messages = array()) {
			$handle['input'][] = array(
				'type' => $type,
				'label' => $label,
				'name' => $name,
				'value' => $value,
				'properties' => $properties,
				'rules' => $rules,
				'messages' => $messages
			);
		}

		//adds a command button to given handle
		public function command(&$handle, $name, $value, array $properties = array()) {
			$handle['command'][] = array(
				'name' => $name,
				'value' => $value,
				'properties' => $properties
			);
		}

		//adds a cancel button to given handle
		public function cancel(&$handle, $name, $value, array $properties = array()) {
			$handle['cancel'][] = array(
				'name' => $name,
				'value' => $value,
				'properties' => $properties
			);
		}

		//javascript that will be executed before submitting the form
		public function before_submit($js) {
			$handle['submit']['pre'] = $js;
		}

		//javascript that will be executed after submitting the form
		public function after_submit($js) {
			$handle['submit']['pos'] = $js;
		}

		//renders the form
		public function render($handle, $validation = true, array $message = array()) {
			$bfr = '<div id="form">'."\n";
			$bfr .= '	<fieldset>'."\n";
			$bfr .= '		<legend>'.$handle['header']['title'].'</legend>'."\n";
			$bfr .= '		<form';
			foreach ($handle['header'] as $key => $value)
				$bfr .= ' '.$key.'="'.$value.'"';
			$bfr .= '>'."\n";
			foreach ($handle['input'] as $item)
				switch ($item['type']) {
					case 'text':
					case 'password':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['type'].'_'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<input type="'.$item['type'].'" id="'.$item['type'].'_'.$item['name'].'" name="'.$item['type'].'_'.$item['name'].'"';
						if ((isset($item['value'])) && ($item['value'] != ''))
							$bfr .= ' value="'.$item['value'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= ' />'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						$bfr .= '			<div id="error_'.$item['type'].'_'.$item['name'].'" class="error_container">'."\n";
						$bfr .='			</div>'."\n";
						break;
					case 'textarea':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['type'].'_'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<textarea id="'.$item['type'].'_'.$item['name'].'" name="'.$item['type'].'_'.$item['name'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= '>'."\n";
						if ((isset($item['value'])) && ($item['value'] != ''))
							$bfr .= '					'.$item['value']."\n";
						$bfr .= '					</textarea>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						$bfr .= '			<div id="error_'.$item['type'].'_'.$item['name'].'" class="error_container">'."\n";
						$bfr .='			</div>'."\n";
						break;
					case 'checkbox':
					case 'radio':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<input type="'.$item['type'].'" id="'.$item['type'].'_'.$item['name'].'" name="'.$item['type'].'_'.$item['name'].'"';
						if ((isset($item['value'])) && ($item['value'] != ''))
							$bfr .= ' value="'.$item['value'].'"';
						foreach ($item['properties'] as $key => $value)
							$bfr .= ' '.$key.'="'.$value.'"';
						$bfr .= ' />'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['type'].'_'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '			</div>'."\n";
						$bfr .= '			<div id="error_'.$item['type'].'_'.$item['name'].'" class="error_container">'."\n";
						$bfr .='			</div>'."\n";
						break;
					case 'select':
						$bfr .= '			<div class="fb">'."\n";
						$bfr .= '				<div class="fbl">'."\n";
						$bfr .= '					<label id="label_'.$item['name'].'" for="'.$item['type'].'_'.$item['name'].'">'.$item['label'].':</label>'."\n";
						$bfr .= '				</div>'."\n";
						$bfr .= '				<div class="fbr">'."\n";
						$bfr .= '					<select id="'.$item['type'].'_'.$item['name'].'" name="'.$item['type'].'_'.$item['name'].'"';
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
						$bfr .= '			<div id="error_'.$item['type'].'_'.$item['name'].'" class="error_container">'."\n";
						$bfr .='			</div>'."\n";
						break;
					case 'hidden':
						$bfr .= '			<input type="hidden" id="hidden_'.$item['name'].'" name="hidden_'.$item['name'].'" value="'.$item['value'].'" />'."\n";
						break;
					default:
						$bfr .= '			<p><strong>Unsupported type: '.$item['type'].'</strong></p>'."\n";
				}
			$bfr .= '			<div id="fbb">'."\n";
			if (isset($handle['command']))
				foreach ($handle['command'] as $item) {
					$bfr .= '				<input type="submit" id="submit_'.$item['name'].'" name="submit_'.$item['name'].'" value="'.$item['value'].'"';
					foreach ($item['properties'] as $key => $value)
						$bfr .= ' '.$key.'="'.$value.'"';
					$bfr .= ' />'."\n";
				}
			if (isset($handle['cancel']))
				foreach ($handle['cancel'] as $item) {
					$bfr .= '				<input type="reset" id="reset_'.$item['name'].'" name="reset_'.$item['name'].'" value="'.$item['value'].'"';
					foreach ($item['properties'] as $key => $value)
						$bfr .= ' '.$key.'="'.$value.'"';
					$bfr .= ' />'."\n";
				}
			$bfr .= '			</div>'."\n";
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
			if ($validation) {
				$bfr .= '<script type="text/javascript">'."\n";
				$bfr .= '	$(document).ready(function() {'."\n";
				$bfr .= '		$(\'[id^=error_]\').hide();'."\n";
				$bfr .= '	});'."\n";
				$bfr .= '	$("#'.$handle['header']['id'].'").validate({'."\n";
				$r = array();
				foreach ($handle['input'] as $item) {
					if (count($item['rules'])) {
						$line = '			'.$item['type'].'_'.$item['name'].': {'."\n";
						$tmp = array();
						foreach ($item['rules'] as $key => $value)
							if (is_bool($value))
								$tmp[] = '				'.$key.': '.($value ? 'true' : 'false');
							else if (is_numeric($value))
								$tmp[] = '				'.$key.': '.intval($value);
							else
								$tmp[] = '				'.$key.': \''.$value.'\'';
						$line .= implode(', '."\n", $tmp)."\n";
						$line .= '			}'."\n";
						$r[] = $line;
					}
				}
				if (count($r)) {
					$bfr .= '		rules: {'."\n";
					$bfr .= implode(', '."\n", $r);
					$bfr .= '		},'."\n";
				}
				$m = array();
				foreach ($handle['input'] as $item) {
					if (count($item['messages'])) {
						$line = '			'.$item['type'].'_'.$item['name'].': {'."\n";
						$tmp = array();
						foreach ($item['messages'] as $key => $value)
							$tmp[] = '				'.$key.': \''.$value.'\'';
						$line .= implode(', '."\n", $tmp)."\n";
						$line .= '			}'."\n";
						$m[] = $line;
					}
				}
				if (count($m)) {
					$bfr .= '		messages: {'."\n";
					$bfr .= implode(', '."\n", $m);
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
				$bfr .= '				$(\'#form_result\').html(\'Enviando dados, aguarde..\');'."\n";
				$bfr .= '				$(form).ajaxSubmit({'."\n";
				$bfr .= '					clearForm: true,'."\n";
				$bfr .= '					dataType: \'json\','."\n";
				if (isset($handle['submit'])) {
					if (isset($handle['submit']['pre'])) {
						$bfr .= '					beforeSubmit: function() {'."\n";
						$bfr .=	'						'.$handle['submit']['pre']."\n";
						$bfr .= '					},'."\n";
					}
					if (isset($handle['submit']['pos'])) {
						$bfr .= '					success: function (data) {'."\n";
						$bfr .= '						'.$handle['submit']['pos']."\n";
						$bfr .= '					}'."\n";
					}
				}
				$bfr .= '				});'."\n";
				$bfr .= '			}'."\n";
				$bfr .= '			return false;'."\n";
				$bfr .= '		}'."\n";
				$bfr .= '	});'."\n";
				$bfr .= '</script>'."\n";
			}
			return $bfr;
		}

	}

?>
