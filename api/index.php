<?php

require_once 'Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

require_once 'config.inc.php';
// todo: autoload?
require_once 'PasswordHasher.php';
require_once 'UserRepository.php';
require_once 'EmailConfirmRepository.php';

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
        $username = $params['username'];
        $firstname = $params['firstname'];
        $lastname = $params['lastname'];
        $email = $params['email'];
        $password = $params['password'];

        if (strlen($username) < 3) {
            throw new Exception('username is to short, needs to be at least 3 characters long.');
        } else if (strlen($username) > 30) {
            throw new Exception('username is to long, needs to be under 30 characters long.');
        }
        if (strlen($firstname) < 3) {
            throw new Exception('firstname is to short, needs to be at least 3 characters long.');
        } else if (strlen($firstname) > 30) {
            throw new Exception('firstname is to long, needs to be under 30 characters long.');
        }
        if (strlen($lastname) < 3) {
            throw new Exception('lastname is to short, needs to be at least 3 characters long.');
        } else if (strlen($lastname) > 30) {
            throw new Exception('lastname is to long, needs to be under 30 characters long.');
        }

        // todo: username exclude names that are used for navigation
        // todo: regex type mail

        $passwordHasher = new PasswordHasher();
        $randomSalt = $passwordHasher->createSalt();
        $salt = $config->passwordSaltStatic . $randomSalt;
        $hashedPassword = $passwordHasher->createHash($password, $salt);


        $userRepository = new UserRepository($pdo);
        $emailConfirmRepository = new EmailConfirmRepository($pdo);

        if ($userRepository->userExistsByUsernameOrEmail($username, $email)) {
            throw new Exception('User already exists by username or email.');
        }

        $pdo->beginTransaction();

        $user = $userRepository->addUser($username, $firstname, $lastname, $email,
            $randomSalt, $hashedPassword);

        $confirmToken = $passwordHasher->createSalt();
        $emailConfirmRepository->addEmailConfirm($user->id, $email, $confirmToken);

        $pdo->commit();

        // todo: send mail for email confirm.

        // todo: create user session and return accessToken.


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
        $username = $params['username'];
        $password = $params['password'];
        $remember = $params['remember'];

        $userRepository = new UserRepository($pdo);

        // todo check for invalid login attempts.

        $user = $userRepository->getUser($username);

        $passwordHasher = new PasswordHasher();
        $salt = $config->passwordSaltStatic . $user->salt;
        if (!$passwordHasher->validatePassword($user->passwordHash, $password, $salt)) {
            // todo: log invalid login attempts
            // todo: create new Exceptions for 403
            throw new Exception('not authorised.');
        }

        // todo: create real response.


        /*$result = array(
            'username' => $username,
            'password' => '',
            'remember' => $remember,
            'accessToken' => '9823nr89dsfoij3f'
        );

        $app->response()->body(json_encode($result, JSON_PRETTY_PRINT));*/
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