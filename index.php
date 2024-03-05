<?php

// Function to search for longitude and latitude using OpenStreetMap API
function searchLocation($locationName) {
    // URL to the OpenStreetMap API
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($locationName) . "&format=json";

    // Set up headers with User-Agent
    $opts = [
        "http" => [
            "header" => "User-Agent: MyScript/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);

    // Make a request to the API
    $response = file_get_contents($url, false, $context);

    // Decode the JSON response
    $data = json_decode($response);

    // Check if the response is not empty and has results
    if (!empty($data)) {
        // Extract longitude and latitude from the first result
        $longitude = $data[0]->lon;
        $latitude = $data[0]->lat;

        // Return longitude and latitude
        return array('longitude' => $longitude, 'latitude' => $latitude);
    } else {
        // Return null if no results found
        return null;
    }
}

// Read the JSON file
$geonames = json_decode(file_get_contents('http://localhost/wementoor-quick/assets/json/states-cities.json'), true);
$new_geonames = [];

// Loop through each region
foreach ($geonames as $region => $locations) {
    echo "Region: $region<br />";
    
    // Loop through each location within the region
    foreach ($locations as $key => $location) {
        // Search for longitude and latitude
        $result = searchLocation($location . ', ' . $region);

        // Display results
        echo "Location: $location<br />";
        if ($result !== null) {
            echo "Longitude: " . $result['longitude'] . ", Latitude: " . $result['latitude'] . "<br />";
            // Update the location with longitude and latitude
            $new_geonames[$region][$location]['longitude'] = $result['longitude'];
            $new_geonames[$region][$location]['latitude'] = $result['latitude'];
        } else {
            echo "No results found.<br />";
        }
        echo "<br />";
    }
}

// Convert the updated array back to JSON and write it to the file
file_put_contents('states-cities-' . date('Ymd') . '-' . time() . '.json', json_encode($new_geonames, JSON_PRETTY_PRINT));

?>