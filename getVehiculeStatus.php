<?php
require 'vendor/autoload.php'; // Autoload the Composer dependencies
require 'getRequestGuid.php'; // Include the GetRequestGuid class

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Define the GetVehiculeStatus class
class GetVehiculeStatus
{
    private $client; // HTTP client for sending requests

    // Constructor to initialize the class
    public function __construct()
    {
        $this->client = new Client(); // Initialize Guzzle HTTP client
    }

    // Method to get the request status
    public function getRequestStatus($USERNAME, $PASSWORD, $vehicleData)
    {
        // Create an instance of GetRequestGuid class
        $getRequestGuid = new GetRequestGuid($USERNAME, $PASSWORD);

        // Get the request GUID key by sending vehicle data to the API
        $response = $getRequestGuid->getRequestGuidKey($vehicleData);

        // If the response is a JSON string, decode it
        if (is_string($response)) {
            $response = json_decode($response, true); // Decode the JSON response
        }
        // Check if 'status' key exists in the response
        if (isset($response['status']) && $response['status'] == 'success') {
            // If the response is successful, get the request GUID
            $requestGuid = $response['requestGuid'];
        } else {
            // If the response is not successful, print the response and stop execution
            echo json_encode($response); // Convert the array to JSON string for printing
            die();
        }

        try {
            // Send a GET request to the API to get the vehicle status
            $response = $this->client->request('GET', "https://ws-professionals.car-pass.be/api/observation/{$requestGuid}/status/vhr?language=FR", [
                'headers' => [
                    'Content-Type' => 'application/json', // Set the content type to JSON
                    'Authorization' => 'Basic ' . base64_encode("$USERNAME:$PASSWORD"), // Add basic auth header
                    'client-software-builder' => 'Produpress SA',
                    'client-software-name' => 'GlobalCube Manager',
                    'client-software-version' => '3.0.0',
                ],
            ]);

            $responseBody = $response->getBody()->getContents(); // Get the response body
            $data = json_decode($responseBody, true); // Decode the JSON response

            // Return the URL with the request GUID in a valid status message
            echo json_encode([
                'status' => 'valid',
                'url' => "https://public.car-pass.be/vhr/{$requestGuid}"
            ]);
        } catch (RequestException $e) {
            // Handle any request exceptions
            echo $this->handleException($e);
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

            if (json_last_error() === JSON_ERROR_NONE && isset($errorData['status']) && isset($errorData['message'])) {
                // If the error data is valid JSON and contains status and message, return them
                echo json_encode([
                    'status' => $errorData['status'],
                    'message' => $errorData['message']
                ]);
            } else {
                // If the error data is not valid JSON, return the status code and a default message
                echo json_encode([
                    'status' => $response->getStatusCode(),
                    'message' => 'No valid'
                ]);
            }
        } else {
            // If no response is received, return an error message
            echo json_encode([
                'status' => 'error',
                'message' => 'No response received'
            ]);
        }
    }
}
