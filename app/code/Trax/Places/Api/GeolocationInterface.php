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
     * GET Regions
     * @param string $value
     * @return array
     */
 
    public function getCities();
}