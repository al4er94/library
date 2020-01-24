<?php
include_once 'lib.php';
//Модуль для сравнения хэш-сумм в файле
class hash_module extends FileLib{
    public function get_hash_summ(){
        try{
            if(!($this->file)){ //Провреям, существует ли файл
                throw new FileException(1);
            } else {
                //Записываем хэш-суммы строк в массив
                $hash_array = array();
                while (!feof($this->file)){
                    $string= fgets($this->file); 
                    $hash_array[]= md5($string);
                }
                $repeat_elements = array();
                //Ищем одинаковые хэш-суммы
                for($i=0; $i<count($hash_array); $i++){
                    for($j=($i+1); $j<count($hash_array); $j++){
                        if($hash_array[$i] == $hash_array[$j]){
                            $repeat_elements[] = $i.','.$j;    
                        }
                    }
                }
                return $repeat_elements;
            }
        } catch (FileException $ex) {
            if ($ex->getCode() == 1) {echo "error: file not exist\n";}
        }
    }
}