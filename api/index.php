<?php

require_once 'Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

// todo: autoload?
require_once 'config.inc.php';
require_once 'ApiController.php';

$app = new \Slim\Slim(array('debug' => true));

$response = $app->response();
$response->header('Content-Type', 'application/json');

$pdo = new PDO('mysql:host=' . $config->databaseHost . ';dbname=' . $config->databaseName,
    $config->databaseUser, $config->databasePassword);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$app->post('/user(/)', function() use($app, $config, $pdo) {
    try {
        $params = (array) json_decode($app->request()->getBody());
        if (!isset($params['username']) || strlen($params['username']) === 0 ||
                !isset($params['firstname']) || strlen($params['firstname']) === 0 ||
                !isset($params['lastname']) || strlen($params['lastname']) === 0 ||
                !isset($params['email']) || strlen($params['username']) === 0 ||
                !isset($params['password']) || strlen($params['username']) === 0) {
            throw new Exception('params not set.');
        }

        $apiController = new ApiController($config, $pdo);
        $apiController->registerUser(
            $params['username'],
            $params['firstname'],
            $params['lastname'],
            $params['email'],
            $params['password']
        );

        $session = $apiController->loginUser(
            $params['username'],
            $params['password'],
            false
        );

        $app->response()->body(json_encode($session, JSON_PRETTY_PRINT));
    } catch (Exception $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $result = array(
            'exception' => $exception->getMessage()
        );
        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));

        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $exception->getMessage());
    }
});

$app->post('/session(/)', function() use($app, $config, $pdo) {
    try {
        $params = (array) json_decode($app->request()->getBody());
        if (!isset($params['username']) ||
                !isset($params['password']) ||
                !isset($params['remember'])) {
            throw new Exception('params not set.');
        }

        $apiController = new ApiController($config, $pdo);
        $session = $apiController->loginUser(
            $params['username'],
            $params['password'],
            $params['remember']
        );

        $app->response()->body(json_encode($session, JSON_PRETTY_PRINT));
    } catch (Exception $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $result = array(
            'exception' => $exception->getMessage()
        );
        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));

        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $exception->getMessage());
    }
});

$app->get('/randomRequest(/)', function() use($app, $config, $pdo) {
    try {
        $headers = $app->request()->headers;
        $sessionToken = $app->request()->headers->get('X-Session-Token');

        $apiController = new ApiController($config, $pdo);
        $apiController->checkLogin($sessionToken);

        $result = array('done' => $sessionToken);

        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));
    } catch (Exception $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $result = array(
            'exception' => $exception->getMessage()
        );
        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));

        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $exception->getMessage());
    }
});

$app->run();