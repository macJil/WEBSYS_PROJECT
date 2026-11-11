<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/DisasterSummary.php';
class DisasterSummaryController extends Controller
{
    public const TYPES=['flood'=>'Flooding','fire'=>'Fire','landslide'=>'Landslide','road_obstruction'=>'Road obstruction','other'=>'Other emergency'];
    public const STATUSES=['pending','verified','responding','resolved','rejected'];
    private DisasterSummaryModel $model;
    public function __construct() { requireRole('admin'); $this->model=new DisasterSummaryModel(); }
    public function period(): array
    {
        $zone=new DateTimeZone('Asia/Manila'); $today=new DateTimeImmutable('today',$zone);
        $values=[]; $dates=[];
        foreach (['from'=>$today->format('Y-m-01'),'to'=>$today->format('Y-m-d')] as $key=>$default) {
            $value=$_GET[$key]??$default;
            $date=is_string($value)?DateTimeImmutable::createFromFormat('!Y-m-d',$value,$zone):false;
            if (!$date || $date->format('Y-m-d')!==$value || $value<'1000-01-01' || $value>'9999-12-30') {
                http_response_code(400); exit('Enter valid start and end dates in YYYY-MM-DD format.');
            }
            $values[$key]=$value; $dates[$key]=$date;
        }
        if ($dates['from']>$dates['to']) { http_response_code(400); exit('Start date must be on or before end date.'); }
        return $values+['start'=>$values['from'].' 00:00:00','end_exclusive'=>$dates['to']->modify('+1 day')->format('Y-m-d').' 00:00:00'];
    }
    public function summary(array $period): array { return $this->model->summarize($period['start'],$period['end_exclusive']); }
}
