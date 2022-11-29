<?php

ini_set('max_execution_time', '1700');
set_time_limit(1700);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

$input = json_decode(file_get_contents("php://input"), true);
if ($inputInclude != NULL) {
    $input = $inputInclude;
}
putenv('GDFONTPATH=' . realpath('.'));

function send_forward($inputJSON, $link){
    $request = 'POST';	
    $descriptor = curl_init($link);
     curl_setopt($descriptor, CURLOPT_POSTFIELDS, $inputJSON);
     curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
     curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $request);
    $itog = curl_exec($descriptor);
    curl_close($descriptor);
    return $itog;
}
function send_bearer($url, $token, $type = "GET", $param = []){
    $descriptor = curl_init($url);
     curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
     curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('User-Agent: M-Soft Integration', 'Content-Type: application/json', 'Authorization: Bearer '.$token)); 
     curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);
    $itog = curl_exec($descriptor);
    curl_close($descriptor);
    return $itog;
}

if ($input["getInfo"] == "true") {
    $result["state"] = true;
    $result["GD_info"] = gd_info();
    echo json_encode($result);
    exit;
}

$dir = dirname($_SERVER["PHP_SELF"]);
$url = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $dir;
$url = explode("?", $url);
$url = $url[0];
$imageName = "image_".date_timestamp_get(date_create())."_".mt_rand(0,999);

if ($input["image"] != NULL && is_array($input["image"])) {
    // Открытие фонового изображения
    if ($input["image"]["url"] != NULL) {
        $imageFormat = getimagesize($input["image"]["url"]);
        $imageWidth = $imageFormat[0];
        $imageHeight = $imageFormat[1];
        if ($imageFormat["mime"] == "image/bmp") {
            $imageSRC = imagecreatefrombmp($input["image"]["url"]);
        } else if ($imageFormat["mime"] == "image/png") {
            $imageSRC = imagecreatefrompng($input["image"]["url"]);
        } else if ($imageFormat["mime"] == "image/jpeg") {
            $imageSRC = imagecreatefromjpeg($input["image"]["url"]);
        }
        if ($imageSRC === false) {
            $result["state"] = false;
            $result["error"]["message"][] = "'image.url' failed is open image. File must be an bmp/png/jpeg";
            echo json_encode($result);
            exit;
        }
    } else {
        $result["state"] = false;
        $result["error"]["message"][] = "'image.url' is missing";
        echo json_encode($result);
        exit;
    }
} else {
    $result["state"] = false;
    $result["error"]["message"][] = "'image' must be an array";
    echo json_encode($result);
    exit;
}
// Добавление изображений поверх
if ($input["layers"] != NULL) {
    if (is_array($input["layers"])) {
        foreach ($input["layers"] as $layerKey => $layerValue) {
            if (gettype($layerValue) != "array") {
                $result["state"] = false;
                $result["error"]["message"][] = "'layers.".$layerKey."' must me an array";
            } else {
                if ($layerValue["type"] == "text") {
                    // Текст
                    if ($layerValue["font"] == NULL) {
                        $result["state"] = false;
                        $result["error"]["message"][] = "'layers.".$layerKey.".font' is missing";
                    }
                    if ($layerValue["text"] == NULL) {
                        $result["state"] = false;
                        $result["error"]["message"][] = "'layers.".$layerKey.".text' is missing";
                    } else if ($layerValue["max_width"] != NULL) {
                        $layerValue["text"] = wordwrap($layerValue["text"], $layerValue["max_width"], PHP_EOL);
                    }
                    if (!($layerValue["size"] > 4)) {
                        $result["state"] = false;
                        $result["error"]["message"][] = "'layers.".$layerKey.".size' must be an > 4";
                    }
                    if ($layerValue["angle"] == NULL) {
                        $layerAngle = 0;
                    } else {
                        $layerAngle = $layerValue["angle"];
                        settype($layerAngle, "int");
                        if ($layerAngle != $layerValue["angle"]) {
                            $result["state"] = false;
                            $result["error"]["message"][] = "'layers.".$layerKey.".angle' must be in integer";
                        }
                    }
                    if ($layerValue["x"] == NULL) {
                        $layerX = 0;
                    } else if ($layerValue["x"] == "center") {
                        $textBox = imageftbbox($layerValue["size"], $layerAngle, $layerValue["font"], $layerValue["text"]);
                        if ($textBox === false) {
                            $result["state"] = false;
                            $result["error"]["message"][] = "'layers.".$layerKey."' failed add text";
                            break;
                        } else {
                            $diag1 = abs($textBox[4] - $textBox[0]);
                            $diag2 = abs($textBox[6] - $textBox[2]);
                            if ($diag1 >= $diag2) {
                                $layerX = ($imageWidth - $diag1) / 2;
                            } else {
                                $layerX = ($imageWidth - $diag2) / 2;
                            }
                        }
                    } else {
                        $layerX = $layerValue["x"];
                        settype($layerX, "int");
                        if ($layerX != $layerValue["x"]) {
                            $result["state"] = false;
                            $result["error"]["message"][] = "'layers.".$layerKey.".x' must be in integer";
                        }
                    }
                    if ($layerValue["y"] == NULL) {
                        $layerY = 0+$layerValue["size"];
                    } else if ($layerValue["y"] == "center") {
                        $textBox = imageftbbox($layerValue["size"], $layerAngle, $layerValue["font"], $layerValue["text"]);
                        if ($textBox === false) {
                            $result["state"] = false;
                            $result["error"]["message"][] = "'layers.".$layerKey."' failed add text";
                            break;
                        } else {
                            $diag1 = abs($textBox[5] - $textBox[1]);
                            $diag2 = abs($textBox[7] - $textBox[3]);
                            if ($diag1 >= $diag2) {
                                $layerY = ($imageHeight - $diag1) / 2;
                            } else {
                                $layerY = ($imageHeight - $diag2) / 2;
                            }
                        }
                    } else {
                        $layerY = $layerValue["y"];
                        settype($layerY, "int");
                        if ($layerY != $layerValue["y"]) {
                            $result["state"] = false;
                            $result["error"]["message"][] = "'layers.".$layerKey.".y' must be in integer";
                        }
                    }
                    
                    if ($result["state"] === false) {
                        break;
                    }
                    // Добавить проверку цветов
                    $textColor = imagecolorallocatealpha($imageSRC, $layerValue["color"]["red"], $layerValue["color"]["green"], $layerValue["color"]["blue"], $layerValue["color"]["alpha"]);
                    $layerText = imagettftext($imageSRC, $layerValue["size"], $layerAngle, $layerX, $layerY,  $textColor, $layerValue["font"], $layerValue["text"]);
                    if ($layerText === false) {
                        $result["state"] = false;
                        $result["error"]["message"][] = "'layers.".$layerKey."' failed add text";
                        break;
                    }
                } else {
                    $result["state"] = false;
                    $result["error"]["message"][] = "'layers.".$layerKey.".type' is not supported";
                }
            }
        }
    }
}

if ($result["state"] === false) {
    echo json_encode($result);
    exit;
} else {
    if (file_exists("images") !== true) {
        mkdir("images");
    }
    // Сохранение изображения
    $imageSRC = imagejpeg($imageSRC, "images/".$imageName.".jpg", 100);
    $imageSaveUrl = $url."/images/".$imageName.".jpg";
    if ($resultImage === false) {
        $result["state"] = false;
        $result["error"]["message"][] = "failed is save image";
        echo json_encode($result);
        exit;
    } else {
        $result["state"] = true;
        $result["image"]["url"] = $imageSaveUrl;
        $result["image"]["width"] = getimagesize($imageSaveUrl)[0];
        $result["image"]["height"] = getimagesize($imageSaveUrl)[1];
    }
}

echo json_encode($result);
