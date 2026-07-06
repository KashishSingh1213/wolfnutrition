<?php
$hash = '$2y$10$1.ssfFk6Xz8hNjNF6p8VgeAjIdoFMsEvSJtZzF2/fIzDz.Mz6gqIe';
$pwds = ['password','123456','admin','admin123','wolf123','wolfnutrition','kashish','madhav','12345678','qwerty','test123','pass123','wolf1234','Wolve123','Password1','Kashish123','Wolf@123','Wolf123','kashish123','singh123','singh','Kashish','madhavarora','wolf','nutra123','WolfNutrition','wolf@123'];
foreach($pwds as $p){
    if(password_verify($p, $hash)){
        echo "FOUND: " . $p . PHP_EOL;
        exit;
    }
}
echo "NOT FOUND in common list" . PHP_EOL;
