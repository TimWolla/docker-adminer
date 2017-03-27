<?php
if (PHP_SAPI !== 'cli') exit;
if ($_SERVER['argc'] !== 2) exit;

$name = $_SERVER['argv'][1];
// Sanity checks.
if (basename($name) !== $name) {
	fwrite(STDERR, 'Refusing to load plugin file "'.$name.'" for security reasons.'."\n");
	exit(1);
}
if (!is_readable('plugins/'.$name.'.php')) {
	fwrite(STDERR, 'Unable to find plugin file "'.$name.'".'."\n");
	exit(1);
}

// Try to find class.
$file = 'plugins/'.$name.'.php';
$code = file_get_contents('plugins/'.$name.'.php');
$tokens = token_get_all($code);

$classFound = false;
$classes = [];
for ($i = 0, $max = count($tokens); $i < $max; $i++) {
	if ($tokens[$i][0] === T_CLASS) $classFound = true;
	if ($classFound && $tokens[$i][0] === T_STRING) {
		$classes[] = $tokens[$i][1];
		$classFound = false;
	}
}

// Sanity checks.
if (count($classes) == 0) {
	fwrite(STDERR, 'Unable to load plugin file "'.$name.'", because it does not define any classes.'."\n");
	exit(1);
}

if (count($classes) > 1) {
	fwrite(STDERR, 'Unable to load plugin file "'.$name.'", because it defines multiple classes.'."\n");
	exit(1);
}

// Check constructor.
$class = $classes[0];
require($file);

$constructor = (new \ReflectionClass($class))->getConstructor();

if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
	$requiredParameters = array_slice($constructor->getParameters(), 0, $constructor->getNumberOfRequiredParameters());

	fwrite(STDERR, 'Unable to load plugin file "'.$name.'", because it has required parameters: '.implode(', ', array_map(function ($item) {
		return $item->getName();
	}, $requiredParameters))."\n".
'Create a file "'.getcwd().'/plugins-enabled/'.$name.'.php" with the following contents to load the plugin:'."\n\n".
'<?php
require_once('.var_export($file, true).');

'.$constructor->getDocComment().'
return new '.$class.'(
	'.implode(",\n\t", array_map(function ($item) {
		return '$'.$item->getName()." = ".($item->isOptional() ? var_export($item->getDefaultValue(), true) : '???');
	}, $constructor->getParameters())).'
);
');
	exit(1);
}

echo '<?php
require_once('.var_export($file, true).');

return new '.$class.'();
';
exit(0);
