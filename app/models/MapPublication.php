<?php
require_once __DIR__.'/../../config/db.php';
class MapPublicationModel
{
    private PDO $pdo;
    private const TABLES=['incident'=>'incident_reports','center'=>'evacuation_centers','relief'=>'relief_locations'];
    public function __construct(){ $this->pdo=(new DATABASE())->connect(); }
    private function table(string $type):string {
        if(!isset(self::TABLES[$type]))throw new DomainException('Invalid location type.');
        return self::TABLES[$type];
    }
    public function find(string $type,int $id,bool $lock=false):array {
        $s=$this->pdo->prepare('SELECT * FROM '.$this->table($type).' WHERE id=?'.($lock?' FOR UPDATE':''));
        $s->execute([$id]);$row=$s->fetch();
        if(!$row)throw new DomainException('Record not found.');return $row;
    }
    public function save(string $type,int $id,bool $publish,array $data):void {
        $this->pdo->beginTransaction();
        try {
            $r=$this->find($type,$id,true);
            if($publish){
                if($r['archived_at']!==null)throw new DomainException('Archived records cannot be published.');
                if($type==='incident'&&!in_array($r['status'],['verified','responding','resolved'],true))throw new DomainException('Only verified, responding, or resolved incidents can be published.');
                if($type==='center'&&$r['status']!=='open')throw new DomainException('Only open centers can be published.');
                if($type==='relief'&&!in_array($r['location_type'],['distribution','both'],true))throw new DomainException('Storage-only locations cannot be published.');
                if($type!=='incident'&&(!hasMapPoint($r)||trim($r['address_details']??'')===''))throw new DomainException('Save a valid point and written address first.');
            }
            if($type==='incident'&&$publish){
                $s=$this->pdo->prepare('UPDATE incident_reports SET is_public=1,public_label=?,public_latitude=?,public_longitude=? WHERE id=?');
                $s->execute([$data['label'],$data['latitude'],$data['longitude'],$id]);
            }else{
                $s=$this->pdo->prepare('UPDATE '.$this->table($type).' SET is_public=? WHERE id=?');$s->execute([(int)$publish,$id]);
            }
            $this->pdo->commit();
        }catch(Throwable $e){$this->pdo->rollBack();throw $e;}
    }
}
