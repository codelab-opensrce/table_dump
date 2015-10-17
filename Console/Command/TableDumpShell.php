<?php
/**
 * TableDump 0.1 CakePHP 2.x Plug-in
 * Copyright (c) Cake Codelab. (http://codelab.jp)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Codelab. (http://codelab.jp)
 * @link          http://codelab.jp
 * @since         TableDump 0.1 CakePHP Plug-in
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::import('Core', 'Controller');
App::import('Core', 'App');

/**
 * table import/export Shell Class
 * This Shell generates a CSV file form the database and imports the database from the CSV file.
 * CSV files are output to the APP\Config\Schema\export_{"model_name"}.csv .
 * 
 * Usage:
 * cake table_dump [import|export] [model_name] (options1 option2 ....)
 *
 * Options:
 * drop			Table drop before import.
 * conection [database_name]	database name. default is "default"
**/

class TableDumpShell extends AppShell {
	
	var $model_name = '';
	var $database_name	= 'default';
	var $table_drop		= false;
	var $force		= false;
	
	var $path		= '';
	
	var $max_record_num	=9999;
	
	function startup(){
		
		if(empty($this->args)){
			return ;
		}
		
		try{
			foreach($this->args as $key =>$arg){
				if($key==0){
					$this->model_name = $arg;
					continue;
				}
				if($arg=='drop'){
					$this->table_drop = true;
					continue;
				}
				if($arg=='conection'){
					$this->database_name='';
					continue;
				}
				if(empty($this->database_name)){
					$this->database_name = $arg;
					continue;
				}
				
				if($arg=='limit'){
					$this->max_record_num = 0;
					continue;
				}
				if(empty($this->max_record_num)){
					$this->max_record_num = $arg;
					continue;
				}
				
				if($arg=='force'){
					$this->force = true;
				}
			}
			if(empty($this->path)){
				$this->path = APP."Config".DS."Schema".DS;
			}
			$this->loadModel($this->model_name);
			$this->{$this->model_name}->recursive = -1;
			$this->{$this->model_name}->setDataSource($this->database_name);
		}catch (Exception $e) {
			$this->out("fatal error!!!");
			$this->out( $e->getMessage());
			return -1;
		}
	}
	public function main(){
		$this->out("This Shell generates a CSV file form the database and imports the database from the CSV file.");
		$this->out("");
		
		$this->out("\e[31m[Usage]\e[0m");
		$this->out("cake tabledump [params] [model_name] {option1 option2 ....}");
		$this->out("");
		
		$this->out("\e[31m[Params]\e[0m");
		$this->out("import,export");
		$this->out("");
				
		$this->out("\e[31m[Options]\e[0m");
		$this->out("drop : table drop before import.");
		$this->out("conection [database name]: database name. default is 'default'");
		$this->out("force : reply in YES to all questions.");
		$this->out("limit [num]: export records limit num.  default is 9999");

		$this->out("");
	}
	
	function import(){
		echo("import Model \"".$this->model_name."\" from export_".$this->model_name.".csv \r\n");
		$this->loadModel($this->model_name);
		$this->{$this->model_name}->recursive = -1;
		try{
			if($this->table_drop){
				//table drop Y/N
				if($this->force==false){
					$ret = $this->in("drop table ".$this->model_name." [Y/N]","y,Y,n,N","Y");
					if($ret=='Y' OR $ret =='y'){
						$this->out('drop table!!!');
						
						$this->{$this->model_name}->deleteAll(true);
					}else{
						$this->out('exit');
						return ;
					}
				}
			}
			
			$file_name = $this->path."export_".$this->model_name.".csv";
			$fp = fopen($file_name,"r");
			if(empty($fp)){
				throw new Exception('file open error!!\r\n');
			}
			$fields=array();
			while($line=fgetcsv($fp)){
				if(empty($fields)){
					// field name
					foreach($line as $key => $val){
						$fields[]=$val;
					}
					continue;
				}
				// data
				$data=array();
				foreach($fields as $key => $val){
					$data[$this->model_name][$val]=$line[$key];
				}
				$this->{$this->model_name}->set($data);
				if(!$this->{$this->model_name}->save()){
					throw new Exception('save error!!\r\n');
				}
				echo "*";
			}
			echo "\r\n";
			fclose($fp);
			
		}catch (Exception $e) {
			$this->out("fatal error!!!");
			$this->out( $e->getMessage());
			return -1;
		}
		echo "import ".$file_name."\r\n";
		echo "done.\r\n";
	}
	
	
	function export(){
		echo("export Model \"".$this->model_name."\" to export_".$this->model_name.".csv \r\n");
		try{
			$records = $this->{$this->model_name}->find('all',array('limit'=>$this->max_record_num));
			echo("record num ".count($records)."\r\n");
			
			if(empty($records)){
				echo ("no record!!\r\n");
				return true;
			}
			$file_name = $this->path."export_".$this->model_name.".csv";
			$fp = fopen($file_name,"w");
			if(empty($fp)){
				throw new Exception('file open error!!\r\n');
			}
			foreach($records as $index => $record){
				$line = $record[$this->model_name];
				echo "*";
				if($index==0){
					//export titles
					$tmp='';
					foreach($line as $key => $val){
						$tmp[]=$key;
					}
					fputcsv($fp,$tmp);
				}
				fputcsv($fp,$line);
			}
			echo "\r\n";
			fclose($fp);
			
		}catch (Exception $e) {
			$this->out("fatal error!!!");
			$this->out( $e->getMessage());
			return -1;
		}
		echo "output ".$file_name."\r\n";
		echo "done.\r\n";
	}
}
