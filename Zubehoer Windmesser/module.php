<?
class EseraWindmesser extends IPSModule {
	
    public function Create(){
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("CounterID", 0);
        $this->RegisterPropertyInteger("Impulses", 4);

        $this->RegisterVariableInteger("Counter_delta", "Counter_Delta", "", 1);
		$this->RegisterVariableInteger("Counter_alt", "Counter_Alt", "", 2);
        $this->RegisterVariableFloat("Wind_kmh", "Windspeed km/h", "~WindSpeed.kmh", 10);
        $this->RegisterVariableFloat("Wind_ms", "Windspeed m/s", "~WindSpeed.ms", 20);

        $this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounter($_IPS[\'TARGET\']);');		//Modultimer
		
		//Windspeed max
        $this->RegisterVariableFloat("Wind_kmh_max", "Windspeed km/h max Day", "~WindSpeed.kmh", 40);
        $this->RegisterVariableInteger("Wind_kmh_max_Zeit", "Windspeed km/h max Time", "~UnixTimestamp", 50);
		$this->RegisterTimer("DailyReset", 0, 'ResetWindspeedmaxDaily($_IPS[\'TARGET\']);');
		
		//Mittelwertberechnung
        $this->RegisterVariableFloat("Wind_kmh_slow", "Windspeed km/h average", "~WindSpeed.kmh", 30);
		
		$this->SetBuffer("intern1", 0);
		$this->SetBuffer("intern2", 0);
		$this->SetBuffer("intern3", 0);
		$this->SetBuffer("intern4", 0);
		$this->SetBuffer("interncount", 0);

    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
    }
	
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
        $this->SetTimerInterval("Refresh", 30 * 1000);
    }
	
    public function ReceiveData($JSONString) {
        // not implemented
    }
	
    public function RefreshCounter(){
       $this->calculate();
    }
	
	public function ResetPowerMeterDaily(){
        $this->SetDailyTimerInterval();
        SetValue($this->GetIDForIdent("Wind_kmh_max"), 0);
        SetValue($this->GetIDForIdent("Wind_kmh_max_Zeit"), 0);
    }

    private function Calculate(){
<<<<<<< HEAD
		//Windspeed-Berechnung
		//$CounterOld = GetValue(Counter_alt);
		$CounterOld = GetValue($this->ReadPropertyInteger("Counter_alt"));
        $CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
        
		if ($CounterNew > $CounterOld)
		{
=======
		//Windspeed berechnung
		$CounterOld = GetValue($this->GetIDForIdent("Counter"));
        $CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
        if ($CounterNew > $CounterOld){
>>>>>>> origin/master
			$delta = $CounterNew - $CounterOld;
			$Factor = $this->GetFactor($this->ReadPropertyInteger("Impulses"));
			$delta_Wind = ((($delta / $Factor) * 3600) / 1000);
			$delta_Wind_ms = $delta / $Factor;
<<<<<<< HEAD
			
		}
		else
		{
			//SetValue($this->GetIDForIdent("Counter"), $delta);	//wenn der alte counterwert grösser als der neue counterwert ist, überschreibe den alten Counterwert
			$delta = 0;
			$delta_Wind = 0;
			$delta_Wind_ms = 0;				
		}
		
		SetValue($this->GetIDForIdent("Counter_alt"), $CounterNew);
		SetValue($this->GetIDForIdent("Counter_delta"), $delta);      
=======
		}
		else{
			SetValue($this->GetIDForIdent("Counter"), $CounterNew);	//wenn der alte counterwert grösser als der neue counterwert ist, überschreibe den alten Counterwert
		}
		//$this->SetBuffer("counter", $CounterNew);
        SetValue($this->GetIDForIdent("Counter"), $CounterNew);
>>>>>>> origin/master
        SetValue($this->GetIDForIdent("Wind_kmh"), $delta_Wind);
        SetValue($this->GetIDForIdent("Wind_ms"), $delta_Wind_ms);

		// Windspeed max
        $windspeedmax = GetValue($this->GetIDForIdent("Wind_kmh_max"));
        if ($delta_Wind > $windspeedmax)
		{
            SetValue($this->GetIDForIdent("Wind_kmh_max"), $delta_Wind);
            SetValue($this->GetIDForIdent("Wind_kmh_max_Zeit"), time());
        }
		
		//Mittelwertberechnung
		//$windspeedslow = GetValue($this->GetIDForIdent("Wind_kmh_slow"));
		$intern_0 = $delta_Wind;
		$intern_1 = $this->Getbuffer("intern1");
		$intern_2 = $this->Getbuffer("intern2");
		$intern_3 = $this->Getbuffer("intern3");
		$intern_4 = $this->Getbuffer("intern4");
		$interncount = $this->Getbuffer("interncount");
	
		$windspeedslow = $intern_0+$intern_1+$intern_2+$intern_3+$intern_4;			//Mittelwert aufsummieren
		$windspeedslow = $windspeedslow / 5;										//Mittelwert berechnen
		
		$interncount = $interncount +1;												//Zähler für Mittelwertausgabe
<<<<<<< HEAD
		
=======
>>>>>>> origin/master
		if ($interncount >= 5 ){													//Datenausgabe Mittelwert nach 5 neuen Werten
			$interncount = 5;
			SetValue($this->GetIDForIdent("Wind_kmh_slow"), $windspeedslow);	    //Mittelwert ausgebe in Variable	
		}
<<<<<<< HEAD
		
=======
>>>>>>> origin/master
		$this->SetBuffer("interncount", $interncount);
		$this->SetBuffer("intern1", $intern_0);
		$this->SetBuffer("intern2", $intern_1);
		$this->SetBuffer("intern3", $intern_2);			
		$this->SetBuffer("intern4", $intern_3);
<<<<<<< HEAD
		
        // Only for debugging
        $this->DebugMessage("Counter", "Counter_Alt: " . $CounterOld);
=======

		
        // Only for debugging
        $this->DebugMessage("Counter", "CounterOld: " . $CounterOld);
>>>>>>> origin/master
        $this->DebugMessage("Counter", "CounterNew: " . $CounterNew);
        $this->DebugMessage("Counter", "Delta: " . $delta);
        $this->DebugMessage("Counter", "Delta Wind: " . $delta_Wind);
        $this->DebugMessage("Counter", "Delta Wind ms: " . $delta_Wind_ms);
		$this->DebugMessage("Counter", "interncount: " . $interncount);
<<<<<<< HEAD
=======

>>>>>>> origin/master
    }
	
	public function CallFloat(float $Value) {
		    SetValue($this->GetIDForIdent("Wind_kmh_max"), 0);
			SetValue($this->GetIDForIdent("Wind_kmh_max_Zeit"), 0);
			//echo "Reset Windspeed max";
		}
	
    private function GetFactor($Impulses){
        switch ($Impulses){
            case 2:
              return (35);
            break;

            case 4:
              return (70);
            break;
        }
    }
	
    private function DebugMessage($Sender, $Message){
        $this->SendDebug($Sender, $Message, 0);
    }
}
?>
