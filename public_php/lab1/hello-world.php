<?php
function sayHello(array $names) {
    foreach ($names as $name) {
        echo "Hello $name";
        echo "<br>";
    }
}
sayHello('Lushui', 'Jasmina', 'Pali');




