<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'lib.php';
require_once 'moduls/hash_module.php';
//Вывод вхождений позиций вхождений в строку и номер строки
$file = new FileLib('text.txt');
$res = $file->Count_and_pos('wel');
foreach ($res as $val){
    echo "В строке ".$val['line']." вхождения на позиции: ";
    $str = '';
    foreach ($val['positions'] as $positions){
        $str .= $positions.', ';
    }
    $str = substr($str, 0, -2);
    echo $str.'</br>';
}
//Вывод одинаковых хэш-сумм в строках файла 
$new_file = new hash_module('text.txt');
$repeat_elements = $new_file->get_hash_summ();
if(count($repeat_elements) > 0){
    foreach ($repeat_elements as $val){
        echo "Хэш-суммы строк $val совпадают </br>";
    }
} else {
    echo "Нет повторяющихся хэш-сумм";
}
$f = new FileLib('http://cm26687.tmweb.ru/text_file.txt');
var_dump($f->Count_and_pos('123'));

?>