<?php
namespace Main\Controller;
use Main\Repositories\Repository,
    Common\Controller\Error;

class Controller
{
    /**
     * (INTERN) Primary Repository Name
     * @var string
     */
    const PRIMARY = "primary";

    /**
     * Repository Holder
     * @var array
     */
    private $_repositories = array();

    /**
     * Retrieve Repository
     * @param string $name
     * @return Main\Repositories\Repository:
     */
    public function getRepository($name=self::PRIMARY)
    {
        return $this->_repositories[$name];
    }

    /**
     * Set primary Repository for this Controller
     * @param Main\Repositories\Repository $repository
     */
    public function setRepository(Repository $repository)
    {
        $this->_repositories[self::PRIMARY] = $repository;
    }

    /**
     * Add another Repository
     * @param Main\Repositories\Repository $repository
     * @param string $name
     * @throws Error
     */
    public function addRepository(Repository $repository, $name)
    {
        if ($name == self::PRIMARY) throw new Error("Name '". self::PRIMARY . "' is now allowed!");

        $this->_repositories[$name] = $repository;
    }
}