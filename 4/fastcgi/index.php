<?php
namespace docker {
	function adminer_object() {
		require_once('plugins/plugin.php');

		class Adminer extends \AdminerPlugin {
			function _callParent($function, $args) {
				if ($function === 'loginForm') {
					ob_start();
					$return = \Adminer::loginForm();
					$form = ob_get_clean();

					if (!empty($_ENV['ADMINER_DEFAULT_DRIVER'])) {
						$form = \preg_replace(
							'#<select name=["\']auth\[driver\]["\'].*</select>#',
							\sprintf('<input name="auth[driver]" value="%s">', $_ENV['ADMINER_DEFAULT_DRIVER']),
							$form,
						);
					}

					if (!empty($_ENV['ADMINER_DEFAULT_PASSWORD'])) {
						$form = \preg_replace(
							'#name="auth\[password\]" (value="([^"]*)" )?#',
							\sprintf('name="auth[password]" value="%s" ', $_ENV['ADMINER_DEFAULT_PASSWORD']),
							$form,
						);
					}

					$form = \preg_replace_callback(
						'/name="auth\[(server|username|db)\]" (id="[^"]*" )?(autofocus )?value="([^"]*)"/',
						static function (array $matches): string {
							$defaultValues = ['server' => 'db'];
							return \sprintf(
								'name="auth[%s]" %s%svalue="%s"',
								$matches[1],
								$matches[2] ?: '',
								$matches[3] ?: '',
								$_ENV['ADMINER_DEFAULT_' . \strtoupper($matches[1])] ?? ($matches[4] ?: ($defaultValues[$matches[1]] ?? ''))
							);
						},
						$form,
					);

					echo $form;

					return $return;
				}

				return parent::_callParent($function, $args);
			}
		}

		$plugins = [];
		foreach (glob('plugins-enabled/*.php') as $plugin) {
			$plugins[] = require($plugin);
		}

		return new Adminer($plugins);
	}
}

namespace {
	if (basename($_SERVER['DOCUMENT_URI'] ?? $_SERVER['REQUEST_URI']) === 'adminer.css' && is_readable('adminer.css')) {
		header('Content-Type: text/css');
		readfile('adminer.css');
		exit;
	}

	function adminer_object() {
		return \docker\adminer_object();
	}

	require('adminer.php');
}
