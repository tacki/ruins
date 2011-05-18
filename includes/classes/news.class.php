<?php
/**
* Simple Newssystem Class
* @author Sebastian Meyer <greatiz@arcor.de>
* @copyright Copyright (C) 2007 Sebastian Meyer
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version SVN: $Id: news.class.php 326 2011-04-19 20:19:34Z tacki $
* @package Ruins
*/

class News
{
    /**
    * Values for data input
    * @var array
    */
    private $_addNews;

    /**
    * Table settings
    * @var array
    */
    private $_newsSettings;

    /**
    * Requested News
    * @var array
    */
    public $newsRes;

    /**
    * Show the news
    * @var string
    */
    private $_showNews;

    /**
    * constructor - load the default values and initialize the attributes
    */
    function __construct()
    {

        //  Initilize variables
        $this->_newsSettings = array();
        $this->_addNews = array();
        $this->newsRes = new QueryTool;
        $this->_showNews = "";
    }

    /**
    * Set the attributes for the news table
    * @param int $times How many news should be listed on the page
    * @param mixed $width width "25%" or "25" [optional]
    * @param string $table Defined ClassType in file.css IMPORTANT spaces between signs "`45~ `34" [optional]
    * @param string $header Defined ClassType in file.css IMPORTANT spaces between signs "`45~ `34" [optional]
    * @param string $body Defined ClassType in file.css IMPORTANT spaces between signs "`45~ `34" [optional]
    */
    public function setNewsAttributes($times,$width=false,$table=false,$header=false,$body=false)
    {
        // Check if times is an int and not negative or 0
        if (is_int($times) && $times>0)
        {
            // if it's ok
            $this->_newsSettings['times'] = $times;
        } else {
            // if it's not
            echo "Anzahl der anzuzeigenden Nachrichten ist keine ganze Zahl > 0";
        }
        if ($width!=false) $this->_newsSettings['width'] = $width;
        if ($header!=false) $this->_newsSettings['header'] = $header;
        if ($body!=false) $this->_newsSettings['body'] = $body;
        if ($table!=false) $this->_newsSettings['table'] = $table;
    } // End setNewsAttributes($time,$width,$table,$header,$body)

    /**
    * Load the data and show the newstable
    */
    public function load()
    {
        $result = $this->newsRes->select("news.id, displayname, title, body")
                                ->from("news")
                                ->join("characters", "characters.id=news.author")
                                ->where("area=1")
                                ->order("date", true)
                                ->exec()
                                ->fetchAll();

        if (count($result) == 0) {
            return false;
        }

        for ($i=0;$i<$this->_newsSettings['times'];$i++)
        {
            // local variable
            $newstable = new Table;
            $newstable->setTabAttributes($this->_newsSettings['width'],$this->_newsSettings['table'],0);
            $newstable->addTabSize(2,2);
            $newstable->addFieldContent(1,1,$result[$i]['title'],"60%",$this->_newsSettings['header'],1,1);
            $newstable->addFieldContent(1,2,"Erstellt am ".date("d.m.Y - H:i",$result[$i]['time'])." von ".$result[$i]['displayname'],"40%",$this->_newsSettings['header'],1,1);
            $newstable->addFieldContent(2,1,$result[$i]['body'],false,$this->_newsSettings['body'],1,2);
            $this->_showNews .= $newstable->load()."`n`n";
        }
        return $this->_showNews;
    }	// End load()
}
?>
