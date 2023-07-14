<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Vollkorn:ital@0;1&display=swap" rel="stylesheet">
    <style>
  /* Set default for body */
  body {
  color: #333;
  background-color: #f2f2f2;
  display:flex;
  padding: 80px;
 
}
/*bg video css*/
.back-video{
  margin: 0;
  position: absolute;
  right: 0;
  bottom: 0;
  z-index: -1;
  object-fit: cover;
  width: 100%;
  height: auto;
}
/* Style the container */
.container {
  max-width: 600px;
  color: "white";
  height: auto;
  margin: 0 auto;
  padding: 40px;
  background: linear-gradient(rgb(244, 234, 234),rgb(72, 206, 243));
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Style the form */
form {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
}

label {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 10px;
}

input[type="text"] {
  padding: 10px;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  margin-bottom: 20px;
  width: 100%;
  max-width: 400px;
}

button[type="submit"] {
  background-color: #009688;
  color: #fff;
  padding: 10px 20px;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s;
}

button[type="submit"]:hover {
  background-color: #00796b;
}

/* Style the weather info */
.city {
  font-size: 36px;
  font-weight: bold;
}

#temp {
  font-size: 48px;
  font-weight: bold;
}

#weather-icon {
  vertical-align: middle;
  margin-left: 10px;
}

p {
  font-size: 18px;
  margin-bottom: 10px;
}
.weather-icon{
  width:100px;
}
#condition {
  font-weight: bold;
}

#wind, #humidity {
  font-weight: bold;
}

#datetime {
  font-weight: bold;
  font-size: 16px;
}
table {
  background: linear-gradient(rgb(244, 234, 234),rgb(72, 206, 243));
  color: black;
  border: 1px solid black;
  border-collapse: collapse;
  width: 50%;
  margin-bottom: 20px;
  align-items:center;
  }
  
th {
  background-color: #009688;
  color: white;
  border: 1px solid black;
  padding: 8px;
  }
td {
  border: 1px solid black;
  padding: 8px;
  text-align: center;
  }
tr:hover {
  background-color: #009688;
  color: white;
}
#weather-icon img {
      width: 100px; /* set the desired width */
      
}
/* Responsive layout */
@media screen and (max-width: 700px) {
  body{
    padding: 0;
    display: flex;
    flex-direction: column;
    
  }
  .back-video{
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
}
  .container {
    max-width: 80%;
    padding: 10px;
    height:500px;
    border-radius: 10px;
    box-shadow: none;
    position: relative;
    overflow: hidden;
  }
  input[type="text"] {
    max-width: none;
  }
}
</style>
    <title>Weather data</title>
</head>
<body>
    <?php 
   //API Key for OpenWeatherMap
  $api_key = "488fb76602b0a7f804952e2e3c1fc548";
  //check whether the location is provided or not via POST,else use default city name
  if(isset($_POST['location'])){
  $city = $_POST['location'];
  }else{
  $city = "Fort Collins";
  }

  // Connecting to the Database
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database="weather_data";
    
  // Create a connection
  $conn = mysqli_connect($servername, $username, $password, $database);
    
  // Die if connection was not successful
  if (!$conn){
      die("Sorry we failed to connect: ". mysqli_connect_error());
  }
  $sql = "TRUNCATE TABLE weather_info";
  if (!mysqli_query($conn, $sql)) {
    echo "Error truncating table: " . mysqli_error($conn);
  }

  //fetch data from API
  $url = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$api_key&units=metric";
  $response = @file_get_contents($url); // suppress warnings
  if ($response === false) {
    die("Could not find weather data for $city.");
  }
  //decodes the JSON data to a PHP associative array
  $data = json_decode($response, true);

  //access the data
  for ($i = 0; $i < 7; $i++) {
   $day = $data["list"][$i];
   $num_days = $i + 1;
   $date = date("Y-m-d H:i:s", strtotime("-$num_days day", $day["dt"]));
   $temp = $day["main"]["temp"];
   $weather = $day["weather"][0]["description"];
   $icon = $day["weather"][0]["icon"];
   $windspeed = $day["wind"]["speed"];
   $humidity = $day["main"]["humidity"];

  //query to insert
  $sql = "INSERT INTO weather_info (date, temperature, weather, icon, windspeed, humidity) VALUES ('$date', $temp, '$weather', '$icon', $windspeed, $humidity)";
  mysqli_query($conn, $sql);
}
 
$sql = "SELECT * FROM weather_info";
$result = mysqli_query($conn, $sql);
//displays database named as weather_info in a tabular form
if (mysqli_num_rows($result) > 0) {
  echo "<table>";
  echo '<tr><th>Date and Time</th><th>Temperature</th><th>Weather</th><th>Icon</th><th>Windspeed</th><th>Humidity</th></tr>';
  while ($row = mysqli_fetch_assoc($result)) {
    $icon_url = "https://openweathermap.org/img/w/" . $row["icon"] . ".png";
    echo "<tr><td>" . $row["date"] . "</td><td>" . $row["temperature"] . " °C</td><td>" . $row["weather"] . "</td><td><img src='$icon_url' alt='" . $row["weather"] . "'></td><td>" . $row["windspeed"]." km/hr" . "</td><td>" . $row["humidity"]."%" . "</td></tr>";
  }
  echo "</table>";
} else {
  echo "No data discovered";
}
  
  // Close database connection
  mysqli_close($conn);
  ?>
  <div class="container">
  <form method="post">
    <label for="location">Enter location:</label>
    <input type="text" id="location" name="location" required >
    <button type="submit">Get Weather</button>
    <video autoplay loop muted plays-inline class="back-video">
         <source src="./video.mp4" type="video/mp4">
      </video>
  </form>

  <?php
    echo '<h1 class="city">Weather in ' . $city . '</h1>';
    echo '<p>Temperature: <span id="temp">' . $temp . '</span> <i id="weather-icon"><img src="' . $icon_url . '" alt="icon"></i></p>';
    echo '<p>Weather Condition: <span id="condition">' . $weather . '</span></p>';
    echo '<p>Wind Speed: <span id="wind">' . $windspeed . ' kph</span></p>';
    echo '<p>Humidity: <span id="humidity">' . $humidity . '%</span></p>';
  ?>
  <p>Date: <span id="datetime"><?php echo date("F j, Y"); ?></span></p>
</div>
    </div>
</body>
</html>