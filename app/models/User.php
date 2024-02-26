<?php

namespace app\models;

use app\utils\Connection;
use app\utils\Password;
use Exception;

class User
{
    /** @var null|integer */
    protected ?int $id = null;

    /** @var null|string */
    public ?string $login = null;



    /** @var Connection */
    private Connection $connection;




    public function __construct(Connection $connection = null)
    {
        if (!empty($connection)) {
            $this->connection = new Connection();
        }
    }

    /**
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function restoreSession(?int $clientId = null)
    {
        if (empty($clientId)) {
            return;
        }

        $this->setData($this->loadUserById($clientId));
    }

    public function setData($data): self
    {
        return $this;
    }


    public function findUser($login, $password = '')
    {
        if ($password !== '') {
            $password = Password::hash($password);
        }

        $filter = 'login=?' . ($password !== '' ? ' AND pass=?' : '');

        return $this->connection->fetch1($this->connection->select('Users', 'id', $filter, array($login, $password)));
    }

    public function newUser($login, $password, $access = 0)
    {
        if ($this->findUser($login)) {
            throw new Exception('User already exists', 403);
        }

        $password = Password::hash($password);

        $newId = $this->connection->insert('Users', array('login' => $login, 'pass' => $password));

        return $newId;
    }

    private function loadUserById(int $id): ?array
    {
        $qr = $this->connection->fetch1Row($this->connection->select('Users', '*', 'id=?d', array($id)));


        return $qr;
    }
}
