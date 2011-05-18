<?php
/**
 * Traveling Class
 *
 * Class for calculate traveling time und checking waypoint connections
 * @author Sebastian Meyer <greatiz@gmail.com>
 * @copyright Copyright (C) 2009 Sebastian Meyer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Traveling Class
 *
 * Class for calculate traveling time und checking waypoint connections
 * @package Ruins
 */
class traveling {

    /**
     * The Stack
     * @var array
     */
    private $_stack;

    /**
     * Waypoints
     * @var array
     */
    private $_waypoints;

    /**
     * Waypoint connections
     * @var array
     */
    private $_waypoints_con;

    /**
     * Request var
     * @var array
     */
    private $_request;

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
        $this->_request = new DBListing;
        $this->distance = 0;
//		$this->shortest_way = array();
        $this->shortest_way = new StackObject;
    }

//	function traveling(){
//		$stack = new StackObject;
//	}

    /**
     * load all data from waypoints_connection table
     */
     public function load(){
         $this->_request->setTable("waypoints");
         $this->_request->setSelect("id, x, y, z");
         $this->_waypoints = $this->_request->getAll();

         $this->_request->setTable("waypoints_connection");
         $this->_request->setSelect("start, end, difficulty");
         $this->_waypoints_con = $this->_request->getAll();
//	 	var_dump($this->_waypoints_con);

         if(count($this->_waypoints_con)<=0 OR count($this->_waypoints)<=0){
             return false;
         }
     }

    /**
     * check if your location is connected with your destination(target location)
     * @param int $start location where the char actually is
     * @param int $target target to check
    */
    public function check_target($start,$target){

//		global $user;
        $found = false;
          $visited = array();
          $available_waypoints = array();
          $fstelement = 0;
          $this->_stack->add($start);
//	  	array_push($this->_stack, $start);
          if(!in_array($target, $this->_stack->export())){
              while(count($this->_stack->export())>0){
//	  			echo "Anzahl der Elemente im Stack: ".count($this->_stack->export())."<br>";
//	  			echo "--- Schleife beginnt ---<br>";
                 $fstelement = $this->_stack->delFirst();
//				echo "Aktuelle Element: $fstelement <br>";
                 array_push($visited, $fstelement);
                 $available_waypoints = $this->check_connections($fstelement);
//				echo "Im Visitedstack befindet sich: ";
// 				print_r($visited);
// 				echo "<br>Gefunde Verbindungen: ";
// 				print_r($available_waypoints);
// 				echo "<br>";
                 if (!in_array($target, $available_waypoints)) {
// 					echo "Befindet sich der Zielpunkt unter den gefundenen Verbindungen.<br>";
                     for ($i=0;$i<count($available_waypoints);$i++){
// 						echo "Haben wir den aktuellen Punkt schon besucht?<br>";
                         if (!in_array(current($available_waypoints), $visited)){
// 							echo "Nein, Kommt auf den Stack -->".current($available_waypoints)."<br>";
                             $this->_stack->add(current($available_waypoints));
                             next($available_waypoints);
                         }else {
//							echo "Ja, wir haben den Punkt ".current($available_waypoints)." schon besucht<br>";
                             next($available_waypoints);
                         }
                     }
//					echo "Im Stack befinden sich folgende Elemente: ";
// 					print_r($stack1);
// 					echo "<br>";
                 }else if(in_array($target,$available_waypoints)) {
                     $this->_stack->clear();
//					reset($this->_stack);
                    $found = true;
// 					echo "Verbindung gefunden!<br>---------<br>";
// 					break;
                 }
             }
         }
// 		echo "ENDE";
         return $found;

      }

    /**
     * return the available connections from the current location
     * @param int $current_location the location which should be checked
     */
    private function check_connections($current_location){
        $result = array();
//		echo "CHECK_CONNECTIONS Ausgangspunkt: ".$current_location."<br>";
//		var_dump($this->_waypoints_con);
        for ($i=0;$i<count($this->_waypoints_con);$i++) {
//			echo "Momentane Ziel($i): ".$this->_waypoints_con[$i]["end"]."<br>";
            if($this->_waypoints_con[$i]["start"]==$current_location){
//				echo "Punkt: ".$this->_waypoints_con[$i]["start"]." Ziel: ".$this->_waypoints_con[$i]["end"]."<br>";
                array_push($result, $this->_waypoints_con[$i]["end"]);
            }else if($this->_waypoints_con[$i]["end"]==$current_location){
//				echo "Punkt: ".$this->_waypoints_con[$i]["end"]." Ziel: ".$this->_waypoints_con[$i]["start"]."<br>";
                array_push($result, $this->_waypoints_con[$i]["start"]);
            }
        }
        return $result;
    }

    /**
     * calculate the distance between two waypoints
     * @param int $start start location
     * @param int $end target location
     */
    private function calculate_dist($start,$end){
        $site_a = abs($this->_waypoints[$start-1]['x']-$this->_waypoints[$end-1]['x']);
        $site_b = abs($this->_waypoints[$start-1]['y']-$this->_waypoints[$end-1]['y']);
        $site_c = floor(sqrt(pow($site_a,2)+pow($site_b,2)));
//		$site_c = floor((pow($site_a,2)+pow($site_b,2))/60);
//		echo $site_c."<br>";
        return $site_c;
    }


    /**
     * find the shortest way to the target location
     * implemented an algorithm like dijkstra-algorithm
     * @param int $target destination
     */
     // MUSS BERARBEITET WERDEN!!!
    public function find_way($start, $target){
         $act_point = $start;
         $temp_distance = 0;
         $visited = array();
// 		array_push($this->shortest_way, $target);
        $this->shortest_way->add($start);
        array_push($visited,$start);
// 		echo "---- Algorithmus BEGINNT ----<br>";
         while ($act_point!=$target){
             $available_waypoints = $this->check_connections($act_point);
// 			echo "--> Aktuelle Punkt ist: ".$act_point."<br>";
// 			echo "Verfügbare Punkte sind: <br>";
// 			print_r($available_waypoints);
             // calculate the distance of any point to see which is closer to the target
             // and choose the shortest
             for ($i=0; $i<count($available_waypoints);$i++){
// 				echo "Der zu untersuchende Punkt ist: ".current($available_waypoints)."<br>";
                 if (current($available_waypoints)!=$target && !in_array(current($available_waypoints),$visited) && $act_point!=$target){
// 					echo "Seine Distanz zum Ziel beträgt: ".$this->calculate_dist($target,current($available_waypoints))."<br>";
                     if ($this->calculate_dist($target,current($available_waypoints))<$temp_distance && $temp_distance>0){
                         $act_point = (int) current($available_waypoints);
                         $temp_distance = $this->calculate_dist($target,current($available_waypoints));
// 						echo "Dieser Punkt ist momentan der Favorit<br>";
                     }else if($temp_distance==0){
                         $act_point = (int) current($available_waypoints);
                         $temp_distance = $this->calculate_dist($target,current($available_waypoints));
// 						echo "Dieser Punkt ist momentan der Favorit<br>";
                     }else{
// 						echo "Dieser Punkt ist vorläufig raus.<br>";
                     }
                 }
                 // if the point is found
                 if (current($available_waypoints)==$target){
                     $act_point = (int) current($available_waypoints);
// 					echo "Punkt gefunden!!!!<br><br>";
                 }
                 next($available_waypoints);
             }
             // reset temp_distance
             $temp_distance = 0;
             // if we find a new point, we can add it to the stack and go get the next one,
             // but if it's the same, we have to go a step back
             if ($act_point!=$this->shortest_way->getLast()){
// 				echo "Weiter geht's!<br>";
                 $this->shortest_way->add($act_point);
                 array_push($visited,$act_point);
             }else {
// 				echo "Wir müssen wieder einen Schritt zurück!<br>";
                 $this->shortest_way->delLast();
                 if ($this->shortest_way->count()>0){
                     $act_point = $this->shortest_way->getLast();
                 }
             }
         }
         // Calculating the distance
         $temp_distance = $this->shortest_way->export();
         for ($i=1;$i<count($temp_distance);$i++){
// 			echo $i."<br>";
             $this->distance += $this->calculate_dist($temp_distance[$i-1],$temp_distance[$i]);
// 			echo $i."<br>";
// 			echo "Distanz: ".$this->distance."<br>";
         }
         return $this->distance;
    }
}
?>
