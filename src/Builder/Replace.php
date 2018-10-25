<?php

namespace Kodols\MySQL\Builder;

use \Kodols\MySQL\Builder\Insert;

class Replace extends Insert
{

    protected $method = 'REPLACE INTO';
    protected $buildFormat = 'replace';

}
