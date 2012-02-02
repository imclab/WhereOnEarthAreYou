<!doctype html>  
<html>
 <head>  
  <title>Where on Earth Are You?</title>  
  <style type="text/css">
li {
  padding-bottom: 1em;
}
  </style>
 </head>  
 <body>  
  <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
   <label for="name">Location Name:</label>
   <input type="text" name="name" id="name" value="">
   <input type="submit" value="Submit">
  </form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appId = "YOUR_APP_ID_HERE";
    $handle = curl_init("http://wherein.yahooapis.com/v1/document");
    curl_setopt($handle, CURLOPT_POST, 1);
    curl_setopt($handle, CURLOPT_POSTFIELDS, sprintf("documentContent=%s&documentType=%s&outputType=%s&autoDisambiguate=%s&appid=%s&inputLanguage=%s",
        urlencode($_POST["name"]),
        "text/plain",
        "xml",
        "false",
        $appId,
        "en-US"));
    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($handle);
    curl_close($handle);

    $places = array();
    $xml = simplexml_load_string($response);
    foreach ($xml->document->placeDetails as $xmlPlaceDetail) {
    $xmlPlace = $xmlPlaceDetail->place;
        $xmlCentroid = $xmlPlace->centroid;

        $place = new stdClass();
        $place->id = (int)$xmlPlaceDetail->placeId;
        $place->woeid = (int)$xmlPlace->woeId;
        $place->name = (string)$xmlPlace->name;
        $place->lat = (float)$xmlCentroid->latitude;
        $place->lng = (float)$xmlCentroid->longitude;
        $place->confidence = (int)$xmlPlaceDetail->confidence;
       
        $places[$place->id] = $place;
    }

    uasort($places, function ($a, $b) {
        if ($a->confidence == $b->confidence) {
            return 0;
        }
        return ($a->confidence > $b->confidence) ? -1 : 1;
    });

    echo "<hr>";
    echo "<p>" . count($places) . " result(s) for: <strong>" .
        htmlspecialchars($_POST["name"]) . "</strong></p>";
    echo "<ul>";
    foreach ($places as $place) {
        echo "<li>";
        foreach ($place as $key => $value) {
            echo "<strong>" . $key . ":</strong> " .
                htmlspecialchars($value) . "<br>";
        }
        echo "</li>";
    }
    echo "</ul>";
}
?>
 </body>
</html>
