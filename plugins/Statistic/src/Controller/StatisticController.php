<?php
namespace Statistic\Controller;

use App\Controller\AppController;
use Statistic\Model\Table;
use Cake\ORM\TableRegistry;
//use http\Client\Request;
use App\Controller\PageController;

/**
 * Employees Controller
 *
 * @property \Statistic\Model\Table\StatisticTable $Statistic
 */
//class StatisticController extends AppController
class StatisticController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $queryString = $this->request->query;
        if($queryString['api']==true){
            $employees = $this->Statistic;
            header('Content-type: application/json');
            switch ($queryString['operation']) {
                case 'by_region'        : echo json_encode($employees->getCountByRegion());         break;
                case 'by_district'      : echo json_encode($employees->getCountByDistricts());      break;
                case 'by_organization'  : echo json_encode($employees->getCountByOrganization());   break;
                case 'by_type'  : echo json_encode($employees->getTypeOrganization());              break;
                default                 : echo json_encode([]);                                     break;
            }

            die();
        }
        $employees = $this->Statistic;
        $this->set('lastUpdateStatistic', $employees->getLastUpdateStatistic() );
        $this->set('typeOrganizations',   $employees->getTypeOrganization() );

    }

    /**
     * View method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $employee = $this->EmployeesReport->get($id, [
            'contain' => []
        ]);

        $this->set('employee', $employee);
        $this->set('_serialize', ['employee']);
    }


    public function api() {
        $employees = $this->Statistic;
        $queryString = $this->request->query;

        header('Content-type: application/json');
        switch ($queryString['operation']) {
            case 'by_region'        : echo json_encode($employees->getCountByRegion());         break;
            case 'by_district'      : echo json_encode($employees->getCountByDistricts());      break;
            case 'by_organization'  : echo json_encode($employees->getCountByOrganization());   break;
            case 'by_type'  : echo json_encode($employees->getTypeOrganization());              break;
            default                 : echo json_encode([]);                                     break;
        }

        die();
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $employee = $this->Employees->newEntity();
        if ($this->request->is('post')) {
            $employee = $this->Employees->patchEntity($employee, $this->request->data);
            if ($this->Employees->save($employee)) {
                $this->Flash->success(__('The employee has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee could not be saved. Please, try again.'));
        }
        $this->set(compact('employee'));
        $this->set('_serialize', ['employee']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $employee = $this->Employees->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $employee = $this->Employees->patchEntity($employee, $this->request->data);
            if ($this->Employees->save($employee)) {
                $this->Flash->success(__('The employee has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The employee could not be saved. Please, try again.'));
        }
        $this->set(compact('employee'));
        $this->set('_serialize', ['employee']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Employee id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $employee = $this->Employees->get($id);
        if ($this->Employees->delete($employee)) {
            $this->Flash->success(__('The employee has been deleted.'));
        } else {
            $this->Flash->error(__('The employee could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
