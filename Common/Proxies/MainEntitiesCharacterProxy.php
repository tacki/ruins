<?php

namespace Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class MainEntitiesCharacterProxy extends \Main\Entities\Character implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    private function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    
    public function initLayers()
    {
        $this->__load();
        return parent::initLayers();
    }

    public function endLayers()
    {
        $this->__load();
        return parent::endLayers();
    }

    public function getSpeed()
    {
        $this->__load();
        return parent::getSpeed();
    }

    public function __get($name)
    {
        $this->__load();
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $this->__load();
        return parent::__set($name, $value);
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'user', 'name', 'displayname', 'level', 'healthpoints', 'lifepoints', 'strength', 'dexterity', 'constitution', 'wisdom', 'intelligence', 'charisma', 'money', 'groups', 'current_nav', 'allowednavs', 'allowednavs_cache', 'template', 'type', 'loggedin', 'race', 'profession', 'sex', 'lastpagehit', 'debugloglevel');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}