<?php
namespace Czim\JsonApi\Test;

abstract class DatabaseTestCase extends TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'testbench');

        $this->setDatabaseConnectionConfig($app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setDatabaseConnectionConfig($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp()
    {
        parent::setUp();

        $this->migrateDatabase();
        $this->seedDatabase();
    }


    protected function migrateDatabase()
    {
    }

    protected function seedDatabase()
    {
    }

}
