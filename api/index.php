<?php

require_once 'Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

require_once 'config.inc.php';
// todo: autoload?
require_once 'UserRepository.php';

$app = new \Slim\Slim(array('debug' => true));

$response = $app->response();
$response->header('Content-Type', 'application/json');

$app->post('/session(/)', function() use($app) {
    try {
        $params = (array) json_decode($app->request()->getBody());
        if (!isset($params['username']) ||
                !isset($params['password']) ||
                !isset($params['remember'])) {
            throw new Exception('params not set.');
        }
        $username = $params['username'];
        $password = $params['password'];
        $remember = $params['remember'];

        $userRepository = new UserRepository();
        if (!$userRepository->verifyUser($username, $password)) {
            // todo: log invalid login attempts
            // todo: create new Exceptions for 403
            throw new Exception('not authorised.');
        }

        // check for invalid login attempts

        // todo: create real response.
        $result = array(
            'username' => $username,
            'password' => '',
            'remember' => $remember,
            'accessToken' => '9823nr89dsfoij3f'
        );

        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));
    } catch (Exception $exception) {
        $result = array(
            'exception' => $exception->getMessage()
        );
        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));

        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $exception->getMessage());
    }
});


$app->run();