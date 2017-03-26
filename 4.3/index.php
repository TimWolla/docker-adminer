<?php
namespace docker {
	function adminer_object() {
		class Adminer extends \Adminer {
			function loginForm() {
				ob_start();
				$return = parent::loginForm();
				$form = ob_get_clean();

				echo str_replace('name="auth[server]" value="" title="hostname[:port]"', 'name="auth[server]" value="db" title="hostname[:port]"', $form);

				return $return;
			}
		}

		return new Adminer();
	}
}

namespace {
	if (basename($_SERVER['REQUEST_URI']) === 'adminer.css' && is_readable('adminer.css')) {
		header('Content-Type: text/css');
		readfile('adminer.css');
		exit;
	}

	function adminer_object() {
		return \docker\adminer_object();
	}

	require('adminer.php');
}
