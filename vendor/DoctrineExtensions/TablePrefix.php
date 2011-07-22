<?php
/**
 * Table Prefix Handler from Doctrine2 Cookbook
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006-2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace DoctrineExtensions;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Table Prefix Handler from Doctrine2 Cookbook
 * @package Ruins
 */
class TablePrefix
{
    protected $prefix = '';

    /**
     * Enable Table Prefix
     * @param string $prefix Table Prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = (string) $prefix;
    }

    /**
     * Event Handler
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $mappedTableName = "";
        $classMetadata = $eventArgs->getClassMetadata();
        $classMetadata->setTableName($this->prefix . $classMetadata->getTableName());

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {
                $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
            }
        }
    }

}
?>