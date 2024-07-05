<?php
require 'vendor/autoload.php'; // Autoload the Composer dependencies

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Define the GetRequestGuid class
class GetRequestGuid
{
    private $client; // HTTP client for sending requests
    private $username; // API username
    private $password; // API password
    private $apiEndpoint; // API endpoint URL

    // Constructor to initialize the class with username and password
    public function __construct($username, $password)
    {
        $this->client = new Client(); // Initialize Guzzle HTTP client
        $this->username = $username;
        $this->password = $password;
        $this->apiEndpoint = 'https://ws-professionals.car-pass.be/api/observation'; // Set the API endpoint
    }

    // Method to get the request GUID key by sending vehicle data to the API
    public function getRequestGuidKey($vehicleData)
    {
        try {
            // Send a POST request to the API endpoint
            $response = $this->client->request('POST', $this->apiEndpoint, [
                'headers' => [
                    'Content-Type' => 'application/json', // Set the content type to JSON
                    'Authorization' => 'Basic ' . base64_encode("$this->username:$this->password"), // Add basic auth header
                    'client-software-builder' => 'Produpress SA',
                    'client-software-name' => 'GlobalCube Manager',
                    'client-software-version' => '3.0.0',
                ],
                'json' => $vehicleData, // Attach the vehicle data as JSON
            ]);

            $responseBody = $response->getBody()->getContents(); // Get the response body
            $data = json_decode($responseBody, true); // Decode the JSON response

            // Return the request GUID in a success message
            return json_encode([
                'status' => 'success',
                'requestGuid' => "$data[requestGuid]"
            ]);
        } catch (RequestException $e) {
            return $this->handleException($e); // Handle any request exceptions
        }
    }

    // Private method to handle exceptions
    private function handleException(RequestException $e)
    {
        if ($e->hasResponse()) {
            // If the exception has a response, get the response body
            $response = $e->getResponse();
            $responseBody = $response->getBody()->getContents();
            $errorData = json_decode($responseBody, true); // Decode the JSON error data
            return $errorData; // Return the error data
        } else {
            return ['error' => $e->getMessage()]; // If no response, return the exception message
        }
    }
}
