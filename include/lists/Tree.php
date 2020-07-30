<?php
/*********************************************************************************
 * maksimovich@yandex.ru
 ********************************************************************************/

require_once 'include/PearDatabase.php';

class Tree_List {

  protected $level = 0;
  protected $openLevel = 3;

  public function getTree(){
    $tabList = self::getTabFormatList();
    $treeElements = $this->getTreeFormatElemets($tabList, 0);
    return $treeElements;
  }

  public function getTreeFormatElemets($tabList, $id){
    $resultArr = array();
    if($tabList && isset($tabList[$id])){
       $this->level++;
       foreach ($tabList[$id] as $value) {
           $child = $this->getTreeFormatElemets($tabList, $value['id']);
           if($child){
              $value['children'] = $child;
           }
           if($this->level < $this->openLevel){
             $value['state'] = array('opened' => true);
           }
           if($this->level == '1'){
             $value['state'] = array('opened' => true,'selected' => true);
           }
           $value['li_attr'] = array('recordid' => $value['id']);
           $resultArr[] = $value;
       }
       $this->level--;
       return $resultArr;
    } else {
      return false;
    }

  }

  public static function getTabFormatList(){
    $adb = PearDatabase::getInstance();
    $booksArr = array();
    $res = $adb->pquery('SELECT * FROM books ORDER BY text ');
    while ($booksTab = $adb->fetchByAssoc($res)) {
      $booksTabResult = array();
      $booksTabResult['id'] = $booksTab['bookid'];
      $booksTabResult['text'] = $booksTab['text'];
      $booksArr[$booksTab['parent']][] = $booksTabResult;
    }
    if(count($booksArr) > 0){
       return $booksArr;
    } else {
        return false;
    }
  }

}
