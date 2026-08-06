<?php
namespace docker {
	function adminer_object() {
		/**
		 * Prefills login fields with the corresponding ADMINER_DEFAULT_* environment variables.
		 */
		final class EnvLoginFormFieldsPlugin extends \Adminer\Plugin {
			public function __construct(
				private \Adminer\Adminer $adminer
			) { }

			public function loginFormField(...$args): string {
				return (function (...$args): string {
					$field = $this->loginFormField(...$args);

					if (!empty($_ENV['ADMINER_DEFAULT_DRIVER']) && \str_contains($field, '<select')) {
						return \preg_replace(
							'#<select name=["\']auth\[driver\]["\'].*</select>#',
							\sprintf('<input name="auth[driver]" value="%s">', $_ENV['ADMINER_DEFAULT_DRIVER']),
							$field,
						);
					}

					if (!empty($_ENV['ADMINER_DEFAULT_PASSWORD']) && \str_contains($field, 'auth[password]')) {
						return \preg_replace(
							'/name="auth\[password\]" (value="([^"]*)" )?/',
							\sprintf('name="auth[password]" value="%s" ', $_ENV['ADMINER_DEFAULT_PASSWORD']),
							$field,
						);
					}

					return \preg_replace_callback(
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
						$field,
					);
				})->call($this->adminer, ...$args);
			}
		}

		$plugins = [];
		foreach (glob('plugins-enabled/*.php') as $plugin) {
			$plugins[] = require($plugin);
		}

		$adminer = new \Adminer\Plugins($plugins);

		(function () {
			$last = &$this->hooks['loginFormField'][\array_key_last($this->hooks['loginFormField'])];
			if ($last instanceof \Adminer\Adminer) {
				$envLoginFormFieldsPlugin = new EnvLoginFormFieldsPlugin($last);
				$this->plugins[] = $envLoginFormFieldsPlugin;
				$last = $envLoginFormFieldsPlugin;
			}
		})->call($adminer);

		return $adminer;
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
