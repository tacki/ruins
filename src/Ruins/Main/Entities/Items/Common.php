<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities\Items;
use Ruins\Main\Entities\Item;

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