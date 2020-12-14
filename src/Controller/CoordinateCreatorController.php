<?php

namespace App\Controller;
use App\Services\Coordinate\ExcelParser;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Security\Model\Table\UsersTable;

/**
 * Class CoordinateCreatorController
 * @package App\Controller
 */
class CoordinateCreatorController extends AppController
{

    private $internetModel = 'Institution.InfrastructureUtilityInternets';
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Institution.Institutions');
        $this->loadModel('AcademicPeriod.AcademicPeriods');
    }

    public function create()
    {
//        $parser = new ExcelParser('../src/Services/Coordinate/newCoordinates.xlsx');
//        $institutionsCoordinate = $parser->getInfo();
//        $notFound=array();
//        $i=0;
//        foreach($institutionsCoordinate as $item){
//            $codeOKPO = trim($item[0]);
//            $longitude = trim(preg_replace('/\s+/', '', $item[2]));
//            $latitude = trim(preg_replace('/\s+/', '', $item[3]));
//            $institutionItem = $this->Institutions
//                ->find()
//                ->where(['code'=>$codeOKPO])
//                ->first();
//
//            if(count($institutionItem) > 0){
//
//                dump($institutionItem->longitude.'-'.$latitude);
//                dump($institutionItem->latitude.'-'.$longitude);
////                $institutionItem->longitude = !empty($latitude) ? $latitude : $institutionItem->longitude;
////                $institutionItem->latitude = !empty($longitude) ? $longitude :  $institutionItem->latitude ;
////                $this->Institutions->save($institutionItem);
//
//            }
//            else {
//                $notFound[]=$item;
//            }
//            $i++;
//        }
//        dump($notFound);
//        return new Response();
    }
    public function internet(){
//        $dataByType= [
//            'Kyrgyztelecom'=> ['internet_provider_id'=>1, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Aknet'=>['internet_provider_id'=>2, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Maxlink'=>['internet_provider_id'=>4, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Beeline'=>['internet_provider_id'=>5, 'utility_internet_type_id' => 4,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Beeline+Megacom'=>['internet_provider_id'=>6, 'utility_internet_type_id' => 4,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Megacom'=>['internet_provider_id'=>7, 'utility_internet_type_id' => 4,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Сотовоя связь "О"'=> ['internet_provider_id'=>8, 'utility_internet_type_id' => 4,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Elcat'=>['internet_provider_id'=>9, 'utility_internet_type_id' => 3,'internet_purpose'=> 1,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Fast Net'=>['internet_provider_id'=>10, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Homeline'=>['internet_provider_id'=>11, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'JET'=>['internet_provider_id'=>12, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Saima'=>['internet_provider_id'=>13, 'utility_internet_type_id' => 4,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'skynet'=>['internet_provider_id'=>14, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'КНОКС'=>['internet_provider_id'=>15, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Мегалайн'=>['internet_provider_id'=>16, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//            'Кыргыз почтасы'=>['internet_provider_id'=>17, 'utility_internet_type_id' => 3,'internet_purpose'=> 1 ,'utility_internet_bandwidth_id'=>2,'utility_internet_condition_id'=>1],
//        ];
//
//        $internetTable=TableRegistry::get($this->internetModel);
//
//        if (!$internetTable) {
//            return new Response();
//        }
//
//        $parser = new ExcelParser('../src/Services/Coordinate/newCoordinates.xlsx');
//        $institutionsCoordinate = $parser->getInfo();
//        foreach ($institutionsCoordinate as $item) {
//            $codeOKPO = trim($item[0]);
//            $providerName = trim( $item[1]);
//
//            $institutionItem = $this->Institutions
//                ->find()
//                ->where(['code' => $codeOKPO])
//                ->first();
//
//            if (count($institutionItem) > 0 && $dataByType[$providerName] != null) {
//
//                $internetItem = $internetTable
//                    ->find()
//                    ->where([
//                        'institution_id' => $institutionItem->id,
//                        'academic_period_id' => $this->AcademicPeriods->getCurrent()
//                    ])
//                    ->first();
//
//                if (count($internetItem) > 0) {
//
//                    $internetItem->internet_purpose = $dataByType[$providerName]['internet_purpose'];
//                    $internetItem->utility_internet_bandwidth_id = $dataByType[$providerName]['utility_internet_bandwidth_id'];
//                    $internetItem->utility_internet_type_id = $dataByType[$providerName]['utility_internet_type_id'];
//                    $internetItem->utility_internet_condition_id = $dataByType[$providerName]['utility_internet_condition_id'];
//                    $internetItem->internet_provider_id = $dataByType[$providerName]['internet_provider_id'];
//
//                    $internetTable->save($internetItem);
//                }
//                else {
//
//                    $entity = $internetTable->newEntity();
//
//                    $entity->institution_id =$institutionItem->id;
//                    $entity->academic_period_id = $this->AcademicPeriods->getCurrent();
//                    $entity->internet_purpose = $dataByType[$providerName]['internet_purpose'];
//                    $entity->utility_internet_bandwidth_id = $dataByType[$providerName]['utility_internet_bandwidth_id'];
//                    $entity->utility_internet_type_id = $dataByType[$providerName]['utility_internet_type_id'];
//                    $entity->utility_internet_condition_id = $dataByType[$providerName]['utility_internet_condition_id'];
//                    $entity->internet_provider_id = $dataByType[$providerName]['internet_provider_id'];
//
//                    $internetTable->save($entity);
//                }
//            }
//            else dump($item);
//
//        }
        return new Response();
    }
}