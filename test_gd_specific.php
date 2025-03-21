<?php
if (extension_loaded('gd')) {
    echo "La extensión GD está habilitada<br>";
    echo "Versión de GD: " . gd_info()['GD Version'] . "<br>";
} else {
    echo "La extensión GD NO está habilitada<br>";
}
?> 