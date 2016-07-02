<?php
/*
	phpMyDomo : Home Automation Web Interface
	http://www.phpmydomo.org
	----------------------------------------------
	Copyright (C) 2016  Robert Blomdalen

	LICENCE: ###########################################################
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>
	#####################################################################
*/
/*
#########################################################################################
tdtool API ##############################################################################
#########################################################################################
*/

class PMD_ApiClient extends PMD_Root_ApiClient{


	//----------------------------------------------------------------------------------
	function ApiListInfos(){
		// (optionnal) used to get system infos, ie server date, etc...
		//$this->Debug('Infos',$this->infos);
	}


	//----------------------------------------------------------------------------------
	function ApiListDevices(){
		$cmd = 'tdtool';

		if($cmd=$this->conf['tdtool']['bin']){
			$this->parameters['cmd']="(Main Conf): $cmd";
		}

		$devices = shell_exec("$cmd -l");
		$devices = explode("\n", $devices);

		foreach($devices as $d) {
			if (empty($d)) continue;

			$d = explode(chr(9), $d);
			$d = (array) $d;
			$d['raw']       = $d;

			$d['id']   = $d[0];
			$d['address'] = sprintf('%s/%d', $this->_cleanLocation($d[1]), $d[0]);
			$d['class'] = 'command';
			$d['type']  = 'switch';
			$d['state'] = strtolower($d[2]);

			$this->RegisterDevice($d);
		}
		// $this->Debug('Devices',$this->devices);
	}

	function _cleanLocation($location) {
		$location = preg_replace('/[^A-Za-z0-9\-]/', '_', $location);
		return strtolower($location);
	}

	//----------------------------------------------------------------------------------
	//commands  : 'set' | 'list' (list is used only in your own ApiListDevices)
	//Set types : 'switch' | 'dimmer' | 'dim_level'
	//address   : (unique address of device, in your case this is "location/device")
	//states    : 'on' | 'off' | number (for dim_level)
	function ApiFetchCustom($command, $type='', $address='',$state=''){
		$result=false;
		$cmd = 'tdtool';

		if($cmd=$this->conf['tdtool']['bin']){
			$this->parameters['cmd']="(Main Conf): $cmd";
		}

		/*
$command: set
$type: switch
$address: bionaire/2
$state: off
{"status":"err","api_url":null,"api_response":false}
		*/

		//status -------------
		if($command=='list'){
			$out=json_decode('OK',true);
			if(is_array($out)){
				$this->api_status=true;
			}
			else{
				$this->api_status=false;
			}
		}
		elseif($command=='set'){
			$action = '-n';
			if ($state == 'off') {
				$action = '-f';
			}
			preg_match ('/\/(\d)+/', $address, $matches);
			if (isset($matches[1])) {
				$device_id = $matches[1];
				$result = shell_exec("$cmd $action $device_id");
			}
			if (preg_match('/Success/', $result)) {
				$this->api_status=true;
				return true;
			}
		}
		$this->api_status=false;
		return $out;
	}
}
?>
