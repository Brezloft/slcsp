<?php
exec('./setup.sh');

$zipsFileName = "zips.csv";
$plansFileName = "plans.csv";
$targetFileName = "slcsp.csv";

$host = "localhost";
$user = "adhocTmpUser";
$password = "Homework";
$database = "adhocHomeworkTmp";
$zipsTable = "zips";
$plansTable = "plans";

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

if (!($stmt = $mysqli->prepare("INSERT INTO " . $zipsTable . " (zipcode, state, county_code, name, rate_area) VALUES(?, ?, ?, ?, ?)"))) {
    echo "Prepare failed(" . $mysqli->errno . ") " . $mysqli->error;
}

$zipsFile = fopen($zipsFileName, "r")  or die ("Unable to open " . $zipsFileName);
// ignore titles
$line = fgets($zipsFile);

// records of interest
$zipArr = file($targetFileName);

$zipsFile = fopen($zipsFileName, "r") or die ("Unable to open zips.csv");
while(!feof($zipsFile)) {
    $line = fgets($zipsFile);
    $data = explode(",", $line);

    // Only save records that might be of interest for performance reasons
    if(array_search($data[0] . ",\n", $zipArr)) {
        if(!$stmt->bind_param("isssi", $data[0], $data[1], $data[2], $data[3], $data[4])) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if(!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
    }

}
fclose($zipsFile);
$stmt->close();

if (!($stmt = $mysqli->prepare("INSERT INTO " . $plansTable . " (plan_id, state, metal_level, rate, rate_area) VALUES(?, ?, ?, ?, ?)"))) {
    echo "Prepare failed(" . $mysqli->errno . ") " . $mysqli->error;
}

$plansFile = fopen($plansFileName, "r")  or die ("Unable to open " . $plansFileName);
$line = fgets($plansFile);
while(!feof($plansFile)) {
    $line = fgets($plansFile);
    $data = explode(",", $line);

    // Only save records that might be of interest for performance reasons
    if(count($data) > 1 && $data[2] == "Silver") {
        if(!$stmt->bind_param("sssdi", $data[0], $data[1], $data[2], $data[3], $data[4])) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if(!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
}
fclose($plansFile);
$stmt->close();

$targetFile = fopen($targetFileName, "w");
fwrite($targetFile, $zipArr[0]);

for($i = 1; $i < count($zipArr); $i++) {
    $targetZip = rtrim($zipArr[$i]);
    $targetZip = substr($targetZip, 0, -1);
    $query = "SELECT DISTINCT p.rate, p.rate_area, p.state FROM plans p JOIN zips z ON (z.state= p.state AND z.rate_area = p.rate_area) WHERE z.zipcode = " . $targetZip. " ORDER BY rate";
    if ($result = $mysqli->query($query)) {
        $j = 0;
        $rate = -1;
        while ($row = $result->fetch_row()) {
            $j++;
            if ($j == 1) {
                $j++;
            }
            if($j == 2) {
               // mysqli returns all data types as strings
               $rateFloat = (float)$row[0];
               // float casting can be a little off
               $rate = round($rateFloat, 2);
            }
        }
        writeEntry($targetZip, $rate, $targetFile);
    } else {
        echo "Join query failed: (" . $stmt->errno . ") " . $stmt->error;
    }
}
fclose($targetFile);

exec('./cleanup.sh');
exit();


function writeEntry($targetZip, $rate, $tmpFile) {
    $outputStr = "" . $targetZip;
    if ($rate > 0) {
        $outputStr .= "," . $rate;
    }
    $outputStr .= "\n";
    fwrite($tmpFile, $outputStr);
}
?>
