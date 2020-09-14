<?php


namespace PhpOne\CanalPHP;

/**
 * Class ClientIdentity
 * @package PhpOne\CanalPHP
 *
 * 客户端标识
 */
class ClientIdentity
{
    /**
     * @param mixed $filter
     */
    public function setFilter($filter): void
    {
        $this->filter = $filter;
    }
    private $destination;
    private $clientId;
    private $filter;

    public function __construct(string $destination, int $clientId)
    {
        $this->destination = $destination;
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

}