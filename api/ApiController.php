<?php

require_once 'PasswordHasher.php';
require_once 'repositories/UserRepository.php';
require_once 'repositories/EmailConfirmRepository.php';
require_once 'repositories/SessionRepository.php';

class ApiController {

    /**
     * @var array
     */
    private $config;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @param $config
     * @param $pdo
     */
    public function __construct($config, $pdo) {
        $this->config = $config;
        $this->pdo = $pdo;
    }

    /**
     * @param $username
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $password
     *
     * @return stdClass
     * @throws Exception
     */
    public function registerUser($username, $firstname, $lastname, $email, $password) {
        self::validateUserData($username, $firstname, $lastname, $email, $password);

        $randomSalt = PasswordHasher::createSalt();
        $salt = $this->config->passwordSaltStatic . $randomSalt;
        $hashedPassword = PasswordHasher::createHash($password, $salt);

        $userRepository = new UserRepository($this->pdo);
        if ($userRepository->userExistsByUsernameOrEmail($username, $email)) {
            throw new Exception('User already exists by username or email.');
        }

        $this->pdo->beginTransaction();

        $user = $userRepository->addUser($username, $firstname, $lastname, $email,
            $randomSalt, $hashedPassword);

        $confirmToken = $randomSalt = PasswordHasher::createSalt();
        $emailConfirmRepository = new EmailConfirmRepository($this->pdo);
        $emailConfirmRepository->addEmailConfirm($user->id, $email, $confirmToken);

        $this->pdo->commit();

        // todo: send mail for email confirm.

        return $user;
    }

    /**
     * @param $username
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $password
     *
     * @return bool
     * @throws Exception
     */
    private static function validateUserData($username, $firstname, $lastname, $email, $password) {
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
        // todo: check/sanitize characters in username, firstname and lastname

        return true;
    }

    /**
     * @param $username
     * @param $password
     * @param $remember
     *
     * @return array
     * @throws Exception
     */
    public function loginUser($username, $password, $remember) {

        // todo: check for invalid login attempts (per user/global) and throttle/deny access.
        // todo: apply different throttle standards to different IPs or user clients/browsers

        $userRepository = new UserRepository($this->pdo);
        $user = $userRepository->getUser($username);

        $passwordHasher = new PasswordHasher();
        $salt = $this->config->passwordSaltStatic . $user->passwordSalt;

        if (!$passwordHasher->validatePassword($user->passwordHash, $password, $salt)) {
            // todo: log invalid login attempt
            // todo: create 401 Exception
            throw new Exception('not authorised.');
        }

        // todo: after one week (make configurable) of registration check if mail is validated.

        // todo: log valid attempt with IP hash and browser fingerprint hash.
        // todo: think about privacy in regard to saving IP and browser fingerprint.

        if ($remember) {
            $secondsToExpire = $this->config->longSessionDuration;
        } else {
            $secondsToExpire = $this->config->shortSessionDuration;
        }

        $sessionToken = PasswordHasher::createSalt();

        // todo: hash sessionToken

        $sessionRepository = new SessionRepository($this->pdo);
        $sessionRepository->addSession($user->id, $secondsToExpire, $sessionToken, $remember);

        return array(
            'username' => $username,
            'password' => '',
            'remember' => $remember,
            'sessionToken' => $sessionToken
        );
    }

    /**
     * @param $sessionToken
     *
     * @return mixed
     * @throws Exception
     */
    public function checkSession($sessionToken) {
        $sessionRepository = new SessionRepository($this->pdo);

        // todo: do this with a cronjob
        $sessionRepository->removeExpiredSessions();

        $session = $sessionRepository->getNotExpiredSession($sessionToken);
        if (!$session) {
            // todo: create 401 Exception
            throw new Exception('You are not authorized.');
        }

        if (!$session->remember) {
            $secondsToExpire = $this->config->shortSessionDuration;
            $sessionRepository->updateSessionExpire($sessionToken, $secondsToExpire);
        }

        return $session->userId;
    }
}