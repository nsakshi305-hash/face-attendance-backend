<?php

namespace App\Services;

class GeoFenceService
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + 
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        
        return $kilometers * 1000;
    }
    
    public function isWithinGeoFence($userLat, $userLng, $fenceId = null)
    {
        if ($fenceId) {
            $stmt = $this->db->prepare("
                SELECT * FROM geo_fences WHERE id = ? AND is_active = TRUE
            ");
            $stmt->execute([$fenceId]);
            $fence = $stmt->fetch();
            
            if ($fence) {
                $distance = $this->calculateDistance(
                    $userLat, $userLng, 
                    $fence['latitude'], $fence['longitude']
                );
                return $distance <= $fence['radius'];
            }
            return false;
        }
        
        $stmt = $this->db->query("
            SELECT * FROM geo_fences WHERE is_active = TRUE
        ");
        $fences = $stmt->fetchAll();
        
        foreach ($fences as $fence) {
            $distance = $this->calculateDistance(
                $userLat, $userLng, 
                $fence['latitude'], $fence['longitude']
            );
            if ($distance <= $fence['radius']) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getAllGeoFences()
    {
        $stmt = $this->db->query("
            SELECT * FROM geo_fences WHERE is_active = TRUE
        ");
        return $stmt->fetchAll();
    }
}
?>