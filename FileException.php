<?php
//Класс обработки исключений 
class FileException extends Exception {
  public function __construct($code) {
    parent::__construct("", $code);
  }
}

?>