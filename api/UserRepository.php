<?php

class UserRepository {

    private $pdo;

    private $tableName = 'user';

    /**
     * @param $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;

        $this->createTable();
    }

    /**
     * @return bool
     */
    public function tableExists() {
        $statement = $this->pdo->prepare('SHOW TABLES LIKE :table');
        $statement->bindParam(':table', $this->tableName);
        $statement->execute();
        return $statement->fetch() !== false;
    }

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
                salt VARCHAR(255) NOT NULL,
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
     * @param $salt
     * @param $passwordHash
     * @return array
     */
    public function addUser($username, $firstname, $lastname, $email, $salt, $passwordHash) {
        $statement = $this->pdo->prepare('
            INSERT INTO ' . $this->tableName . '
            (username, firstname, lastname, email, salt, passwordHash)
            VALUES (:username, :firstname, :lastname, :email, :salt, :passwordHash)
        ');
        $statement->bindParam(':username', $username);
        $statement->bindParam(':firstname', $firstname);
        $statement->bindParam(':lastname', $lastname);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':salt', $salt);
        $statement->bindParam(':passwordHash', $passwordHash);
        $statement->execute();

        return array(
            'id' => $this->pdo->lastInsertId(),
            'username' => $username,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email
        );
    }
} 