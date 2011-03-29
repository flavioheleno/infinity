<?php

	require_once __DIR__.'/cfg/core/framework.config.php';

	class EMAIL {
		//holds configuration for email connection
		private $cfg = array();

		//class constructor
		public function __construct() {
			global $_INFINITY_CFG;
			$this->cfg = $_INFINITY_CFG['email'];
		}

		//sends the email
		public function send($acc, array $to, $subject, $body, &$failures = array(), $attachment = '') {
			if (isset($this->cfg['accs'][$acc]))
				try {
					if (isset($this->cfg['accs'][$acc]['host']))
						$smtp = new Swift_SmtpTransport($this->cfg['accs'][$acc]['host'], $this->cfg['accs'][$acc]['port']);
					else
						$smtp = new Swift_SmtpTransport($this->cfg['host'], $this->cfg['port']);
					$smtp->setUsername($this->cfg['accs'][$acc]['user']);
					$smtp->setpassword($this->cfg['accs'][$acc]['pass']);

					$message = new Swift_Message();
					$message->setPriority(1);
					$message->getHeaders()->addTextHeader('X-Mailer', 'fw');
					$message->getHeaders()->addTextHeader('User-Agent', 'fw');
					$message->setSubject($subject);
					$message->setBody($body, 'text/html');
					$message->addPart(utf8_encode(html_entity_decode(strip_tags($body))), 'text/plain');
					$message->setFrom(array($this->cfg['accs'][$acc]['user'] => $this->cfg['accs'][$acc]['name']));
					$message->setTo($to);
					if (isset($this->cfg['accs'][$acc]['reply']))
						$message->setReplyTo($this->cfg['accs'][$acc]['reply']);
					else
						$message->setReplyTo(array($this->cfg['accs'][$acc]['user'] => $this->cfg['accs'][$acc]['name']));
					if ($attachment != '')
						$message->attach(Swift_Attachment::fromPath($attachment));
					$mailer = new Swift_Mailer($smtp);
					if (count($to) > 1) {
						$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(60, 30));
						$mailer->registerPlugin(new Swift_Plugins_ThrottlerPlugin(1, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE));
						try {
							$ret = $mailer->batchSend($message, $failures);
						} catch (Exception $e) {
							$ret = 0;
						}
					} else
						try {
							$ret = $mailer->send($message, $failures);
						} catch (Exception $e) {
							$ret = 0;
						}
					return $ret;
				} catch (Exception $e) {
					return 0;
				}
			else
				return 0;
		}

	}

?>
