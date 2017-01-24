<?php

	namespace Kodols\MySQL\Builder;

	use \Kodols\MySQL\Builder\Insert;

	class Ignore extends Insert {

		protected $method = 'INSERT IGNORE INTO';
		protected $buildFormat = 'ignore';

	}
