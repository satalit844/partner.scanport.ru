
$corePath = dirname(dirname(dirname(__FILE__))) . '/';
$classFile = $corePath . 'controllers/mgr/practices.class.php';
if (is_file($classFile)) {
    require_once $classFile;
}