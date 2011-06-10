<?php
/**
 * Namespaces
 */
namespace Main\Entities\Items;
use Main\Entities\Item;

/**
 * @Entity
 * @Table(name="items__common")
 */
class Common extends Item
{
    public function __construct()
    {
        parent::__construct();
    }
}