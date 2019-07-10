<?php
require 'vendor/autoload.php';

use Sinesp\Sinesp;

$Mainform = new Sinesp;

try {
	//$Mainform->proxy('IP, 'PORTA');
	$Mainform->a4lJIhgYU54();
    $Mainform->buscar('GGG-9996');
    if ($Mainform->existe()) {
        print_r($Mainform->dados());
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
?>