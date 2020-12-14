<?php

namespace App\Controller;
use App\Services\nation\Creator;
use App\Services\nation\ExcelParser;
use Cake\Network\Response;
use Security\Model\Table\UsersTable;

/**
 * Class EducationCreatorController
 * @package App\Controller
 */
class NationCreatorController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Area.Areas');
    }

    public function create()
    {
        /*$parser = new ExcelParser('../src/Services/nation/areas.xlsx');
        $aimakInfo = $parser->getInfo();
        foreach($aimakInfo as $key=>$item){
            $areaCode = trim($item[1]);
            $aimakCode = trim($item[2]);
            $aimakName = trim($item[3]);

            $areasItem = $this->Areas
                ->find()
                ->where(['code'=>$areaCode])
                ->first();
            if(count($areasItem) > 0){
                $area_level_id = $areasItem->area_level_id;
                $areaId = $areasItem->id;
                if($area_level_id == 3){
                    $aimakItem = $this->Areas
                        ->find()
                        ->where(['code'=>$aimakCode])
                        ->first();
                    if(count($aimakItem) == 0){
                        $data = array();
                        $data['code'] = $aimakCode;
                        $data['name'] = $aimakName;
                        $data['parent_id'] = $areaId;
                        $data['area_level_id'] = 4;

                        $areasObject = $this->Areas->newEntity($data);
                        $this->Areas->save($areasObject);
                    }
                }
            }
        }*/
        die;
        /*$creator = new Creator($this->EducationProgrammes, $this->EducationSpecialization);
        foreach($educationFieldInfo as $keyProgramme=>$itemProgramme){
            if($keyProgramme == '0') continue;
            $creator->create($itemProgramme);
        }*/

        return new Response();
    }
}