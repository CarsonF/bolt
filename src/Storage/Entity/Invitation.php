<?php

namespace Bolt\Storage\Entity;

/**
 * Entity for Invitations.
 */
class Invitation
{
    /** @var string */
    protected $token;
    /** @var int */
    protected $ownerId;
    /** @var \DateTime */
    protected $expiration;
    /** @var string[] */
    protected $roles = [];

    /** @var string|null */
    protected $username;
    /** @var string|null */
    protected $displayName;
    /** @var string|null */
    protected $email;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return Invitation
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param int $ownerId
     *
     * @return Invitation
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param \DateTime $expiration
     *
     * @return Invitation
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     *
     * @return Invitation
     */
    public function setRoles(array $roles)
    {
        $this->roles = array_values(array_unique($roles));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     *
     * @return Invitation
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     *
     * @return Invitation
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return Invitation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }
}
