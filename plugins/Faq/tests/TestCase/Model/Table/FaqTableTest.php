<?php
namespace Faq\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Faq\Model\Table\FaqTable;

/**
 * Faq\Model\Table\FaqTable Test Case
 */
class FaqTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Faq\Model\Table\FaqTable
     */
    public $Faq;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.faq.faq',
        'plugin.faq.modified_users',
        'plugin.faq.created_users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Faq') ? [] : ['className' => 'Faq\Model\Table\FaqTable'];
        $this->Faq = TableRegistry::get('Faq', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Faq);

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
