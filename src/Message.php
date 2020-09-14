<?php


namespace PhpOne\CanalPHP;


class Message
{
    protected $id = 0;
    protected $entries;

    public function __construct()
    {
        $this->entries = new \ArrayIterator([]);
    }

    public function count()
    {
        return $this->entries->count();
    }

    public function addEntry($entry)
    {
        $this->entries->append($entry);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param mixed $entries
     */
    public function setEntries($entries): void
    {
        $this->entries = $entries;
    }




}