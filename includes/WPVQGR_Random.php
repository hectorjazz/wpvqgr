<?php 

class WPVQGR_Random 
{

	private $seed;

	function __construct()
	{
		if( !session_id()) {
        	session_start();
        }

        if (!isset($_SESSION['wpvqgr']['seed'])) {
        	$this->generateSeed();
        }
	}

	public function getSeed() {
		return $this->seed;
	}

	public function reset() {
		return $this->generateSeed();
	}

	private function generateSeed() 
	{
		$this->seed = rand(1,99999);
        $_SESSION['wpvqgr']['seed'] = $this->seed;
        return $this->seed;
	}

}

