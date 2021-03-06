<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 **/

namespace Pdr\Ppm\Controller;

class Config extends \Pdr\Ppm\Cli\Controller {

	public function __construct() {
		parent::__construct();
	}

	public function commandIndex(){

		$config = new \Pdr\Ppm\ComposerConfig;
		$view = new \Pdr\Ppm\View\Config;

		$config->open();
		$view->open($config);
		$view->write();
	}

	public function commandSet(){

	}
}
