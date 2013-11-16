<?php
abstract class AbstractRepository {

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo) {
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
     * @return mixed
     */
    public abstract function createTable();
} 