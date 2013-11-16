<?php

include_once 'AbstractRepository.php';

class UserRepository extends AbstractRepository {

    /**
     * @var string
     */
    protected $tableName = 'user';

    /**
     *
     */
    public function createTable() {
        $statement = $this->pdo->prepare('
            CREATE TABLE IF NOT EXISTS
            ' . $this->tableName . '
            (
                id INT AUTO_INCREMENT,
                PRIMARY KEY (id),
                created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                username VARCHAR(255) NOT NULL,
                firstname VARCHAR(255) NOT NULL,
                lastname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                emailConfirmed BOOL NOT NULL DEFAULT 0,
                passwordSalt VARCHAR(255) NOT NULL,
                passwordHash VARCHAR(255) NOT NULL,
                admin BOOL NOT NULL DEFAULT 0
            ) ENGINE = InnoDB;
        ');
        $statement->execute();
    }

    /**
     * @param $username
     * @param $email
     * @return bool
     */
    public function userExistsByUsernameOrEmail($username, $email) {
        $statement = $this->pdo->prepare('
            SELECT id FROM ' . $this->tableName . '
            WHERE username = :username OR email = :email;
        ');
        $statement->bindParam(':username', $username);
        $statement->bindParam(':email', $email);
        $statement->execute();
        return $statement->fetch() !== false;
    }

    /**
     * @param $username
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $passwordSalt
     * @param $passwordHash
     * @return stdClass
     */
    public function addUser($username, $firstname, $lastname, $email, $passwordSalt, $passwordHash) {
        $statement = $this->pdo->prepare('
            INSERT INTO ' . $this->tableName . '
            (username, firstname, lastname, email, passwordSalt, passwordHash)
            VALUES (:username, :firstname, :lastname, :email, :passwordSalt, :passwordHash);
        ');
        $statement->bindParam(':username', $username);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':passwordSalt', $passwordSalt);
        $statement->bindParam(':passwordHash', $passwordHash);
        $statement->execute();

        $user = new stdClass();
        $user->id = $this->pdo->lastInsertId();
        $user->username = $username;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        return $user;
    }

    public function getUser($username) {
        $statement = $this->pdo->prepare('
            SELECT id, username, firstname, lastname, email, passwordSalt, passwordHash
            FROM ' . $this->tableName . '
            WHERE username = :username
            LIMIT 1;
        ');
        $statement->bindParam(':username', $username);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
    }
} 