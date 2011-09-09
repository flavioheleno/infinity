<?php
/**
* Basic mailer abstraction
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

	require_once 'swift_required.php';

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
			$config = CONFIGURATION::singleton();
			$config->load_core('email');
			if (isset($config->email['accs'][$acc]))
				try {
					if (isset($config->email['accs'][$acc]['host']))
						$smtp = new Swift_SmtpTransport($config->email['accs'][$acc]['host'], $config->email['accs'][$acc]['port']);
					else
						$smtp = new Swift_SmtpTransport($config->email['host'], $config->email['port']);
					$smtp->setUsername($config->email['accs'][$acc]['user']);
					if (isset($config->email['accs'][$acc]['pass']))
						$smtp->setpassword($config->email['accs'][$acc]['pass']);
					$message = new Swift_Message();
					$message->setPriority(1);
					$message->getHeaders()->addTextHeader('X-Mailer', 'infinity-framework');
					$message->getHeaders()->addTextHeader('User-Agent', 'infinity-framework');
					$message->setSubject($subject);
					$message->setBody($body, 'text/html');
					$message->addPart(utf8_encode(html_entity_decode(strip_tags($body))), 'text/plain');
					$message->setFrom(array($config->email['accs'][$acc]['user'] => $config->email['accs'][$acc]['name']));
					$message->setTo($to);
					if (isset($config->email['accs'][$acc]['reply']))
						$message->setReplyTo($config->email['accs'][$acc]['reply']);
					else
						$message->setReplyTo(array($config->email['accs'][$acc]['user'] => $config->email['accs'][$acc]['name']));
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
