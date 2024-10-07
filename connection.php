<?php
$serverName = "localhost";
$userName = "root";
$password = "";
$databaseName = "prototype2";

// Create connection
$conn = new mysqli($serverName, $userName, $password);

// Check connection
if ($conn->connect_error) {
    $error = "Connection failed: " . $conn->connect_error;
    echo json_encode(['error' => $error]);
    exit;
}

// Create database if it doesn't exist
$createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS $databaseName";
if ($conn->query($createDatabaseQuery) !== TRUE) {
    $error = "Error creating database: " . $conn->error;
    echo json_encode(['error' => $error]);
    exit;
}

// Select database
$conn->select_db($databaseName);

// Create table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS weather1 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(255) NOT NULL,
    temperature FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    wind_speed FLOAT NOT NULL,
    pressure FLOAT NOT NULL,
    weather_condition VARCHAR(255) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    wind_direction FLOAT NOT NULL,
    fetched_at DATETIME
)";
if ($conn->query($createTableQuery) !== TRUE) {
    $error = "Error creating table: " . $conn->error;
    echo json_encode(['error' => $error]);
    exit;
}

// Check if existing weather data for the city is less than 2 hours old
$cityName = isset($_GET['q']) ? $_GET['q'] : "Belfast";
$selectAllData = "SELECT * FROM weather1 WHERE city = '$cityName' ORDER BY fetched_at DESC";
$result = $conn->query($selectAllData);
$row = $result->fetch_assoc();
$lastUpdated = isset($row['fetched_at']) ? strtotime($row['fetched_at']) : 0;
$currentTime = time();

if ($result->num_rows == 0 || $currentTime - $lastUpdated >= 7200) {
    // Fetch weather data from the API
    $apiKey = "7ec3a74dc43469b1b8ded51f11ef82d4";
    $url = "http://api.openweathermap.org/data/2.5/weather?q=" . $cityName . "&units=metric&APPID=" . $apiKey;
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data) {
        $city = $data['name'];
        $humidity = $data['main']['humidity'];
        $wind_speed = $data['wind']['speed'];
        $pressure = $data['main']['pressure'];
        $temperature = $data['main']['temp'];
        $weather_condition = $data['weather'][0]['main'];
        $icon = $data['weather'][0]['icon'];
        $wind_direction = $data['wind']['deg'];
        $date = date('Y-m-d H:i:s', $data['dt']); // Format the Unix timestamp to MySQL datetime format

        // Log the date to check the value
        error_log("Fetched date: $date");

        // Insert the new weather data into the database
        $insertDataQuery = "INSERT INTO weather1 (city, temperature, humidity, wind_speed, pressure, weather_condition, icon, wind_direction, fetched_at) VALUES ('$city', '$temperature', '$humidity', '$wind_speed', '$pressure', '$weather_condition', '$icon', '$wind_direction', '$date')";
        if ($conn->query($insertDataQuery) !== TRUE) {
            $error = "Error inserting data: " . $conn->error;
            echo json_encode(['error' => $error]);
            exit;
        }
    } else {
        $error = "Error fetching data from API";
        echo json_encode(['error' => $error]);
        exit;
    }
}

// Retrieve the latest weather data for the city
$result = $conn->query($selectAllData);
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// Encoding fetched data to JSON and sending as response
$json_data = json_encode($rows);
header('Content-Type: application/json');
echo $json_data;

$conn->close();
?>