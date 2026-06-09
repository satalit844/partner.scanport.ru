
/**
 * Compatibility wrapper.
 * @var modX $modx
 */
$corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
return include rtrim($corePath, '/') . '/controllers/practices.php';