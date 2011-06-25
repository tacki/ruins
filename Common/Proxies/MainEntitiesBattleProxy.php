<?php

namespace Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class MainEntitiesBattleProxy extends \Main\Entities\Battle implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }
    
    
    public function isMember(\Main\Entities\Character $character)
    {
        $this->__load();
        return parent::isMember($character);
    }

    public function getMember(\Main\Entities\Character $character)
    {
        $this->__load();
        return parent::getMember($character);
    }

    public function addMember(\Main\Entities\Character $character, $side)
    {
        $this->__load();
        return parent::addMember($character, $side);
    }

    public function removeMember(\Main\Entities\Character $character)
    {
        $this->__load();
        return parent::removeMember($character);
    }

    public function addMessage($message)
    {
        $this->__load();
        return parent::addMessage($message);
    }

    public function clearMessages()
    {
        $this->__load();
        return parent::clearMessages();
    }

    public function addAction(\Main\Entities\Character $character, $target, \Main\Entities\Skill $skill)
    {
        $this->__load();
        return parent::addAction($character, $target, $skill);
    }

    public function setActionDone(\Main\Entities\Character $character)
    {
        $this->__load();
        return parent::setActionDone($character);
    }

    public function hasActionDone(\Main\Entities\Character $character)
    {
        $this->__load();
        return parent::hasActionDone($character);
    }

    public function getActionDoneList()
    {
        $this->__load();
        return parent::getActionDoneList();
    }

    public function getActionNeededList()
    {
        $this->__load();
        return parent::getActionNeededList();
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
        return array('__isInitialized__', 'id', 'initiator', 'actions', 'members', 'messages', 'timer', 'round', 'active', 'battlemembersnapshot');
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