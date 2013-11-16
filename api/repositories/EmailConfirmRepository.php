<?php

include_once 'AbstractRepository.php';

class EmailConfirmRepository extends AbstractRepository {

    /**
     * @var string
     */
    protected $tableName = 'emailConfirm';

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
                userId INT,
                FOREIGN KEY (userId) REFERENCES user(id),
                email VARCHAR(255) NOT NULL,
                confirmToken VARCHAR(255) NOT NULL UNIQUE
            ) ENGINE = InnoDB;
        ');
        $statement->execute();
    }

    public function addEmailConfirm($userId, $email, $confirmToken) {
        $statement = $this->pdo->prepare('
            INSERT INTO ' . $this->tableName . '
            (userId, email, confirmToken)
            VALUES (:userId, :email, :confirmToken);
        ');
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':confirmToken', $confirmToken);
        $statement->execute();
    }
} 