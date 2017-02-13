<?php
$zipsFileName = "zips.csv";
$plansFileName = "plans.csv";
$targetFileName = "slcsp.csv";
$tmpFileName = "tmp.csv";

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
$zipArr = file("slcsp.csv");

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

$tmpFile = fopen($tmpFileName, "w");
fwrite($tmpFile, $zipArr[0]);

for($i = 1; $i < count($zipArr); $i++) {
    $targetZip = rtrim($zipArr[$i]);
    $targetZip = substr($targetZip, 0, -1);
    $query = "SELECT DISTINCT state, rate_area FROM " . $zipsTable . " WHERE zipcode =  " . $targetZip;
    $rateArr = [];
    $rate_area_arr = [];

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_row()) {
            // Assumes zip codes do not cross state lines
            $state = $row[0];
            array_push($rate_area_arr,$row[1]);       
	}
        // if no rate_areas of interest, write output and continue
        if(count($rate_area_arr) == 0) {
            writeEntry($targetZip, $rateArr, $tmpFile);
        } else {
            foreach($rate_area_arr as $rate_area) {

                $query = "SELECT rate FROM " . $plansTable . " WHERE state = '" . $state . "' AND rate_area = " . $rate_area;
                if ($result = $mysqli->query($query)) {
                    while ($row = $result->fetch_row()) {
                        // mysqli returns all data types as strings
                        $rateFloat = (float)$row[0];
                        // float casting can be a little off
                        $usableRate = round($rateFloat, 2);
                        array_push($rateArr, $usableRate);
                    }
                }
            }
            writeEntry($targetZip, $rateArr, $tmpFile);
        }
    }
}
fclose($tmpFile);
copy($tmpFileName,$targetFileName);

// Cleanup
unlink($tmpFileName);
$query = "DROP TABLE " . $plansTable;
$result = $mysqli->query($query);
$query = "DROP TABLE " . $zipsTable;
$result = $mysqli->query($query);
exit();

function writeEntry($targetZip, $rateArr, $tmpFile) {
    $inputStr = "" . $targetZip;
    if(count($rateArr) > 1) {
        sort($rateArr);
        $inputStr .= "," . $rateArr[1];   
    }
    $inputStr .= "\n";
    fwrite($tmpFile, $inputStr);
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
?>
