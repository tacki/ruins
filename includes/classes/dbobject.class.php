<?php
/**
 * Database Object Class
 *
 * Class to load/save Data from a corresponding Databse. Classes who have their own DB-Table need this.
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: dbobject.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Database Object Class
 *
 * Class to load/save Data from a corresponding Databse. Classes who have their own DB-Table need this.
 * @package Ruins
 */
class DBObject extends BaseObject
{

    /**
     * Loaded Index ID
     * Default: 0
     * @var mixed
     */
    private $id;

    /**
     * Flag to set, if this is a self-created object
     * Default: false
     * @var bool
     */
    private $created;

    /**
     * Name of the associated Table
     * Default: Classname (+s)
     * @var string
     */
    protected $tablename;

    /**
     * Primary Index
     * Default: id
     * @var int
     */
    protected $primaryindex;

    /**
     * Database Object
     * Default: id
     * @var MDB2
     */
    protected $database;

    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings = false)
    {
        // From dbconnect.cfg.php
        global $dbconnect;

        // Call Constructor of the Parent-Class
        parent::__construct($settings);

        // Set default ID
        $this->id = 0;

        // Set default created Flag
        $this->created = false;

        // Class Settings
        if (isset($settings['tablename'])) {
            $this->tablename = $settings['tablename'];
        } else {
            // Database-Tables have to be lowercase CLASS_NAME + s
            // Ignore the additional 's' if the Class-Name already
            // ends with 's'
            if(substr(get_class($this), -1, 1) == "s") {
                $this->tablename = strtolower(get_class($this));
            } else {
                $this->tablename = strtolower(get_class($this)) . "s";
            }
        }

        if (isset($settings['primaryindex'])) {
            $this->primaryindex = $settings['primaryindex'];
        } else {
            $this->primaryindex = "id";
        }

        // Enforce valid properties for Database-Objects
        $this->validproperty = true;

        // Use the global Database-Connection
        $this->database = getDBInstance();
    }

    /**
     * Sleep Function - called by serialize()
     * Clean up Object and disconnect from the Database
     * @return bool Array of properties to serialize
     */
    public function __sleep()
    {
        // Disconnect from the Database
        //$this->database->disconnect();
        // empty the database-property
        //unset($this->database);

        // now serialize everything in this class...
        $sleepvars = array_keys( (array)$this );

        return $sleepvars;
    }

    /**
     * Wakeup Function - called by unserialize()
     * Reconnect to Database and rearrange temporary values
     */
    public function __wakeup()
    {
        // Connect to the Database
        //$this->database = getDBInstance();

        //if (PEAR::isError($this->database)) {
            //throw new Error("Can't connect to Database after wakeup (". $result->getMessage() .")");
        //}
    }

    /**
     * Create a blank new object
     * @param integer $id id of the entry to load
     * @return bool true if successful, else false
     */
    public function create()
    {
        if (!$this->isloaded) {
            // we need a loaded object before we can set anything
            $this->isloaded = true;
            // turn off property-checking while creating
            $this->validproperty = false;

            // get table-data from database
            $this->database->loadModule('Manager');
            $tablefields = $this->database->listTableFields($this->tablename);

            // write default values to each entry
            /* no longer needed?
            foreach ($tablefields as $value) {
                $this->$value = "";
            }*/

            // get Sequence list
            if (!$sequencelist = SessionStore::readCache("sequencelist")) {
                $sequencelist = $this->database->listSequences();
                SessionStore::writeCache("sequencelist", $sequencelist);
            }

            $sequencename = $this->tablename . "_" . $this->primaryindex;

            if (in_array($sequencename, $sequencelist)) {
                // Our Sequence exists
                $primaryindexname 	= $this->primaryindex;
                $newid 				= $this->database->nextID($sequencename);

                if (PEAR::isError($newid)) {
                    throw new Error("Can't get next Sequence ID (". $newid->getUserInfo() .")", $newid->code);
                }

                // assign Sequence to new Object
                $this->id 					= $newid;
                $this->$primaryindexname 	= $newid;
            }


            // set created-flag to tell save()
            // to use INSERT INTO instead of UPDATE
            $this->created = true;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes the DB-Entry and unloads the Object
     * @return bool true if successful, else false
     */
    public function delete()
    {
        if ($this->isloaded) {
            // Create new QueryTool Object
            $dbqt = new QueryTool();

            $result = $dbqt	->deletefrom($this->tablename)
                            ->where($this->primaryindex."=".$dbqt->quote($this->id))
                            ->exec();

            if (PEAR::isError($result)) {
                throw new Error("Error while deleting the Database Object (". $result->getMessage() .")");
            }

            $this->unload();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Load the data from the Database
     * @param integer $id id of the entry to load
     * @param array $fields custom fields to load
     * @return bool true if successful, else false
     */
    public function load($id, $fields=false)
    {
        // PreLoad-Modules
        $this->mod_preload();

        if ($id) {

            // Create new QueryTool Object
            $dbqt = new QueryTool();

            // Set Table (prefix is already set)
            $dbqt->table($this->tablename);

            if (is_array($fields)) {
                // Select id and fields named in the array
                $dbqt->select($this->primaryindex);
                foreach ($fields as $fieldname) {
                    $dbqt->select($fieldname);
                }
            } elseif (is_string($fields)) {
                // Select one Field
                $dbqt->select($fieldname);
            } else {
                // Select All
                $dbqt->select("*");
            }

            // Load where Primaryindex = given $id
            $dbqt->where($this->primaryindex."=".$dbqt->quote($id));

            // fetch Result (we use our DB-Object here, to get Error-Messages
            // Bugfix (fixes SELECT ""-Problem)
            $query = str_replace("SELECT \"\"", "SELECT *", (string)$dbqt);

            $result = $dbqt->exec($query);

            if (PEAR::isError($result)) {
                throw new Error("Error while loading the Database Object (". $result->getUserInfo() .")", $result->code);
            }

            // fetch result and free query
            $this->properties = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
            $result->free();

            // the query returns no result
            if (!is_array($this->properties)) {
                $this->properties = array();
                return false;
            }

            // stripslahes all + unserialize where needed
            foreach (array_keys($this->properties) as $key) {
                $this->properties[$key] = stripslashes($this->properties[$key]);
                if (is_serialized($this->properties[$key])) {
                    $this->properties[$key] = unserialize($this->properties[$key]);

                    // convert arrays into BaseObjects
                    /*
                    if (is_array($this->properties[$key])) {
                        $temparray = $this->properties[$key];
                        $this->properties[$key] = new BaseObject();
                        $this->properties[$key]->load($temparray);
                    } */

                }

                // Bugfix PostgreSQL
                // PostgreSQL returns f for false and t for true from boolean-fields
                // FIXME: Fieldtypecheck? (boolean only)
                if ($this->properties[$key] === 't') {
                    $this->properties[$key] = 1;
                } elseif ($this->properties[$key] === 'f') {
                    $this->properties[$key] = 0;
                }
            }

            // Set the loaded id-property
            $this->id = $id;
        }

        // Set isloaded-flag
        $this->isloaded = true;

        // Load-Modules
        $this->mod_postload();

        // Create Properties Snapshot (needed to detect changes)
        $this->createSnapshot();

        return true;
    }

    /**
     * Unload the data and clean the object
     */
    public function unload()
    {
        // unload the parent too
        parent::unload();
        $this->id = 0;

        $this->isloaded = false;
    }

    /**
     * Save current data to the Database
     * @param bool $all Force to save all Entries
     * @return mixed true or insertid if successful, else false
     */
    public function save($all=false)
    {
        if ($this->isloaded) {
            // Call Save-Mod
            $this->mod_presave();

            if ($this->created || $all) {
                // Everything has changed if this is a newly created Object
                $changed_fields = $this->properties;
            } else {
                $changed_fields = $this->getChangedProperties();
            }

            // Disable all Modules (f.e. Managers or the RaceModule)
            // Managed Modules are always saved to Database
            foreach ($this->properties as $key=>$value) {
                if (is_object($value) && ModuleSystem::isModule($value)) {
                    ModuleSystem::disableModule($value);
                    $changed_fields[$key] = $value;
                    continue;
                }
            }

            if ($changed_fields && count($changed_fields)) {
                $dbqt = new QueryTool;

                // serialize everything below this
                foreach ($changed_fields as $key=>$value) {
                    if ((is_object($value) || is_array($value))) {

                        // Call Subobject-Save Method
                        if (is_object($value) && method_exists($value, "save")) {
                            $value->save();
                        }
                        // Call Subobject-Cleanup Method
                        if (is_object($value) && method_exists($value, "cleanup")) {
                            $value->cleanup();
                        }

                        $changed_fields[$key] = addslashes(serialize($value));
                    } else {
                        $changed_fields[$key] = addslashes($value);
                    }
                }

                // Write to the Database
                if ($this->created) {
                    // this is a newly created Object (INSERT INTO)
                    $result = $dbqt	->insertinto($this->tablename)
                                    ->data($changed_fields)
                                    ->exec();

                    if (PEAR::isError($result)) {
                        throw new Error("Error while saving the created Database Object (". $result->getUserInfo() .")", $result->code);
                    }

                    // unset created-flag, so we don't insert if we save two times
                    $this->created = false;

                    // create a new snapshot
                    $this->createSnapshot();

                    // set and return Index ID;
                    $this->{$this->primaryindex} = $this->database->lastinsertid();
                    return $this->{$this->primaryindex};

                } else {
                    // this Object was loaded from the Database (UPDATE)
                    $result = $dbqt	->update($this->tablename)
                                    ->data($changed_fields)
                                    ->where($this->primaryindex."=".$dbqt->quote($this->id))
                                    ->exec();

                    if (PEAR::isError($result)) {
                        throw new Error("Error while saving the Database Object (". $result->getUserInfo() .")", $result->code);
                    }

                    // create a new snapshot
                    $this->createSnapshot();

                }

                // We saved, so we can clear $this->properties_modified
                $this->properties_modified = array();

            } else {
                // Call Module postsave
                $this->mod_postsave();

                return false;
            }

        } else {
            // Save-Modules
            $this->mod_presave();
        }

        // Call Module postsave
        $this->mod_postsave();

        return true;
    }

}
?>
