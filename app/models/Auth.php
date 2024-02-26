<?php

namespace app\models;

use app\lib\Session;
use app\utils\Connection;

final class Auth
{

    /** @var Session */
    private Session $session;

    /** @var Connection */
    private Connection $connection;

    /** @var User */
    private $user;

    public function __construct()
    {
        $this->session = new Session();

        $this->connection = new Connection();
        $this->user = new User($this->connection);

        $this->user->restoreSession($this->session->get('clientId'));
    }

    public function getClientId(): ?int
    {
        return $this->user->getId();
    }


    public function login($login, $password)
    {
        $user = $this->user->findUser($login, $password);

        $this->session->set('clientId', $user->getClientId());
    }

    public function signout()
    {
        $this->session->delete('clientId');
    }
}
