<?php

	require_once __DIR__.'/template.class.php';
	require_once __DIR__.'/form.class.php';
	require_once __DIR__.'/xhtml.class.php';

	abstract class VIEW {
		//instance of template class
		protected $tpl = null;
		//instance of form class
		protected $form = null;
		//instance of auxiliar class
		protected $aux = null;
		//instance of msg class
		protected $msg = null;
		//instance of xhtml class
		protected $xhtml = null;

		//class constructor
		public function __construct($path, $cache, $msg) {
			$this->tpl = new TEMPLATE($path, $cache);
			$this->form = new FORM();
			$this->aux = AUTOLOAD::loadAuxView();
			$this->msg = $msg;
			$this->xhtml = new XHTML();
		}

		public function message($type, $alert, $title, $message, $back = false, $block = 'template-mensagem') {
			$this->css('css/core/msg.css');
			$this->tpl->add($block, 'core/message.html');
			$this->tpl->set('mensagem-tipo', $type);
			$this->tpl->set('mensagem-alerta', $alert);
			$this->tpl->set('mensagem-titulo', $title);
			$this->tpl->set('mensagem-conteudo', $message);
			$this->tpl->hide('mensagem-ok');
			if ($back)
				$this->tpl->show('mensagem-voltar');
			else
				$this->tpl->show('mensagem-voltar');
			$this->tpl->show($block);
		}

		public function inner_message($type, $alert, $title, $message, $back = false) {
			$this->tpl->loadTemplateFile('core/message.html', true, true);
				$this->tpl->set('mensagem-tipo', $type);
			$this->tpl->set('mensagem-alerta', $alert);
			$this->tpl->set('mensagem-titulo', $title);
			$this->tpl->set('mensagem-conteudo', $message);
			$this->tpl->show('mensagem-ok');
			if ($back)
				$this->tpl->show('mensagem-voltar');
			else
				$this->tpl->hide('mensagem-voltar');
			return $this->tpl->get();
		}

		public function confirmation($alert, $title, $message, $action, $block = 'painel-conteudo') {
			//$this->css('css/core/msg.css');
			//$this->css('css/core/form.css');
			$this->tpl->add($block, 'core/confirmation.html');
			$this->tpl->set('confirmacao-acao', $action);
			$this->tpl->set('confirmacao-alerta', $alert);
			$this->tpl->set('confirmacao-titulo', $title);
			$this->tpl->set('confirmacao-conteudo', $message);
			$this->tpl->show($block);
		}

		protected function display($title, $description = '', $keywords = '') {
			if (basename($_SERVER['SCRIPT_NAME']) == 'index.php')
				$this->tpl->set('base-link', dirname($_SERVER['SCRIPT_NAME']).'?'.$_SERVER['QUERY_STRING'].'&amp;');
			else
				$this->tpl->set('base-link', $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].'&amp;');
			$this->xhtml->setTitle($title);
			$this->xhtml->setDescription($description);
			$this->xhtml->setKeywords($keywords);
			$this->xhtml->appendContent($this->tpl->get());
			$this->xhtml->render();
		}

		//default called method when no action is defined
		public abstract function index(array $env);

		//error method, called when not existent action is called
		public function error(array $env) {
			$m = $this->msg->retrieve(MAIN_ERR404, CODE_ERR);
			if (count($m)) {
				$this->tpl->show('template_header');
				$this->message('erro', $m['alert'], $m['title'], $m['text'], true, 'template-conteudo');
				$this->tpl->show('template_footer');
				$this->display('P&aacute;gina n&atilde;o encontrada');
			} else
				$this->msg->page('default');
		}
	}

?>
