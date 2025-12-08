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

			public function loginFormField(...$args) {
				return (function (...$args) {
					$field = $this->loginFormField(...$args);

					return \str_replace(
						'name="auth[server]" value="" title="hostname[:port]"',
						\sprintf('name="auth[server]" value="%s" title="hostname[:port]"', ($_ENV['ADMINER_DEFAULT_SERVER'] ?: 'db')),
						$field,
					);
				})->call($this->adminer, ...$args);
			}

			public function loginForm(...$args) {
				return (function (...$args) {
					ob_start();
					$return = $this->loginForm(...$args);
					$form = ob_get_clean();

					$form = str_replace(
						'name="auth[server]" value="" title="hostname[:port]"',
						'name="auth[server]" value="'.(getenv('ADMINER_DEFAULT_SERVER') ?: 'db').'" title="hostname[:port]"',
						$form
					);

					$form = str_replace(
						'name="auth[username]" id="username" value=""',
						'name="auth[username]" value="'.(getenv('ADMINER_DEFAULT_USER') ?: '').'"',
						$form
					);

					$form = str_replace(
						'name="auth[password]"',
						'name="auth[password]" value="'.(getenv('ADMINER_DEFAULT_PASSWORD') ?: '').'"',
						$form
					);

					$form = str_replace(
						'name="auth[db]"',
						'name="auth[db]" value="'.(getenv('ADMINER_DEFAULT_DB') ?: '').'"',
						$form
					);

					$driver = $_ENV['ADMINER_DEFAULT_DRIVER'] ?? null;
					$script = '';
					if ($driver !== null && $driver !== '') {
						$script = '<script>(function(){var f=document;var el=f.querySelector("[name='."\"auth[driver]\"".']"); if(el){el.value=' . json_encode($driver) . ';}})();</script>';
					}

					echo $form . $script;
					return $return;
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
