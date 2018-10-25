<?php

namespace Kodols\MySQL;

use \Kodols\MySQL\Report\Database\Structure as databaseStructure;

trait Report
{

    public function reportDatabaseStructure()
    {
        $report = new databaseStructure;
        $report->attach($this);
        return $report->result();
    }

}