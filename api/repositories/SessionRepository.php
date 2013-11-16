<?php

include_once 'AbstractRepository.php';

class SessionRepository extends AbstractRepository {

    /**
     * @var string
     */
    protected $tableName = 'session';

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
                expire TIMESTAMP NOT NULL,
                userId INT,
                FOREIGN KEY (userId) REFERENCES user(id),
                sessionToken VARCHAR(255) NOT NULL,
                remember BOOL NOT NULL DEFAULT 0
            ) ENGINE = InnoDB;
        ');
        $statement->execute();
    }

    /**
     * @param $userId
     * @param $secondsToExpire
     * @param $sessionToken
     * @param boolean $remember
     */
    public function addSession($userId, $secondsToExpire, $sessionToken, $remember) {
        $statement = $this->pdo->prepare('
            INSERT INTO ' . $this->tableName . '
            (userId, expire, sessionToken, remember)
            VALUES (:userId, NOW() + INTERVAL :secondsToExpire SECOND, :sessionToken, :remember);
        ');
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':secondsToExpire', $secondsToExpire);
        $statement->bindParam(':sessionToken', $sessionToken);
        $statement->bindParam(':remember', $remember, PDO::PARAM_BOOL);
        $statement->execute();
    }
} 