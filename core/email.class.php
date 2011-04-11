<?php

	require_once 'Swift.php';
	require_once __DIR__.'/../cfg/core/framework.config.php';

	class EMAIL {

		//sends the email
		public static function send($acc, array $to, $subject, $body, &$failures = array(), $attachment = '') {
			if (isset($_INFINITY_CFG['email']['accs'][$acc]))
				try {
					if (isset($_INFINITY_CFG['email']['accs'][$acc]['host']))
						$smtp = new Swift_SmtpTransport($_INFINITY_CFG['email']['accs'][$acc]['host'], $_INFINITY_CFG['email']['accs'][$acc]['port']);
					else
						$smtp = new Swift_SmtpTransport($_INFINITY_CFG['email']['host'], $_INFINITY_CFG['email']['port']);
					$smtp->setUsername($_INFINITY_CFG['email']['accs'][$acc]['user']);
					$smtp->setpassword($_INFINITY_CFG['email']['accs'][$acc]['pass']);

					$message = new Swift_Message();
					$message->setPriority(1);
					$message->getHeaders()->addTextHeader('X-Mailer', 'fw');
					$message->getHeaders()->addTextHeader('User-Agent', 'fw');
					$message->setSubject($subject);
					$message->setBody($body, 'text/html');
					$message->addPart(utf8_encode(html_entity_decode(strip_tags($body))), 'text/plain');
					$message->setFrom(array($_INFINITY_CFG['email']['accs'][$acc]['user'] => $_INFINITY_CFG['email']['accs'][$acc]['name']));
					$message->setTo($to);
					if (isset($_INFINITY_CFG['email']['accs'][$acc]['reply']))
						$message->setReplyTo($_INFINITY_CFG['email']['accs'][$acc]['reply']);
					else
						$message->setReplyTo(array($_INFINITY_CFG['email']['accs'][$acc]['user'] => $_INFINITY_CFG['email']['accs'][$acc]['name']));
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
