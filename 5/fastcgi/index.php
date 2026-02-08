<?php
namespace docker {
	function adminer_object() {
		/**
		 * Prefills the “Server” field with the ADMINER_DEFAULT_SERVER environment variable.
		 */
		final class DefaultServerPlugin extends \Adminer\Plugin {
			public function __construct(
				private \Adminer\Adminer $adminer
			) { }

			public function loginFormField(...$args): string {
				return (function (...$args): string {
					$field = $this->loginFormField(...$args);
		
					return \preg_replace_callback(
						'/name="auth\[server\]" value="" title="(?:[^"]+)"/',
						static function (array $matches): string {
							return \str_replace(
								'value=""',
								\sprintf('value="%s"', ($_ENV['ADMINER_DEFAULT_SERVER'] ?: 'db')),
								$matches[0],
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
				$defaultServerPlugin = new DefaultServerPlugin($last);
				$this->plugins[] = $defaultServerPlugin;
				$last = $defaultServerPlugin;
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
