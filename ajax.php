<?php
/*********************************************************************************
 * maksimovich@yandex.ru
 ********************************************************************************/

require_once 'include/models/Tree.php';
require_once 'include/lists/Tree.php';

class Tree_Ajax {

  public function process(){
     if($_REQUEST['action']){
        $this->$_REQUEST['action']($_REQUEST);
     } else {
       $this->responseJson(array('success' => true, 'error' => true, 'message' => 'no action'));
     }
  }

  public function getTreeJson($request){
    $treeList = new Tree_List();
    $treeJson = $treeList->getTree();
    $this->responseJson($treeJson);
  }

  public function saveElement($request){
    if($request['recordId'] > 0){
       $recordModel = Tree_Model::getInstanceById($request['recordId']);
    } else {
       $recordModel = Tree_Model::getCleanInstance();
    }
    if($recordModel){
       if($request['parent']){
          $recordModel->set('parent', $request['parent']);
       }
       if($request['text']){
          $recordModel->set('text', $request['text']);
       }
       $recordModel->save();
       return $this->responseJson(array('success' => true, 'error' => false, 'recordid' => $recordModel->getId()));
    } else {
      return $this->responseJson(array('success' => true, 'error' => true, 'message' => 'No Record Model...'));
    }
  }

  public function deleteElement($request){
    if($request['recordId'] > 0){
       $recordModel = Tree_Model::getInstanceById($request['recordId']);
       $recordModel->delete();
       return $this->responseJson(array('success' => true, 'error' => false, 'message' => 'Record '.$request['recordId'].' recursive deleted...'));
    } else {
       return $this->responseJson(array('success' => true, 'error' => true, 'message' => 'No recordId ...'));
    }
  }

  public function createTableDefault(){
     Tree_Model::createTableDefault();
     return $this->responseJson(array('success' => true, 'error' => false, 'message' => 'Default Table Books Created...'));
  }

  public function responseJson($response){
    header('Content-Type: application/json');
    echo json_encode($response);
  }

}

$treeAjax = new Tree_Ajax();
$treeAjax->process();
