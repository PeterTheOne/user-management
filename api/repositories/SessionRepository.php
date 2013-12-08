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

    /**
     *
     */
    public function removeExpiredSessions() {
        $statement = $this->pdo->prepare('
            DELETE FROM ' . $this->tableName . '
            WHERE expire < NOW();
        ');
        $statement->execute();
    }

    /**
     * @param $sessionToken
     * @return mixed
     */
    public function getNotExpiredSession($sessionToken) {
        $statement = $this->pdo->prepare('
            SELECT created, expire, userId, sessionToken, remember
            FROM ' . $this->tableName . '
            WHERE sessionToken = :sessionToken AND expire >= NOW()
            LIMIT 1;
        ');
        $statement->bindParam(':sessionToken', $sessionToken);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
    }

    /**
     * @param $sessionToken
     * @param $secondsToExpire
     */
    public function updateSessionExpire($sessionToken, $secondsToExpire) {
        $statement = $this->pdo->prepare('
            UPDATE ' . $this->tableName . '
            SET expire = NOW() + INTERVAL :secondsToExpire SECOND
            WHERE sessionToken = :sessionToken;
        ');
        $statement->bindParam(':sessionToken', $sessionToken);
        $statement->bindParam(':secondsToExpire', $secondsToExpire);
        $statement->execute();
    }
} 