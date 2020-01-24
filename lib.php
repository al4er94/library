<?php
require_once 'FileException.php';

class FileLib{
    public $filename; //Имя файла
    public $file; //Открытый файл
    public $local_file; //Локальный файл или нет


    public function __construct($filename) {
        //Проверяем откуда у нас файл, с даленного сервера или нет    
        $check_url = parse_url($filename);
        //Если файл не с удаленного сервера
        if(!array_key_exists('scheme', $check_url)){
                $this->local_file = true;
            try {
                if (!file_exists($filename)) throw new FileException(1); //Проверяем существует ли файл
                $this->filename = $filename;
                if (!file_exists('confing.json')) throw new FileException(5);//Проверяем существует ли файл конфигурации
                
                $this->check_conging(); // Проверка конфигурации
                $fp = fopen($filename, 'r');
                if (!$fp) throw new FileException(2); //Проверяем открылся ли файл 
                $this->file = $fp;
            }
            catch (FileException $ex) {
                if ($ex->getCode() == 1) {echo "error: file not exist\n";}
                else if($ex->getCode() == 2) {echo 'error: file open error\n';}
                else if($ex->getCode() == 5) {echo 'error: confing file not exist\n';}
            }
        } else {
            //Если файл с удаленного
                $this->local_file = false;
                //Берем содержимое файла
                $file_content = file_get_contents($filename);
                //Переименовываем его, добавляя к имени фала текущее дату время
                $now_date = date("Y-m-d_H_i_s");
                $new_filename = basename($check_url['path']);
                $new_filename = $now_date.$new_filename;
                $this->filename = $new_filename;
                //Сохраняем к нам на сервер
                file_put_contents($new_filename, $file_content);
                //Проверяем конфигурацию
                $this->check_conging();
                //Теперь с ним можно работать как с локальным файлом
                $this->file = fopen($new_filename, 'r');
        }
    }
    
    public function __destruct() {
        //Закрываем файл
        fclose($this->file);
        //Если файл не локальный, то удаляем его с сервера
        if(($this->local_file) == false){
            $file = __DIR__.'/'. $this->filename;
            unlink($file);
        }
    }
    
    public function check_conging(){
        try {
            //Файл конфигурации храню в формате JSON
            $confing = self::get_file_confing('confing.json'); // Получаю массив конфигурации для файла
            if(filesize($this->filename) > $confing['max_size']) throw new FileException(3); // Проверем размер файла
            $mime_type = false; 
            foreach ($confing['mime-type'] as $val){
                if(mime_content_type($this->filename) == $val){
                    $mime_type = true;
                    break;
                }
            }
            if(!($mime_type)) throw new FileException(4); //Проверяем mime тип файла. Идем по массиву, и, если mime_type совпадает, то присваеваем ему true
        } catch (FileException $ex) {
             if($ex->getCode() == 3) {echo 'error: to big file size\n';}
             else if($ex->getCode() == 4) {echo 'error: wrong mime-type\n';}
        }
    }

    //Метод подсчета вхождений
    public function Count_and_pos($substr) {
        try{
            if(!($this->file)){ //Провреям, существует ли файл
                throw new FileException(1);
            }
            else {
                $all_text = []; // Массив со всеми вхщждениями
                $i = 1; //Номер строки
                
                while (!feof($this->file)) { 
                    $string = fgets($this->file); //Бежим по файлу построчно
                    $count_of_substr = substr_count($string, $substr); // Считем сколько у нас вхождений
                    if ($count_of_substr > 0) {
                        $all_text[] = self::get_substring_entry_position($string, $count_of_substr, $substr, $i); //Ищем позиции вхождений
                    }
                    $i++; //Идем к следующей строке 
                }
                //Возвращаем список с массивами, где line - номер строки, а positions - список позиций в строке
                return $all_text;
            }
        }
        catch (FileException $ex){
            if ($ex->getCode() == 1) {echo "error: file not exist\n";}
        }
    }
    //Метод поиска позиций вхождения в строку
    private static function get_substring_entry_position($string, $count_of_substr, $substr, $line_number){
        $pos = 0;//Позиция вхождения, начинаем искать с начала строки
        $str_pos_arr = [];
        for($i=0; $i<$count_of_substr; $i++){ 
            $pos = strpos($string, $substr, $pos); //Переопределяем позицию
            $str_pos_arr[] = $pos; //Записываем ее в список
            $pos++; //теперь ищем вхождение со следующего символа строки
        }
        //Возвращаем массив, где line - номер строки, а positions - список позиций в строке
        return $result = [ 
            'line' => $line_number,
            'positions' => $str_pos_arr
        ];
    }
    // Метод получения конфигурации файла
    private static function get_file_confing($confing){
        $info = file_get_contents($confing);
        return json_decode($info, true);
    }
}
