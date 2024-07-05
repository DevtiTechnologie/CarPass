<?php
require 'getVehiculeStatus.php'; // Include the GetVehiculeStatus class definition

// Load configuration from config.php
$config = require 'config.php';

// Load vehicle data from vehicleData.php
$vehicleData = require 'vehicleData.php';

// Create an instance of GetVehiculeStatus class
$getVehiculeStatus = new GetVehiculeStatus();

// Get credentials from config
$USERNAME = $config['username'];
$PASSWORD = $config['password'];

// Call getRequestStatus method to get vehicle status and echo the result
echo $getVehiculeStatus->getRequestStatus($USERNAME, $PASSWORD, $vehicleData);
