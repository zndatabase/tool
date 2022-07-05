<?php

namespace ZnDatabase\Tool\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnDatabase\Base\Console\Traits\OverwriteDatabaseTrait;
use ZnDatabase\Base\Domain\Facades\DbFacade;
use ZnDatabase\Base\Domain\Repositories\Eloquent\SchemaRepository;
use ZnDatabase\Eloquent\Domain\Factories\ManagerFactory;

class DropDatabaseCommand extends Command
{

    use OverwriteDatabaseTrait;

    protected static $defaultName = 'db:database:drop';
    private $capsule;
    private $schemaRepository;

    public function __construct(?string $name = null, SchemaRepository $schemaRepository)
    {
        $this->capsule = ManagerFactory::createManagerFromEnv();
        $this->schemaRepository = $schemaRepository;
        parent::__construct($name);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addOption(
                'withConfirm',
                null,
                InputOption::VALUE_REQUIRED,
                'Your selection migrations',
                true
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['<fg=white># Drop database</>']);

        if (!$this->isContinue($input, $output)) {
            return 0;
        }

        $connections = DbFacade::getConfigFromEnv();
        foreach ($connections as $connectionName => $connection) {
            $conn = $this->capsule->getConnection($connectionName);
            $tableList = $this->schemaRepository->allTables();
            //dd($tableList);

            /*$tableList = $conn->select('
                SELECT *
                FROM pg_catalog.pg_tables
                WHERE schemaname != \'pg_catalog\' AND schemaname != \'information_schema\';');*/
            $tables = [];
            $schemas = [];
            foreach ($tableList as $tableEntity) {
                $tableName = $tableEntity->getName();
                if ($tableEntity->getSchemaName()) {
                    $tableName = $tableEntity->getSchemaName() . '.' . $tableName;
                }
                //dd($tableEntity->getSchemaName());

                $tables[] = $tableName;
                if ($tableEntity->getSchemaName() && $tableEntity->getSchemaName() != 'public') {
                    $schemas[] = $tableEntity->getSchemaName();
                }
            }
//            dd($tables);
            if (empty($tables)) {
                $output->writeln(['', '<fg=yellow>Not found tables!</>', '']);
            } else {

                /*foreach ($tables as $t) {
                    $this->capsule->getSchemaByTableName($t)->dropIfExists($t);
                }*/

                $sql = 'DROP TABLE ' . implode(', ', $tables);
                $conn->statement($sql);
            }

            $schemaList = $conn->select('select nspname from pg_catalog.pg_namespace;');
            foreach ($schemaList as $schemaRecord) {
                if (strpos($schemaRecord->nspname, 'pg_') === false && !in_array($schemaRecord->nspname, ['information_schema', 'public'])) {
                    $sql = 'DROP SCHEMA ' . $schemaRecord->nspname;
                    $conn->statement($sql);
                }
            }
        }
        $output->writeln(['', '<fg=green>Drop database success!</>', '']);
        return 0;
    }
}
