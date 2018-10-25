<?php

namespace Kodols\MySQL\Report\Database;

use \Kodols\MySQL\Server;

class Structure
{

    private $server;
    private $configuration;

    public function attach(Server $server)
    {
        $this->server = $server;
        $this->configuration = $server->getConfiguration();
        return $this;
    }

    private $result = null;

    public function result($fresh = false)
    {
        if ($this->result !== null) {
            if (!$fresh) {
                return $this->result;
            }
        }

        $info = [];
        $info['size'] = [
            'bytes' => 0,
            'mb' => 0
        ];
        $info['rows'] = 0;
        $info['name'] = $this->configuration->getDatabase();

        $tables = [];

        foreach ($this->server->build('select')
                     ->column('ist.DATA_LENGTH')
                     ->column('ist.INDEX_LENGTH')
                     ->column('ist.TABLE_ROWS')
                     ->column('ist.TABLE_NAME')
                     ->column('ist.CREATE_TIME')
                     ->from('information_schema.TABLES', 'ist')
                     ->where('TABLE_SCHEMA', '=', $this->configuration->getDatabase())
                     ->execute()
                     ->fetchAll() as $ist) {
            $table = [];
            $table['size'] = [];
            $table['size']['bytes'] = $ist->DATA_LENGTH + $ist->INDEX_LENGTH;
            $table['size']['mb'] = @round(($table['size']['bytes'] / 1024 / 1024), 2);
            $table['rows'] = $ist->TABLE_ROWS;
            $table['name'] = $ist->TABLE_NAME;
            $table['created'] = strtotime($ist->CREATE_TIME);

            $info['size']['bytes'] += $table['size']['bytes'];
            $info['rows'] += $table['rows'];

            $tables[] = $table;
        }

        $info['size']['mb'] = @round(($info['size']['bytes'] / 1024 / 1024), 2);

        $this->result = [
            'database' => $info,
            'tables' => $tables
        ];

        return $this->result;
    }

}