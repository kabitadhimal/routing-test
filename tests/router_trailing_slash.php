<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container;
use App\Router;
use App\Controllers\UserController;

function runTest(string $uri, string $expectedSubstring)
{
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = '/routing-test/public/index.php'; // ensure fallback base path

    $container = new Container();
    $router = new Router($container, ''); // pass empty basePath to rely on SCRIPT_NAME fallback

    $router->get('/users', [UserController::class, 'listing']);
    $router->get('/users/{id}/{mode}', [UserController::class, 'edit']);

    $output = $router->resolve();

    if (strpos($output, $expectedSubstring) !== false) {
        echo "PASS: {$uri} contains {$expectedSubstring}\n";
        return true;
    }

    echo "FAIL: {$uri} does not contain {$expectedSubstring}\n";
    echo "Output was: \n" . $output . "\n";
    return false;
}

$allOk = true;
$allOk &= runTest('/routing-test/public/users', "Its' the list of users");
$allOk &= runTest('/routing-test/public/users/', "Its' the list of users");
$allOk &= runTest('/routing-test/public/users/2/profile', 'User Profile');
// Additional scenarios for different server configs
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$allOk &= runTest('/users', "Its' the list of users");

$_SERVER['SCRIPT_NAME'] = '/subdir/index.php';
$_SERVER['PHP_SELF'] = '/subdir/index.php';
$allOk &= runTest('/subdir/users/', "Its' the list of users");

$_SERVER['SCRIPT_NAME'] = '/subdir/public/index.php';
$_SERVER['PHP_SELF'] = '/subdir/public/index.php';
$allOk &= runTest('/subdir/public/users', "Its' the list of users");
if ($allOk) {
    echo "\nAll tests passed.<br/>";
    exit(0);
}

echo "\nSome tests failed.<br/>";
exit(1);
