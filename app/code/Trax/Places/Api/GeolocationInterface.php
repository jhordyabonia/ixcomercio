<?php

namespace Trax\Places\Api;

interface GeolocationInterface
{
    /**
     * GET for Post api
     * @param string $value
     * @return array
     */
 
    public function getRegions();

    /**
     * GET Cities
     * @param string $value
     * @return array
     */
 
    public function getCities();


    /**
     * GET Zones
     * @param string $value
     * @return array
     */
 
    public function getZones();
}