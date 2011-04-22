<?php

	require_once 'swift_required.php';
	require_once __DIR__.'/../cfg/core/framework.config.php';

	class EMAIL {

		public static function load($file, array $replace) {
			$file = __DIR__.'/../mail/'.$file;
			if ((file_exists($file)) && (is_file($file))) {
				$src = file_get_contents($file);
				foreach ($replace as $key => $value)
					$src = str_replace('%'.strtoupper($key).'%', $value, $src);
				return $src;
			}
			return false;
		}

		//sends the email
		public static function send($acc, array $to, $subject, $body, &$failures = array(), $attachment = null) {
			global $_INFINITY_CFG;
			if (isset($_INFINITY_CFG['email']['accs'][$acc]))
				try {
					if (isset($_INFINITY_CFG['email']['accs'][$acc]['host']))
						$smtp = new Swift_SmtpTransport($_INFINITY_CFG['email']['accs'][$acc]['host'], $_INFINITY_CFG['email']['accs'][$acc]['port']);
					else
						$smtp = new Swift_SmtpTransport($_INFINITY_CFG['email']['host'], $_INFINITY_CFG['email']['port']);
					$smtp->setUsername($_INFINITY_CFG['email']['accs'][$acc]['user']);
					if (isset($_INFINITY_CFG['email']['accs'][$acc]['pass']))
						$smtp->setpassword($_INFINITY_CFG['email']['accs'][$acc]['pass']);
					$message = new Swift_Message();
					$message->setPriority(1);
					$message->getHeaders()->addTextHeader('X-Mailer', 'infinity-framework');
					$message->getHeaders()->addTextHeader('User-Agent', 'infinity-framework');
					$message->setSubject($subject);
					$message->setBody($body, 'text/html');
					$message->addPart(utf8_encode(html_entity_decode(strip_tags($body))), 'text/plain');
					$message->setFrom(array($_INFINITY_CFG['email']['accs'][$acc]['user'] => $_INFINITY_CFG['email']['accs'][$acc]['name']));
					$message->setTo($to);
					if (isset($_INFINITY_CFG['email']['accs'][$acc]['reply']))
						$message->setReplyTo($_INFINITY_CFG['email']['accs'][$acc]['reply']);
					else
						$message->setReplyTo(array($_INFINITY_CFG['email']['accs'][$acc]['user'] => $_INFINITY_CFG['email']['accs'][$acc]['name']));
					if (!is_null($attachment)) {
						if (is_array($attachment))
							foreach ($attachment as $file)
								$message->attach(Swift_Attachment::fromPath($file));
						else
							$message->attach(Swift_Attachment::fromPath($attachment));
					}
					$mailer = new Swift_Mailer($smtp);
					if (count($to) > 1) {
						//$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(60, 30));
						//$mailer->registerPlugin(new Swift_Plugins_ThrottlerPlugin(1, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE));
						try {
							return $mailer->batchSend($message, $failures);
						} catch (Exception $e) {
							return false;
						}
					} else
						try {
							return $mailer->send($message, $failures);
						} catch (Exception $e) {
							return false;
						}
				} catch (Exception $e) {
					return false;
				}
			return false;
		}

	}

?>
