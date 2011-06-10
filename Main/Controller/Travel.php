<?php
/**
 * Traveling Class
 *
 * Class for calculate traveling time und checking waypoint connections
 * @author Sebastian Meyer <greatiz@gmail.com>
 * @copyright Copyright (C) 2009 Sebastian Meyer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Controller;
use Main\Entities\Waypoint,
    StackObject;

/**
 * Traveling Class
 *
 * Class for calculate traveling time und checking waypoint connections
 * @package Ruins
 */
class Travel {

    /**
     * The Stack
     * @var array
     */
    private $_stack;

    /**
     * Distance
     * @var int
     */
    public $distance;

    /**
     * Shortest Way
     * @var array
     */
    public $shortest_way;

    /**
     * constructor - load the default values and initialize the attributes
     */
    function __construct(){
        $this->_stack = new StackObject;
        $this->distance = 0;
        $this->shortest_way = new StackObject;
    }

    /**
     * check if your location is connected with your destination(target location)
     * @param Waypoint $start location where the char actually is
     * @param Waypoint $target target to check
    */
    public function isConnected(Waypoint $start, Waypoint $target)
    {
        $found = false;
        $visited = array();
        $available_waypoints = array();
        $fstelement = 0;

        $this->_stack->add($start);

        if(!in_array($target, $this->_stack->export())) {
            while(count($this->_stack->export()) > 0) {
                // Move first Element from Stack to $visited
                $fstelement = $this->_stack->delFirst();
                array_push($visited, $fstelement);

                // Check for available Connections
                $available_waypoints = $this->getConnections($fstelement);

                if (!in_array($target, $available_waypoints)) {
                    // Target is not in $available_waypoints
                    // Check the rest of the available Waypoints
                    for ($i=0; $i<count($available_waypoints); $i++){
                        // Check if we already visited this Waypoint
                        if (!in_array(current($available_waypoints), $visited)){
                             // Target is new - Add to the Stack
                            $this->_stack->add(current($available_waypoints));
                         }
                     }
                } elseif (in_array($target, $available_waypoints)) {
                    // Target found, clear Stack (will also break while-loop)
                    $this->_stack->clear();
                    $found = true;
                }
            }
        }

        return $found;
    }

    /**
     * Reset all Results
     */
    private function reset()
    {
        $this->_stack = new StackObject;
        $this->distance = 0;
        $this->shortest_way = new StackObject;
    }

    /**
     * return the available connections from the current location
     * @param Waypoint $current_location the location which should be checked
     */
    private function getConnections(Waypoint $current_location)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("waypoint")
                        ->from("Main:WaypointConnection", "connection")
                        ->from("Main:Waypoint", "waypoint")
                        ->where($qb->expr()->andx(
                            $qb->expr()->eq("connection.start", "?1"),
                            $qb->expr()->eq("connection.end", "waypoint")
                        ))
                        ->orWhere($qb->expr()->andx(
                            $qb->expr()->eq("connection.end", "?1"),
                            $qb->expr()->eq("connection.start", "waypoint")
                        ))
                        ->setParameter(1, $current_location)
                        ->getQuery()->getResult();

        return $result;
    }

    /**
     * calculate the distance between two waypoints
     * @param Waypoint $start start location
     * @param Waypoint $end target location
     */
    private function calcDistance(Waypoint $start, Waypoint $end){
        $site_a = abs($start->x - $end->x);
        $site_b = abs($start->y - $end->y);
        $site_c = floor(sqrt(pow($site_a, 2) + pow($site_b, 2)));

        return $site_c;
    }


    /**
     * find the shortest way to the target location
     * implemented an algorithm like dijkstra-algorithm
     * @param int $target destination
     */
     // MUSS BERARBEITET WERDEN!!!
    public function findWay(Waypoint $start, Waypoint $target)
    {
        // Reset previous Results
        $this->reset();

        $act_point = $start;
        $temp_distance = 0;
        $visited = array();
        $this->shortest_way->add($start);
        array_push($visited,$start);
        //echo "---- Algorithmus BEGINNT ----<br>";
        while ($act_point!=$target){
            $available_waypoints = $this->getConnections($act_point);
            //echo "--> Aktuelle Punkt ist: ".$act_point->name."<br>";
            //echo "Verfügbare Punkte sind: <br>";
            //var_dump($available_waypoints);
            // calculate the distance of any point to see which is closer to the target
            // and choose the shortest
            for ($i=0; $i<count($available_waypoints);$i++){
                //echo "Der zu untersuchende Punkt ist: ".current($available_waypoints)->name."<br>";
                if (current($available_waypoints)!=$target && !in_array(current($available_waypoints),$visited) && $act_point!=$target){
                    //echo "Seine Distanz zum Ziel beträgt: ".$this->calcDistance($target,current($available_waypoints))."<br>";
                    if ($this->calcDistance($target,current($available_waypoints))<$temp_distance && $temp_distance>0){
                        $act_point = current($available_waypoints);
                        $temp_distance = $this->calcDistance($target,current($available_waypoints));
                        //echo "Dieser Punkt ist momentan der Favorit<br>";
                    }else if($temp_distance==0){
                        $act_point = current($available_waypoints);
                        $temp_distance = $this->calcDistance($target,current($available_waypoints));
                        //echo "Dieser Punkt ist momentan der Favorit<br>";
                    }else{
                        //echo "Dieser Punkt ist vorläufig raus.<br>";
                    }
                }
                // if the point is found
                if (current($available_waypoints)==$target){
                    $act_point = current($available_waypoints);
                    //echo "Punkt gefunden!!!!<br><br>";
                }
                next($available_waypoints);
            }
            // reset temp_distance
            $temp_distance = 0;
            // if we find a new point, we can add it to the stack and go get the next one,
            // but if it's the same, we have to go a step back
            if ($act_point!=$this->shortest_way->getLast()){
                //echo "Weiter geht's!<br>";
                $this->shortest_way->add($act_point);
                array_push($visited,$act_point);
            } else {
                //echo "Wir müssen wieder einen Schritt zurück!<br>";
                $this->shortest_way->delLast();
                if ($this->shortest_way->count()>0){
                    $act_point = $this->shortest_way->getLast();
                }
            }
        }
        // Calculating the distance
        $temp_distance = $this->shortest_way->export();
        for ($i=1;$i<count($temp_distance);$i++){
            //echo $i."<br>";
            $this->distance += $this->calcDistance($temp_distance[$i-1],$temp_distance[$i]);
            //echo $i."<br>";
            //echo "Distanz: ".$this->distance."<br>";
        }
        return $this->distance;
    }
}
?>
