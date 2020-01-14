<?php

declare(strict_types=1);

namespace Keboola\SnowflakeGrantAPI;

use Keboola\Db\Import\Snowflake\Connection;
use Keboola\SnowflakeGrantAPI\Exception\UserException;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use Monolog\Logger;

class App
{
    /** @var Client $storageApi */
    private $storageApi;

    /** @var Connection $snowflakeConnection */
    private $snowflakeConnection;

    /** @var Logger $logger */
    private $logger;

    private $projectSchemas = [
        6231 => [
            [
                'database' => 'ONDRA_TEST',
                'schema' => 'EXTRACTOR'
            ]
        ]
    ];

    public function __construct(Client $storageApi, Connection $snowflakeConnection, Logger $logger)
    {
        $this->storageApi = $storageApi;
        $this->snowflakeConnection = $snowflakeConnection;
        $this->logger = $logger;
    }

    public function grantPrivilegies(string $rolePrefix): void
    {
        $tokenDetail = $this->verifyToken();

        $projectId = $tokenDetail['owner']['id'];
        if (!isset($this->projectSchemas[$projectId])) {
            throw new UserException('Neni zadne schema pro tenhle token');
        }

        $roles = $this->snowflakeConnection->fetchAll(
            sprintf("SHOW ROLES LIKE '%s'", $rolePrefix . '%')
        );

        foreach ($roles as $role) {
            foreach ($this->projectSchemas[$projectId] as $projectSchema) {
                $sql = sprintf(
                    'GRANT usage ON SCHEMA "%s"."%s" to role "%s";',
                    $projectSchema['database'],
                    $projectSchema['schema'],
                    $role['name']
                );
                $this->snowflakeConnection->query($sql);
            }
        }
    }

    private function verifyToken(): array
    {
        try {
            $tokenDetail = $this->storageApi->verifyToken();
        } catch (ClientException $e) {
            throw new UserException($e->getMessage(), $e->getCode(), $e);
        }
        return $tokenDetail;
    }
}
