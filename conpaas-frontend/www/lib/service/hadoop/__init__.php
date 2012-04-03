<?php
/*
 * Copyright (C) 2010-2011 Contrail consortium.
 *
 * This file is part of ConPaaS, an integrated runtime environment
 * for elastic cloud applications.
 *
 * ConPaaS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * ConPaaS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ConPaaS.  If not, see <http://www.gnu.org/licenses/>.
 */

require_module('cloud');
require_module('service');
require_module('ui/instance/hadoop');

class HadoopService extends Service {

	public function __construct($data, $manager) {
		parent::__construct($data, $manager);
	}

	public function hasDedicatedManager() {
		return true;
	}

	public function sendConfiguration($params) {
		// we ignore this for now
		return '{}';
	}

	public function fetchHighLevelMonitoringInfo() {
		return false;
	}

	public function fetchStateLog() {
		return array();
	}

	public function needsPolling() {
		return parent::needsPolling() || $this->state == self::STATE_INIT;
	}

	public function getInstanceRoles() {
		return array('masters', 'workers');
	}

	public function getAccessLocation() {
		$master_node = $this->getNodeInfo($this->nodesLists['masters'][0]);
		return 'http://'.$master_node['ip'];
	}

	public function createInstanceUI($node) {
		$info = $this->getNodeInfo($node);
		if ($this->nodesLists !== false) {
			foreach ($this->nodesLists as $role => $nodesList) {
				if (in_array($info['id'], $nodesList)) {
					$info[$role] = 1;
				}
			}
		}
		return new HadoopInstance($info);
	}

	public static function getNamenodeAttr($info, $name) {
		preg_match('/'.$name.'\<td id="col2"> :<td id="col3"> ([^\<]+)\</',
			$info, $matches);
		return $matches[1];
	}

	public function getNamenodeData() {
		$master_addr = $this->getAccessLocation();
		$info = file_get_contents($master_addr.':50070/dfshealth.jsp');
		$capacity = self::getNamenodeAttr($info, 'Configured Capacity');
		$used = self::getNamenodeAttr($info, 'DFS Used');
		return array(
			'capacity' => $capacity,
			'used' => $used,
		);
	}

}

?>