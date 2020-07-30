<?php
/*********************************************************************************
 * maksimovich@yandex.ru
 ********************************************************************************/

require_once 'include/PearDatabase.php';

class Tree_Model {

  protected $valueMap;
  public $table_name = 'books';
  public $table_index = 'bookid';


  public function save(){
    $adb = PearDatabase::getInstance();
    $values = $this->getData();
    unset($values['recordid']);
    if($this->getId()){
       $this->update($this->table_name, $values, $this->table_index, $this->getId());
     } else {
              $recordid = $this->insert($this->table_name, $values);
              $this->set('recordid', $recordid);
             }
    return $this;
  }

  public function delete($recursivelyDelete=true){
    $adb = PearDatabase::getInstance();
    $sql = "DELETE FROM $this->table_name WHERE ".$this->table_index."=". $this->getId();
    $adb->pquery($sql);
    if($recursivelyDelete){
       $recordModelChilds = $this->getRecordChilds();
       if($recordModelChilds){
          foreach ($recordModelChilds as $recordModel) {
                   $recordModel->delete();
          }
       }
    }
  }

  public static function  getInstanceById($recordId){
    $adb = PearDatabase::getInstance();
    $instance = new self();
    $sql = "SELECT * FROM ".$instance->table_name." WHERE ".$instance->table_index."=". $recordId;
    $res = $adb->pquery($sql);
    $result = $adb->fetchByAssoc($res);
    $result['recordid'] = $result[$instance->table_index];
    if($result['recordid'] && $result['recordid']>0){
       unset($result[$instance->table_index]);
       $instance->setData($result);
       return $instance;
      } else {
             return false;
            }
  }

  public static function getCleanInstance(){
    $adb = PearDatabase::getInstance();
    $instance = new self();
    $sql = "SHOW COLUMNS FROM ".$instance->table_name;
    $res = $adb->pquery($sql);
    while ($dataCol = $adb->fetchByAssoc($res)) {
           $result[] = $dataCol;
    }
    foreach ($result as $key => $value) {
             if($value['extra'] == 'auto_increment'){
                $values['recordid'] = '';
                } else {
                        $values[$value['field']] = '';
                        }
             }
    $instance->setData($values);
    return $instance;
  }

  public function getId(){
     return $this->valueMap['recordid'];
  }

  public function get($key){
     return $this->valueMap[$key];
  }

  public function set($key,$value){
     $this->valueMap[$key] = $value;
     return $this;
  }

  public function setData($values){
     $this->valueMap = $values;
     return $this;
  }

  public function getData(){
     return $this->valueMap;
  }

  public function has($key) {
     return array_key_exists($key, $this->valueMap);
  }

  public function getRecordChilds() {
    $adb = PearDatabase::getInstance();
    $res = $adb->pquery('SELECT * FROM books WHERE parent = '.$this->getId());
    $booksRecords = array();
    while ($booksTab = $adb->fetchByAssoc($res)) {
           if($booksTab[$this->table_index] > 0){
              $booksRecords[] = self::getInstanceById($booksTab[$this->table_index]);
           }
    }
    if(count($booksRecords) > 0){
      return $booksRecords;
    } else {
      return false;
    }
  }

  private function insert($table, $values = array()){
    $adb = PearDatabase::getInstance();
    foreach ($values as $field => $v){
            $ins[] = " '".$v."' ";
    }
    $ins = implode(', ', $ins);
    $fields = implode(', ', array_keys($values));
    $sql = "INSERT INTO $table ($fields) VALUES ($ins)";
    $adb->pquery($sql);
    return $adb->getLastInsertID($table);
  }

  private function update($table, $values = array(), $table_index, $recordId){
    foreach ($values as $key => $value) {
             $paramsUpdate[] = " ".$key." = '".$value."' ";
    }
    $adb = PearDatabase::getInstance();
    $sql = "UPDATE  $table SET  " . implode(",", $paramsUpdate) . "  WHERE ".$table_index." = ".$recordId;
    $adb->pquery($sql);
    return true;
  }


  public static function createTableDefault(){

    $model = new self();
    $adb = PearDatabase::getInstance();
    $sql = "DELETE FROM ".$model->table_name;
    $adb->pquery($sql);

    $chars = 'Loremipsum';
    $i = 0;
    while ($i < 500) {
      $recordModel = self::getCleanInstance();
      $recordModel->set('text', substr(str_shuffle($chars), 0, 3));
      $recordModel->save();
      $i ++;
    }

    $listBooksArray = array();
    $res = $adb->pquery('SELECT * FROM '. $model->table_name );
    while ($booksTab = $adb->fetchByAssoc($res)) {
      $listBooksArray[] = $booksTab;
    }
    shuffle($listBooksArray);
    $rootId = $listBooksArray[0]['bookid'];
    unset($listBooksArray[0]);
    $i = 0;
    foreach ($listBooksArray as $key => $value) {
             $recordModel = self::getInstanceById($value['bookid']);
             if($i == 0){
                $i = mt_rand (10, 25);
                $recordModel->set('parent', $rootId);
             } else {
                $recordModel->set('parent', $listBooksArray[$key-1]['bookid']);
              }
             $recordModel->save();
             $i --;
    }
  }

}
