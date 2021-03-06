<?php

// DEPRECATED CODE

class PackageConfig {

	public $data;

	public function __construct() {
		\Pdr\Ppm\Logger::deprecated(__CLASS__);
	}

	public function load() {
		if (is_null($this->data)) {
			$text = file_get_contents('composer.json');
			$this->data = json_decode($text, true);
		}
	}

	public function getPackages() {

		$this->load();

		$packages = array();
		if (isset($this->data['require']) == false) {
			return $packages;
		}

		foreach ($this->data['require'] as $packageName => $packageVersion) {
			$package = new \Package;
			$package->name = $packageName;
			$package->version = $packageVersion;
			$packages[] = $package;
		}

		return $packages;
	}
}

/**
 * DEPRECATED see \Pdr\Ppm\GlobalConfig
 **/

class ComposerConfigGlobal {

	public $data;

	public function __construct() {
		\Pdr\Ppm\Logger::deprecated(__CLASS__);
	}

	public function open(){

		$files = array(
			$_SERVER['HOME'].'/.composer/config.json'
		);

		$fileFound = false;
		$filePath = null;
		foreach ($files as $file){
			if (is_file($file) == false){
				continue;
			}
			$fileFound = true;
			$filePath = $file;
			break;
		}

		if (empty($fileFound)){
			\Logger::warn("Global composer config file is not found");
			return false;
		}

		if (($data = json_decode(file_get_contents($filePath))) == false){
			\Logger::warn("JSON parse fail");
			return false;
		}

		$this->data = $data;
		return true;
	}
}

class ComposerConfigLocal {

	public $data;
	public $file;

	public function __construct() {
		\Pdr\Ppm\Logger::deprecated(__CLASS__);
	}

	public function open(){

		$project = new \Project;

		$files = array(
			$project->getRootDir().'/composer.json'
		);

		$fileFound = false;
		$filePath = null;
		foreach ($files as $file){
			if (is_file($file) == false){
				continue;
			}
			$fileFound = true;
			$filePath = $file;
			break;
		}

		if (empty($fileFound)){
			\Logger::warn("Local composer config file is not found");
			return false;
		}

		if (($data = json_decode(file_get_contents($filePath))) == false){
			\Logger::warn("JSON parse fail");
			$this->data = new \stdClass;
			return false;
		}

		$this->file = $filePath;
		$this->data = $data;

		return true;
	}

	public function addPackage(\Package $package){
		$packageName = $package->name;
		$attributeRequireName = 'require';
		if ($package->versionType == 'branch'){
			$this->data->$attributeRequireName->$packageName = 'dev-'.$package->branchName;
		} else {
			throw new \Exception("Unsupported versionType: {$package->versionType}");
		}

	}

	public function save(){
		$temp = tempnam(sys_get_temp_dir(), 'php.');
		$text = json_encode($this->data);
		file_put_contents($this->file, $text);
		\Console::exec("json_pp < ".$this->file." > $temp");
		$text = file_get_contents($temp);
		file_put_contents($this->file, $text);
		unlink($temp);
	}
}

class ComposerLock {

	public $data;
	public $file;

	public function __construct() {
		\Pdr\Ppm\Logger::deprecated(__CLASS__);
	}

	public function open(){

		$project = new \Project;

		$files = array(
			$project->getRootDir().'/composer.lock'
		);

		$fileFound = false;
		$filePath = null;
		foreach ($files as $file){
			if (is_file($file) == false){
				continue;
			}
			$fileFound = true;
			$filePath = $file;
			break;
		}

		if (empty($fileFound)){
			\Logger::warn("Composer lock file is not found");
			return false;
		}

		$data = json_decode(file_get_contents($filePath));
		if (is_null($data)){
			\Logger::warn("JSON parse fail");
			$this->data = new \stdClass;
			$this->data->packages = array();
			return false;
		}

		$this->file = $filePath;
		$this->data = $data;
		return true;
	}

	public function addPackage($packageName, $packageCommitHash){

		$data = new \stdClass;
		$data->name = $packageName;
		$data->source = new \stdClass;
		$data->source->type = 'cvs';
		$data->source->reference = $packageCommitHash;

		$packageFound = false;
		foreach ($this->data->packages as $packageIndex => $packageItem){
			if ($packageItem->name == $packageName){
				$this->data->packages[$packageIndex] = $data;
				$packageFound = true;
				break;
			}
		}

		if ($packageFound == false){
			$this->data->packages[] = $data;
		}
	}

	public function save(){

		$data = new \stdClass;
		$packages = array();

		foreach ($this->data->packages as $package){
			$packageInfo = new \stdClass;
			$packageInfo->name = $package->name;
			$packageInfo->source = new \stdClass;
			if (empty($package->source->type)){
				$package->source->type = 'cvs';
			}
			$packageInfo->source->type = $package->source->type;
			$packageInfo->source->reference = $package->source->reference;
			$packages[$package->name] = $packageInfo;
		}

		ksort($packages);
		$packages = array_values($packages);

		$data->packages = $packages;

		$text = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		file_put_contents($this->file, $text);
	}
}

class ComposerCommand {

	protected $command;

	public function __construct() {
		\Pdr\Ppm\Logger::deprecated(__CLASS__);
		$this->command = $_SERVER['HOME'].'/bin/composer';
	}

	public function createAutoload(){
		\Console::exec($this->command.' dump-autoload');
	}
}

class Project {

	public function getRootDir(){
		return getcwd();
	}

	public function getVendorDir(){
		return getcwd().'/vendor';
	}

	public function getPackages(){
		$config = new \PackageConfig;
		$packages = $config->getPackages();
		return $packages;
	}
}

class Package {

	public $name;
	public $branchName;
	public $version;
	public $versionType;
	public $remoteUrl;

	public function __construct() {
		\Pdr\Ppm\Logger::deprecated(__CLASS__);
	}

	public function printStatus() {

		if ($this->name == 'php') {
			return TRUE;
		}

		$command = $this->getGitCommand().' status --short';

		$text = \Console::text($command);
		if (empty($text) == false) {
			echo("M  vendor/".$this->name."\n");
			return true;
		}

		$gitRemoteCommit = '';
		$command = $this->getGitCommand().' ls-remote origin '.$this->getCurrentBranch();
		$text = \Console::text($command);
		if (empty($text) == false) {
			$item = explode("\t", $text);
			if (count($item) == 2) {
				$gitRemoteCommit = $item[0];
			}
		}

		$gitCurentCommit = $this->getCurrentCommit();
		if ($gitCurentCommit == $gitRemoteCommit) {
			return true;
		}

		echo(' M vendor/'.$this->name."\n");
	}

	protected function getGitCommand() {
		$workTree = 'vendor/'.$this->name;
		$gitDir = 'vendor/'.$this->name.'/.git';
		$command = 'git --git-dir='.$gitDir.' --work-tree='.$workTree;
		return $command;
	}

	public function getCurrentBranch() {
		$command = $this->getGitCommand().' rev-parse --abbrev-ref HEAD';
		return \Console::text($command);
	}

	public function getCurrentCommit() {
		$command = $this->getGitCommand().' log -n 1 --format=%H HEAD';
		return \Console::text($command);
	}

	/**
	 * Update package from remotw
	 **/

	public function update(){

		// get remote commit

		$command = $this->getGitCommand();
		$command .= ' ls-remote '.$this->remoteUrl.' refs/heads/'.$this->branchName;
		$line  = \Console::text($command);

		if (empty($line)){
			\Logger::error("Remote {$this->remoteUrl} does not have branch {$this->branchName}");
		}

		if (preg_match("/^([0-9a-f]{40,40})\s+/",$line,$match) == false){
			throw new \Exception("Parse error");
		}

		$remoteCommit = $match[1];

		// get local commit

		$localCommit = $this->getCurrentCommit();

		if ($remoteCommit != $localCommit){

			\Logger::debug('Remote has different commit hash');

			if ($this->getStatus() == 1){
				\Logger::warn('Update failed, local repository has changes.');
				return false;
			} else {

				// fetch remote refs

				$command = $this->getGitCommand();
				$command .= ' fetch '.$this->remoteUrl.' refs/heads/'.$this->branchName;
				\Console::exec($command);

				// merge commit

				$command = $this->getGitCommand();
				$command .= ' merge '.$remoteCommit;
				\Console::exec($command);
			}
		}

		return true;
	}

	/**
	 * Get status
	 *  0: no change 1: has changes
	 **/

	public function getStatus(){
		$command = $this->getGitCommand().' status --short';
		$output  = \Console::text($command);
		if (empty($output)){
			return 0;
		}
		return 1;
	}

	public function exist(){

		if (	is_dir('vendor/'.$this->name)
			&&	is_dir('vendor/'.$this->name.'/.git')
			){
			return true;
		}

		return false;
	}

	public function create(){

		$dir = 'vendor/'.$this->name;

		$parentDir = dirname($dir);
		if (is_dir($parentDir) == false){
			mkdir($parentDir);
		}
		if (is_dir($dir) == false){
			mkdir($dir);
		}

		\Logger::debug("Install package {$this->name} ..");

		$command = 'git init '.$dir;
		\Console::exec($command);

		$command = $this->getGitCommand().' remote add composer '.$this->remoteUrl;
		\Console::exec($command);

		$command = $this->getGitCommand().' remote add origin '.$this->remoteUrl;
		\Console::exec($command);

		$command = $this->getGitCommand().' fetch origin '.$this->branchName;
		\Console::exec($command);

		$command = $this->getGitCommand().' checkout '.$this->version.' -b '.$this->branchName;
		\Console::exec($command);
	}

	public function getVersionText(){
		if ($this->versionType == 'branch'){
			return 'dev-'.$this->version;
		} else {
			throw new \Exception("Unsupported versionType: {$this->versionType}");
		}
	}

}
