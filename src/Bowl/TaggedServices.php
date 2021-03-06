<?php

namespace Bowl;

use Bowl\Service\ServiceInterface;

/**
 * Manages tagged services
 *
 * @author Kazuyuki Hayashi <hayashi@valnur.net>
 */
class TaggedServices implements \Iterator, \Countable
{

    /**
     * @var ServiceInterface[]
     */
    private $services = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * Add a service
     *
     * @param ServiceInterface $service
     */
    public function add(ServiceInterface $service)
    {
        $this->services[] = $service;
    }

    /**
     * Returns an array of services
     *
     * @return ServiceInterface[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Return the current element
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->services[$this->position]->get();
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->services[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Count elements of an object
     *
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->services);
    }

}