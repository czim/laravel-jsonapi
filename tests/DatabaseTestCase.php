<?php
namespace Czim\JsonApi\Test;

use Illuminate\Foundation\Application;

abstract class DatabaseTestCase extends TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'testbench');

        $this->setDatabaseConnectionConfig($app);
    }

    /**
     * @param Application $app
     */
    protected function setDatabaseConnectionConfig($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->migrateDatabase();
        $this->seedDatabase();
    }


    protected function migrateDatabase(): void
    {
    }

    protected function seedDatabase(): void
    {
    }

}
