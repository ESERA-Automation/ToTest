<?
class EseraDualDimmer extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("OWDID", 1);

		//Dimmer profile 0-31
        $this->CreateVariableProfile("ESERA.dimmer32", 2, " V", 0, 31, 1, 2, "");
        
		//Output for dimmer channels
        $this->RegisterVariableFloat("Output1", "Output 1", "ESERA.dimmer32");
		$this->RegisterVariableFloat("Output2", "Output 2", "ESERA.dimmer32");
		
		//Input for Push Button Input (manual control)
		for($i = 1; $i <= 4; $i++){
			$this->RegisterVariableBoolean("Input".$i, "Input ".$i, "~Switch");
		}


		$this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); //1Wire Controller
	}
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();

	}
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();

		//Apply filter
		$this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .".*");

	}
	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);
		$this->SendDebug("ESERA-DI8C", $data->Value, 0);

		if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
			if ($data->DataPoint == 1) {
				$value = intval($data->Value, 10);
				for ($i = 1; $i <= 4; $i++){
					SetValue($this->GetIDForIdent("Input".$i), ($value >> ($i-1)) & 0x01);
				}
			} else if ($data->DataPoint == 3) {
				$value = intval($data->Value, 10);
				for ($i = 1; $i <= 2; $i++){
					SetValue($this->GetIDForIdent("Output".$i), ($value >> ($i-1)) & 0x1);
				}
			}
		}
	}

	
	
	public function SetDualDimmer(int $OutputNumber, int $Value) {

		$OutputNumber = $OutputNumber - 1;
		$this->Send("SET,OWD,DIM,". $this->ReadPropertyInteger("OWDID") .",". $OutputNumber .",". $Value ."");
	}
	private function Send($Command) {

		//Zur 1Wire Coontroller Instanz senden
		return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

	}
}
?>
