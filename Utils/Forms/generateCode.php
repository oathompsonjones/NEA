<?php
require_once "../Functions/arrayMappers.php";
require_once "../../Classes/PHP/frame.php";
require_once "../../Classes/PHP/animation.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$animation = null;
switch ($_POST["matrix"]) {
    case "Micro:Bit Internal":
        $animation = new MicroBitInternalAnimation($_POST["animationID"]);
        break;
    case "LoL Shield":
        $animation = new LoLShieldAnimation($_POST["animationID"]);
        break;
    case "Pico RGB Keypad":
        $animation = new PicoRGBKeypadAnimation($_POST["animationID"]);
        break;
    case "AdaFruit Neo Pixel RGB 8x8":
        $animation = new AdaFruitNeoPixelRGB8x8Animation($_POST["animationID"]);
        break;
    case "Scroll Bit":
        $animation = new ScrollBitAnimation($_POST["animationID"]);
        break;
    case "HL-M1388AR 8x8":
        $animation = new HLM1388AR8x8Animation($_POST["animationID"]);
        break;
    case "AdaFruit 16x9":
        $animation = new AdaFruit16x9Animation($_POST["animationID"]);
        break;
    default:
        $animation = new Animation($_POST["animationID"]);
        break;
}
switch ($_POST["board"]) {
    case "BBC Micro:Bit":
        switch ($_POST["language"]) {
            case "MicroPython":
                echo htmlspecialchars($animation->generateMicroBitMicroPythonCode($_POST["fps"]));
                break;
            case "TypeScript":
                echo htmlspecialchars($animation->generateMicroBitTypeScriptCode($_POST["fps"]));
                break;
            case "Hex File":
                echo htmlspecialchars($animation->generateMicroBitHexFile($_POST["fps"]));
                break;
        }
        break;
    case "Arduino":
        switch ($_POST["language"]) {
            case "C++":
                echo htmlspecialchars($animation->generateArduinoCppCode($_POST["fps"]));
                break;
        }
        break;
    case "Raspberry Pi Pico":
        switch ($_POST["language"]) {
            case "C++":
                echo htmlspecialchars($animation->generatePicoCppCode($_POST["fps"]));
                break;
            case "MicroPython":
                echo htmlspecialchars($animation->generatePicoMicroPythonCode($_POST["fps"]));
                break;
        }
        break;
}
