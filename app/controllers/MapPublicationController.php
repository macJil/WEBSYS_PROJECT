<?php
require_once __DIR__.'/Controller.php';
require_once __DIR__.'/../models/MapPublication.php';
class MapPublicationController extends Controller
{
    private MapPublicationModel $model;
    public function __construct(){requireRole('admin');$this->model=new MapPublicationModel();}
    public function find(string $type,int $id):array{return $this->model->find($type,$id);}
    public function save(string $type,int $id):array {
        if(!isPost())return [];
        $action=input('action');if(!in_array($action,['publish','withdraw'],true))return ['Invalid action.'];
        $d=['label'=>input('public_label'),'latitude'=>input('public_latitude'),'longitude'=>input('public_longitude')];
        if($action==='publish'&&$type==='incident'){
            $errors=locationErrors($d['label'],$d['latitude'],$d['longitude']);
            if($d['label']===''||strlen($d['label'])>150||$d['latitude']===''||$d['longitude']==='')$errors[]='Enter a safe public label and both approximate coordinates.';
            if($errors)return $errors;
        }
        try{$this->model->save($type,$id,$action==='publish',$d);}
        catch(DomainException $e){return [$e->getMessage()];}
        catch(Throwable $e){error_log((string)$e);return ['Map visibility could not be saved.'];}
        $this->success('Community map visibility saved.','resources/views/admin/map-publication.php?type='.$type.'&id='.$id);return [];
    }
}
