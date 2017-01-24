<?php

	namespace Kodols\MySQL\Builder;

	use \Kodols\MySQL\Builder\Insert;

	class Replace extends Insert {

		protected $method = 'REPLACE';
		protected $buildFormat = 'replace';

	}
