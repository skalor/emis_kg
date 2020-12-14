<?php
namespace Employees\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Employees\Model\Table\EmployeesReportTable;

/**
 * Employees\Model\Table\EmployeesReportTable Test Case
 */
class EmployeesReportTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Employees\Model\Table\EmployeesReportTable
     */
    public $EmployeesReport;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.employees.employees_report',
        'plugin.employees.area_levels'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('EmployeesReport') ? [] : ['className' => 'Employees\Model\Table\EmployeesReportTable'];
        $this->EmployeesReport = TableRegistry::get('EmployeesReport', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EmployeesReport);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
