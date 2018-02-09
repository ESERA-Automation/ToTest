<?
// ToDo:
// - Check TimerDiff
// - Profil Brightness erstellen
// - Action Brightness erstellen
// - Message Sink - Unregister unneeded messages

class Rolladensteuerung extends IPSModule {
  public function Create(){
    //Never delete this line!
    parent::Create();
    //These lines are parsed on Symcon Startup or Instance creation
    //You cannot use variables here. Just static values.

    $this->CreateVariableProfile("SIB.Helligkeit", 1, " lx", 0, 10000, 1, 0, "Sun");

    $this->RegisterPropertyInteger("ShutterID", 0);
    $this->RegisterPropertyInteger("Interval", 300);
    $this->RegisterPropertyBoolean("Emulate", 1);
    $this->RegisterPropertyBoolean("EmulationReset", 1);
    $this->RegisterPropertyBoolean("deactivateManualMove", 0);
    $this->RegisterPropertyBoolean("ShowTrigger", 0);
    $this->RegisterPropertyInteger("NormalState", 1);
    $this->RegisterPropertyInteger("MovementDelay", 600);
    $this->RegisterPropertyBoolean("ScheduledMovement", FALSE);
    $this->RegisterPropertyInteger("BrightnessID", 0);
    $this->RegisterPropertyInteger("BrightnessThreshold", 600);
    $this->RegisterPropertyBoolean("BrightnessDetection", FALSE);
    $this->RegisterPropertyInteger("DoorID", 0);
    $this->RegisterPropertyBoolean("DoorDetection", FALSE);
    $this->RegisterPropertyBoolean("deactivateDoor", 0);
    $this->RegisterPropertyInteger("WindID", 0);
    $this->RegisterPropertyBoolean("WindDetection", FALSE);
    $this->RegisterPropertyInteger("WindAction", 1);
    $this->RegisterPropertyBoolean("WindAtOff", 1);
    $this->RegisterPropertyInteger("RainID", 0);
    $this->RegisterPropertyBoolean("RainDetection", FALSE);
    $this->RegisterPropertyInteger("RainAction", 1);
    $this->RegisterPropertyBoolean("RainAtOff", 1);
    $this->RegisterVariableBoolean("State", "State", "~Switch", 1);
    $this->RegisterPropertyInteger("DirectionExceedThreshold", 2);
    $this->RegisterPropertyBoolean("SunAzimuthDetection", FALSE);
    $this->RegisterPropertyInteger("SunAzimuthID", 0);
    $this->RegisterPropertyInteger("SunAzimuthDown", 0);
    $this->RegisterPropertyInteger("SunAzimuthUp", 0);
    $this->RegisterPropertyBoolean("ExternalBrightnessDetection", FALSE);
    $this->RegisterPropertyInteger("ExternalBrightnessTriggerDirection", 1);
    $this->RegisterPropertyInteger("ExternalBrightnessTriggerID", 0);
    $this->RegisterPropertyBoolean("ImmediatelyBrightnessDetection", FALSE);
    $this->RegisterPropertyBoolean("ExternalStateTrigger", FALSE);
    $this->RegisterPropertyInteger("ExternalStateTriggerID", 0);

    $this->EnableAction("State");

    $this->RegisterTimer("CheckMoveTrigger", $this->ReadPropertyInteger("Interval"), 'SIB_CheckMoveTrigger($_IPS[\'TARGET\']);');
    $this->RegisterTimer("EmulationResetTimer", 0, 'SIB_EmulationReset($_IPS[\'TARGET\']);');
    $this->RegisterMessage($this->GetIDForIdent("State"), 10603 /* VM_UPDATE */);
  }
  public function Destroy(){
    //Never delete this line!
    parent::Destroy();
  }
  public function ApplyChanges(){
    //Never delete this line!
    parent::ApplyChanges();

    // Timer
    // CheckMoveTrigger
    $this->SetTimerInterval("CheckMoveTrigger", $this->ReadPropertyInteger("Interval") * 1000);

    // Emulation Reset
    if ($this->ReadPropertyBoolean("EmulationReset") && $this->ReadPropertyBoolean("Emulate")){
      $this->SetEmulationResetTimer(1);
    }
    else{
      $this->SetEmulationResetTimer(0);
    }

    // Schedule for Auto Up or Auto Down
    if ($this->ReadPropertyBoolean("ScheduledMovement") && !@IPS_GetObjectIDByIdent("Schedule", $this->InstanceID)){
      $Event = IPS_CreateEvent(2);
      IPS_SetParent($Event, $this->InstanceID);
      IPS_SetIdent($Event, "Schedule");
      IPS_SetEventScheduleGroup($Event, 0, 31);
      IPS_SetEventScheduleGroup($Event, 1, 96);
      IPS_SetEventScheduleAction($Event, 1, "Up", 0xFF0000, "SIB_Schedule(\$_IPS['TARGET'], 1);");
      IPS_SetEventScheduleAction($Event, 2, "Down", 0x0000FF, "SIB_Schedule(\$_IPS['TARGET'], 2);");
      IPS_SetEventScheduleGroupPoint($Event, 0, 1, 0, 0, 0, 2);
      IPS_SetEventScheduleGroupPoint($Event, 0, 2, 7, 0, 0, 1);
      IPS_SetEventScheduleGroupPoint($Event, 0, 3, 20, 0, 0, 2);
      IPS_SetEventScheduleGroupPoint($Event, 1, 1, 0, 0, 0, 2);
      IPS_SetEventScheduleGroupPoint($Event, 1, 2, 9, 0, 0, 1);
      IPS_SetEventScheduleGroupPoint($Event, 1, 3, 22, 0, 0, 2);
      IPS_SetName($Event, "Schedule");
      IPS_SetPosition($Event, 4);

      if ($this->GetIDForIdent("State")){
        IPS_SetEventActive($Event, TRUE);
      }
    }

    // BrightnessDetection
    if ($this->ReadPropertyBoolean("BrightnessDetection") && $this->ReadPropertyInteger("BrightnessID") != 0 && $this->ReadPropertyBoolean("ImmediatelyBrightnessDetection") !=0){
      $this->RegisterMessage($this->ReadPropertyInteger("BrightnessID"), 10603 /* VM_UPDATE */);
      $this->RegisterVariableInteger("BrightnessThreshold", "Brightness Threshold", "SIB.Helligkeit", 2);
      $this->EnableAction("BrightnessThreshold");
      if (GetValue($this->GetIDForIdent("BrightnessThreshold")) == 0){
        SetValue($this->GetIDForIdent("BrightnessThreshold"), 1000);
      }
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("BrightnessID"), 10603 /* VM_UPDATE */);
    }

    // external Brightness Detection
    if ($this->ReadPropertyBoolean("ExternalBrightnessDetection") && $this->ReadPropertyInteger("ExternalBrightnessTriggerID") != 0){
      $this->RegisterMessage($this->ReadPropertyInteger("ExternalBrightnessTriggerID"), 10603 /* VM_UPDATE */);
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("ExternalBrightnessTriggerID"), 10603 /* VM_UPDATE */);
    }

    // SunAzimuthDetection
    if ($this->ReadPropertyBoolean("SunAzimuthDetection") && $this->ReadPropertyInteger("SunAzimuthID") != 0){
      $this->RegisterMessage($this->ReadPropertyInteger("SunAzimuthID"), 10603 /* VM_UPDATE */);
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("SunAzimuthID"), 10603 /* VM_UPDATE */);
    }

    // External State Trigger
    if ($this->ReadPropertyBoolean("ExternalStateTrigger") && $this->ReadPropertyInteger("ExternalStateTriggerID") != 0){
      $this->RegisterMessage($this->ReadPropertyInteger("ExternalStateTriggerID"), 10603 /* VM_UPDATE */);
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("ExternalStateTriggerID"), 10603 /* VM_UPDATE */);
    }

    // DoorDetection
    if ($this->ReadPropertyBoolean("DoorDetection") && $this->ReadPropertyInteger("DoorID") != 0){
      $this->RegisterMessage($this->ReadPropertyInteger("DoorID"), 10603 /* VM_UPDATE */);
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("DoorID"), 10603 /* VM_UPDATE */);
    }

    // RainDetection
    if ($this->ReadPropertyBoolean("RainDetection") && $this->ReadPropertyInteger("RainID") != 0){
      $this->RegisterMessage($this->ReadPropertyInteger("RainID"), 10603 /* VM_UPDATE */);
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("RainID"), 10603 /* VM_UPDATE */);
    }

    // WindDetection
    if ($this->ReadPropertyBoolean("WindDetection") && $this->ReadPropertyInteger("WindID") != 0){
      $this->RegisterMessage($this->ReadPropertyInteger("WindID"), 10603 /* VM_UPDATE */);
    }
    else{
      $this->UnregisterMessage($this->ReadPropertyInteger("WindID"), 10603 /* VM_UPDATE */);
    }

     // Show Trigger in String Variable
     if ($this->ReadPropertyBoolean("ShowTrigger")){
       $this->RegisterVariableString("Trigger", "Last Trigger", "", 3);
     }
   }
   public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
     // $this->LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));

     switch ($SenderID){
       case $this->GetIDForIdent("State"):
         $NewState = GetValue($this->GetIDForIdent("State"));
         $this->LogMessage("MessageSink", "Change State detected", 0);
         $this->CheckMoveTrigger();
         $this->SetScheduleState($NewState);

         if ($NewState == 0){
           $this->SetBuffer("SleepMode", 0);
           $this->SetBuffer("Direction", 0);
           $this->SetBuffer("LastMove", 0);
           $this->LogMessage("MessageSink", "Disabling SleepMode");
         }
       break;

       case $this->ReadPropertyInteger("BrightnessID"):
         $this->LogMessage("MessageSink", "Change Brightness detected");
         $this->CheckMoveTrigger();
       break;

       case $this->ReadPropertyInteger("ExternalBrightnessTriggerID"):
         $this->LogMessage("MessageSink", "Change External BrightnessT rigger");
         $this->CheckMoveTrigger();
       break;

       case $this->ReadPropertyInteger("SunAzimuthID"):
         $this->LogMessage("MessageSink", "Change Sun Azimuth detected");
         $this->CheckMoveTrigger();
       break;

       case $this->ReadPropertyInteger("ExternalStateTriggerID"):
         $this->LogMessage("MessageSink", "Change external State Trigger detected");
         SetValue($this->GetIDForIdent("State"), GetValue($this->ReadPropertyInteger("ExternalStateTriggerID")));
       break;

       case $this->ReadPropertyInteger("DoorID"):
         $this->LogMessage("MessageSink", "Change Door detected");
         $this->CheckMoveTrigger();
       break;

       case $this->ReadPropertyInteger("RainID"):
         $this->LogMessage("MessageSink", "Change Rain detected");
         $this->CheckMoveTrigger();
       break;

       case $this->ReadPropertyInteger("WindID"):
         $this->LogMessage("MessageSink", "Change Wind detected");
         $this->CheckMoveTrigger();
       break;

       case $this->InstanceID:
         $this->LogMessage("MessageSink", "Instance has new name!");
       break;

       default:
         $this->LogMessage("MessageSink", "unsupported MessageSing detected:" . $SenderID);
       break;
      }
    }
    public function RequestAction($Ident, $Value) {
      SetValue($this->GetIDForIdent($Ident), $Value);
    }
    public function ReceiveData($JSONString) {
      $this->LogMessage("Data", $JSONString, 0);
      $data = json_decode($JSONString);
      $Type = $data->{'Type'};
      $Group = $data->{'Group'};
      $Parameter = $data->{'Parameter'};
      $Value = $data->{'Value'};
      $this->LogMessage("ReceivedData", $Type . " " . $Group . " " . $Parameter . " " . $Value);

      switch ($Type){
        // Type 1 = Property verändern
        case 1:
          IPS_SetProperty($this->InstanceID, $Parameter, $Value);
          IPS_ApplyChanges($this->InstanceID);
        break;

        // Type 2 = Variable verändern
        case 2:
          SetValue($this->GetIDForIdent($Parameter), $Value);
        break;
      }
    }
    public function Schedule(int $Value){
      switch($Value){
        case 1:
          $this->LogMessage("Schedule", "Sleep Mode is now 0. Go into CheckMoveTrigger");
          $this->SetBuffer("Trigger", "Schedule Move Up");
          $this->SetBuffer("SleepMode", 0);
          $this->SetBuffer("Direction", 0);
          $this->CheckMoveTrigger();
        break;

        case 2:
          $this->LogMessage("Schedule", "Move Down. Sleep Mode is now 1");
          $this->SetBuffer("Trigger", "Schedule Move Down");
          $this->Move(2);
          $this->SetBuffer("SleepMode", 1);
        break;
      }
    }
    public function CheckMoveTrigger(){
      // return 1 to move up
      // return 2 to move up
      // return 0 for no changes
      //
      // Priority:
      // SleepMode - Door - Wind - Rain - State - Sun - External Brightness Trigger - Brightness

      $this->LogMessage("Rolladensteuerung", "Rolladen: " . IPS_GetName($this->InstanceID));
      $this->SetBuffer("NormalState", 0);

      // SleepMode
      $SleepMode = $this->CheckSleepMode();
      if ($SleepMode){
        $this->LogMessage("CheckMove", "SleepMode is active, do nothing");
        return;
      }

      // Door
      if ($this->ReadPropertyBoolean("DoorDetection")){
        $Door = $this->CheckDoor();
        if ($Door){
          $this->LogMessage("CheckMoveTrigger", "Door is open. Move Shutter Up");
          $this->SetBuffer("Trigger", "Door Open");
          $this->Move(1);
          return 1;
        }
        else{
          $this->LogMessage("CheckMoveTrigger", "Door is closed");
        }
      }

      // Wind
      if ($this->ReadPropertyBoolean("WindDetection")){
        $Wind = $this->CheckWind();
        if ($Wind){
          $this->LogMessage("CheckMoveTrigger", "Wind Alarm. Move Shutter");
          $this->SetBuffer("Trigger", "Wind");
          $Direction = $this->ReadPropertyInteger("WindAction");

          // Check if we had to move at "RainAtOff"
          if ((!$this->CheckState() && $this->ReadPropertyBoolean("WindAtOff")) || $this->CheckState()){
            $this->CheckMove($Direction);
            return $Direction;
          }
          else{
            $this->LogMessage("CheckMoveTrigger", "It's windig. Do not Move because WindAtOff is not active!");
          }
        }
        else{
          $this->LogMessage("CheckMoveTrigger", "No Wind");
          if (!$this->ReadPropertyBoolean("BrightnessDetection") || !$this->ReadPropertyBoolean("SunAzimuthDetection") || $this->ReadPropertyInteger("BrightnessID") == 0){
            $this->SetBuffer("NormalState", 1);
            $this->LogMessage("CheckMoveTrigger", "Wind Check says: NormalState");
          }
        }
      }

      // Rain
      if ($this->ReadPropertyBoolean("RainDetection")){
        $Rain = $this->CheckRain();
        if ($Rain){
          $this->LogMessage("CheckMoveTrigger", "It's raining. Move Shutter");
          $this->SetBuffer("Trigger", "Rain");
          $Direction = $this->ReadPropertyInteger("RainAction");

          // Check if we had to move at "RainAtOff"
          if ((!$this->CheckState() && $this->ReadPropertyBoolean("RainAtOff")) || $this->CheckState()){
            $this->CheckMove($Direction);
            return $Direction;
          }
          else{
            $this->LogMessage("CheckMoveTrigger", "It's raining. Do not Move because RainAtOff is not active!");
          }
        }
        else{
          $this->LogMessage("CheckMoveTrigger", "No Rain");
          if (!$this->ReadPropertyBoolean("BrightnessDetection") || !$this->ReadPropertyBoolean("SunAzimuthDetection") || $this->ReadPropertyInteger("BrightnessID") == 0){
            $this->SetBuffer("NormalState", 1);
            $this->LogMessage("CheckMoveTrigger", "Rain Check says: NormalState");
          }
        }
      }

      // Check if we can return to NormalState (only if BrightnessDetection and SunAzimuthDetection is disabled)
      if (!$this->ReadPropertyBoolean("BrightnessDetection") || !$this->ReadPropertyBoolean("SunAzimuthDetection") || $this->ReadPropertyInteger("BrightnessID") == 0){
        $NormalState = $this->ReadPropertyInteger("NormalState");
        if ($this->GetBuffer("NormalState") == 1){
          $this->LogMessage("CheckMoveTrigger", "Check, if we move to NormalState");
          $this->CheckMove($NormalState);
        }
      }

      // State
      if (!$this->CheckState()){
        $this->LogMessage("CheckMoveTrigger", "This shutter automatic is not active. Do nothing.");
        return 0;
      }

      // Sun Azimuth
      if ($this->ReadPropertyBoolean("SunAzimuthDetection")){
        $this->LogMessage("CheckMoveTrigger", "Checking SunAzimuth");
        $this->SetBuffer("Trigger", "SunAzimuth");
        $SunAzimuth = $this->CheckSunAzimuth();
        $this->CheckMove($SunAzimuth);
        return $SunAzimuth;
      }

      // external Brightness Trigger
      if ($this->ReadPropertyBoolean("ExternalBrightnessDetection")){
        $this->LogMessage("CheckMoveTrigger", "Checking ExternalBrightnessTrigger");
        $this->SetBuffer("Trigger", "ExternalBrightnessTrigger");
        $ExternalBrightnessTrigger = $this->CheckExternalBrightnessTrigger();
        $this->CheckMove($ExternalBrightnessTrigger);
        return $ExternalBrightnessTrigger;
      }

      // Brightness
      if ($this->ReadPropertyBoolean("BrightnessDetection")){
        $Brightness = $this->CheckBrightness();
        switch($Brightness){
          case 1:
            $this->LogMessage("CheckMoveTrigger", "Brightness is 1. Move Shutter Up");
            $this->SetBuffer("Trigger", "Brightness Move Up");
            $this->CheckMove(1);
            return 1;
          break;

          case 2:
            $this->LogMessage("CheckMoveTrigger", "Brightness is 2. Move Shutter Down");
            $this->SetBuffer("Trigger", "Brightness Move Down");
            $this->CheckMove(2);
            return 2;
          break;

          case 3:
            $this->LogMessage("CheckMoveTrigger", "Brightness is 3. No action");
            return 3;
          break;
        }
      }
    }
    public function EmulationReset(){
      // Set Buffer to 0, i.e. at midnight
      $RealStatus = $this->GetShutterStatus();
      $this->SetBuffer("Direction", 0);
    }
    public function SetShutterState(bool $NewState){
      if ($NewState){
        SetValue($this->GetIDForIdent("State"), TRUE);
      }
      else{
        SetValue($this->GetIDForIdent("State"), FALSE);
      }
    }
    private function CheckMove(int $direction){
      // use emulated status or real status?
      $RealStatus = $this->GetShutterStatus();
      $this->LogMessage("CheckMove", "Real Shutter Status is " . $RealStatus);
      $EmulatedStatus = $this->GetBuffer("Direction");
      $LastMove = $this->GetBuffer("LastMove");
      $MovementDelay = $this->ReadPropertyInteger("MovementDelay");
      $this->LogMessage("CheckMove", "Emulated Shutter Status is " . (int)$EmulatedStatus);
      $this->LogMessage("CheckMove", "Last Move: " . $LastMove);

      // if there is no Buffer present (i.e. after system restart or creation of new instance)
      if ($EmulatedStatus == "" || $EmulatedStatus == 0){
        $EmulatedStatus = $RealStatus;
        $this->SetBuffer("Direction", $RealStatus);
        $this->LogMessage("CheckMove", "There is no Emulated status. Use Real Status till Emulated Status is present");
        $this->LogMessage("CheckMove", "Emulated Shutter Status was changed and is now " . (int)$EmulatedStatus);
      }
      $Emulated = $this->CheckEmulate();
      $this->LogMessage("CheckMove", "Emulated  (active / not active) is " . $Emulated);

      // check if deactivateManualMove is active
      if ($this->CheckDeactivateAfterManualMovement() && $RealStatus != $EmulatedStatus){
        $this->LogMessage("CheckMove", "Deactivate After Manual Movement now!");
        SetValue(IPS_GetObjectIDByIdent("State", $this->InstanceID), FALSE);
        return;
      }

      // check which status to use
      if($Emulated){
        $Status = $EmulatedStatus;
        $this->LogMessage("CheckMove", "I will use emulated Status!");
      }
      else{
        $Status = $RealStatus;
        $this->LogMessage("CheckMove", "I will use Real Status!");
      }
      $this->LogMessage("CheckMove", "I am working with status " . $Status);

      if ($Status == $direction){
        $this->LogMessage("CheckMove", "Shutter is already in correct position.");
      }
      else{
        // check if the last movement is away enough
        $DiffLastMovement = time() - $LastMove;
        if ($DiffLastMovement < $MovementDelay){
          $this->LogMessage("CheckMove", "Diff Last Movement: " . $DiffLastMovement . " Can't Move");
          return;
        }
        $this->LogMessage("CheckMove", "Shutter is in wrong position.");
        // Move
        $this->Move($direction);
      }
      return;
    }
    private function Move($Direction){
      $this->LogMessage("Move", "Move " . $Direction);
      $this->SendMoveToInstance($Direction);
      $this->TriggerToString($this->GetBuffer("Trigger"));
      $this->SetBuffer("Direction", $Direction);
      $this->SetBuffer("LastMove", time());
      if ($Direction == 1) $DirectionText = "up";
      if ($Direction == 2) $DirectionText = "down";
      IPS_LogMessage("SIB_ShutterControl", "Move " . $DirectionText . ". Trigger: " . $this->GetBuffer("Trigger"));
    }
    private function CheckEmulate(){
      $emulate = $this->ReadPropertyBoolean("Emulate");
      return $emulate;
    }
    private function CheckDeactivateAfterManualMovement(){
      $DeactivateAfterManualMovement = $this->ReadPropertyBoolean("deactivateManualMove");
      return $DeactivateAfterManualMovement;
    }
    private function CheckDeactivateAfterDoor(){
      $DeactivateAfterDoor = $this->ReadPropertyBoolean("deactivateDoor");
      return $DeactivateAfterDoor;
    }
    private function CheckState(){
      $active = GetValue($this->GetIDForIdent("State"));
      if ($active){
        return TRUE;
      }
      else{
        return FALSE;
      }
    }
    private function GetShutterStatus(){
      // Check if Dummy, KNX, LCN oder 1-Wire
      $InstanceID = $this->ReadPropertyInteger("ShutterID");
      $Instance = IPS_GetInstance($InstanceID);
      $Type = $Instance['ModuleInfo']['ModuleID'];

      switch($Type){
        // LCN Shutter
        // 0 = opened / up
        // 4 = closed / down
        case "{C81E019F-6341-4748-8644-1C29D99B813E}":
          $Shutter = GetValue(IPS_GetObjectIDByIdent("Action", $this->ReadPropertyInteger("ShutterID")));
          if ($Shutter == 0) $Shutter = 1;
          if ($Shutter == 4) $Shutter = 2;
        break;

        // LCN Relay
        // TRUE = down
        // false = up
        case "{2D871359-14D8-493F-9B01-26432E3A710F}":
          $Shutter = GetValue(IPS_GetObjectIDByIdent("Status", $this->ReadPropertyInteger("ShutterID")));
          if ($Shutter) $Shutter = 2;
          if (!$Shutter) $Shutter = 1;
        break;

        default:
          $Shutter = GetValue(IPS_GetObjectIDByIdent("Status", $this->ReadPropertyInteger("ShutterID")));
          break;
        }
        // 0 = N/A
        // 1 opened / up
        // 2 closed / down
        return $Shutter;
    }
    private function CheckBrightness(){
      // return 1 to move up
      // return 2 to move down
      // return 3 for no changes
      if ($this->ReadPropertyInteger("BrightnessID") != 0){
        $Brightness = GetValue($this->ReadPropertyInteger("BrightnessID"));
      }
      $BrightnessThreshold = GetValue($this->GetIDForIdent("BrightnessThreshold"));
      $DirectionExceed = $this->ReadPropertyInteger("DirectionExceedThreshold");
      $ShutterState = $this->GetShutterStatus();

      switch ($DirectionExceed){
        case 1:
          $DirectionExceedInverted = 2;
        break;

        case 2:
          $DirectionExceedInverted = 1;
        break;
      }
      $this->LogMessage("CheckBrightness", "Brightness: " . $Brightness);
      $this->LogMessage("CheckBrightness", "BrightnessThreshold: " . $BrightnessThreshold);
      $this->LogMessage("CheckBrightness", "DirectionExceed: " . $DirectionExceed);
      $this->LogMessage("CheckBrightness", "DirectionExceedInverted: " . $DirectionExceedInverted);
      $this->LogMessage("CheckBrightness", "ShutterState: " . $ShutterState);

      if ($Brightness > $BrightnessThreshold){
        $this->LogMessage("CheckBrightness", "Brightness is bigger than BrightnessThreshold.");
        if ($ShutterState == $DirectionExceed){
          $this->SetBuffer("BrightnessTimer", 0);
          $this->LogMessage("CheckBrightness", "Shutter is already in correct state. Set Timer to 0");
          return 3;
        }
        // Shutter is in wrong position
        else{
          $Buffer = $this->GetBuffer("BrightnessTimer");
          // Set Buffer, if not present
          if ($Buffer == 0){
            $this->SetBuffer("BrightnessTimer", time());
            $this->LogMessage("CheckBrightness", "New Timer started");
            return 3;
          }
          // Check time difference
          else{
            $now = time();
            $TimeDiff = $now - $Buffer;
            $this->LogMessage("CheckBrightness", "Timer Diff: " . $TimeDiff . "!");
            $Threshold = $this->ReadPropertyInteger("BrightnessThreshold");
            if ($TimeDiff >= $Threshold){
              $this->LogMessage("CheckBrightness", "Timer End. Move. Set Timer To 0");
              $this->SetBuffer("BrightnessTimer", 0);
              return $DirectionExceed;
            }
            else{
              $this->LogMessage("CheckBrightness", "Nothing to do. Timer not finished yet.");
              return 3;
            }
          }
        }
        return $DirectionExceedInverted;
      }

      if ($Brightness < $BrightnessThreshold){
        $this->LogMessage("CheckBrightness", "Brightness is lower than BrightnessThreshold. Check if we have to move.");
        if ($ShutterState == $DirectionExceedInverted){
          $this->SetBuffer("BrightnessTimer", 0);
          $this->LogMessage("CheckBrightness", "Shutter is already in correct state. Set Timer to 0");
          return 3;
        }
        // Shutter is in wrong position
        else{
          $Buffer = $this->GetBuffer("BrightnessTimer");
          // Set Buffer, if not present
          if ($Buffer == 0){
            $this->SetBuffer("BrightnessTimer", time());
            $this->LogMessage("CheckBrightness", "New Timer started");
            return 3;
          }
          // Check time difference
          else{
            $now = time();
            $TimeDiff = $now - $Buffer;
            $this->LogMessage("CheckBrightness", "Timer Diff: " . $TimeDiff . "!");
            $Threshold = $this->ReadPropertyInteger("BrightnessThreshold");
            if ($TimeDiff >= $Threshold){
              $this->LogMessage("CheckBrightness", "Timer End. Move. Set Timer To 0");
              $this->SetBuffer("BrightnessTimer", 0);
              return $DirectionExceedInverted;
            }
            else{
              $this->LogMessage("CheckBrightness", "Nothing to do. Timer not finished yet.");
              return 3;
            }
          }
        }
        return $DirectionExceedInverted;
      }
      else{
        return 3;
      }
    }
    private function CheckDoor(){
      if ($this->ReadPropertyInteger("DoorID") != 0){
        $Door = GetValue($this->ReadPropertyInteger("DoorID"));
        if ($Door){
          $this->LogMessage("CheckDoor", "Door is open");
          return TRUE;
        }
        else{
          return FALSE;
        }
      }
    }
    private function CheckRain(){
      if ($this->ReadPropertyInteger("RainID") != 0){
        $Rain = GetValue($this->ReadPropertyInteger("RainID"));
        if ($Rain){
          $this->LogMessage("CheckRain", "It's raining");
          return TRUE;
        }
        else{
          return FALSE;
        }
      }
    }
    private function CheckWind(){
      if ($this->ReadPropertyInteger("WindID") != 0){
        $Wind = GetValue($this->ReadPropertyInteger("WindID"));
        if ($Wind){
          $this->LogMessage("CheckWind", "It's windig");
          return TRUE;
        }
        else{
          return FALSE;
        }
      }
    }
    private function CheckSleepMode(){
      $SleepMode = $this->GetBuffer("SleepMode");
      $this->LogMessage("CheckSleepmode", "Sleep " . (int)$SleepMode);
      return $SleepMode;
    }
    private function CheckSunAzimuth(){
      $Azimuth = GetValue($this->ReadPropertyInteger("SunAzimuthID"));
      $Down = $this->ReadPropertyInteger("SunAzimuthDown");
      $Up = $this->ReadPropertyInteger("SunAzimuthUp");

      // 0 - Down --> Go up
      if ($Azimuth > 0 && $Azimuth < $Down){
        $this->LogMessage("CheckSunAzimuth", "Azimuth > 0 AND < Down. --> Go Up");
        return 1;
      }
      // Down - Up --> Go Down
      if ($Azimuth >= $Down && $Azimuth <= $Up){
        $this->LogMessage("CheckSunAzimuth", "Azimuth > 0 AND < Down. --> Go Down");
        return 2;
      }
      // Up - 360 --> Go Up
      if ($Azimuth >= $Up){
        $this->LogMessage("CheckSunAzimuth", "Azimuth > Up. --> Go Up");
        return 1;
      }
    }
    private function CheckExternalBrightnessTrigger(){
      $ExternalBrightnessTrigger = GetValue($this->ReadPropertyInteger("ExternalBrightnessTriggerID"));
      $DirectionAtTrigger = $this->ReadPropertyInteger("ExternalBrightnessTriggerDirection");
      $this->LogMessage("CheckExternalBrightnessTrigger", "Direction At Trigger is: " . $DirectionAtTrigger);

      switch($DirectionAtTrigger){
        // 1: TRUE = down / FALSE = up
        case 1:
          if($ExternalBrightnessTrigger){
            $this->LogMessage("CheckExternalBrightnessTrigger", "External Brightness Trigger: TRUE --> go down");
            return 2;
          }
          else{
            $this->LogMessage("CheckExternalBrightnessTrigger", "External Brightness Trigger: FALSE --> go up");
            return 1;
          }
        break;

        // 2: TRUE = up / FALSE = down
        case 2:
          if($ExternalBrightnessTrigger){
            $this->LogMessage("CheckExternalBrightnessTrigger", "External Brightness Trigger: TRUE --> go up");
            return 1;
          }
          else{
            $this->LogMessage("CheckExternalBrightnessTrigger", "External Brightness Trigger: FALSE --> go down");
            return 2;
          }
        break;
      }
    }
    private function SetScheduleState($State){
      $ScheduleID = IPS_GetObjectIDByIdent("Schedule", $this->InstanceID);
      IPS_SetEventActive($ScheduleID, $State);
    }
    private function TriggerToString($Trigger){
      if ($this->ReadPropertyBoolean("ShowTrigger")){
        SetValue(IPS_GetObjectIDByIdent("Trigger", $this->InstanceID), $this->GetBuffer("Trigger"));
      }
    }
    private function SendMoveToInstance($Direction){
      // Check if Dummy, KNX, LCN oder 1-Wire
      $InstanceID = $this->ReadPropertyInteger("ShutterID");
      $Instance = IPS_GetInstance($InstanceID);
      $Type = $Instance['ModuleInfo']['ModuleID'];

      switch($Type){
        // Dummy
        case "{485D0419-BE97-4548-AA9C-C083EB82E61E}":
          $this->LogMessage("SendMoveToInstance", "Dummy Shutter");
          SetValue(IPS_GetObjectIDByIdent("Status", $InstanceID), $Direction);
        break;

        // KNX
        case "{24A9D68D-7B98-4D74-9BAE-3645D435A9EF}":
          $this->LogMessage("SendMoveToInstance", "KNX Shutter");
          switch ($Direction){
            case 1:
              $Direction = 0;
            break;

            case 2:
              $Direction = 4;
            break;
          }
          EIB_Move($InstanceID, $Direction);
        break;

        // ESERA-Automation Shutter Modul 1-Fach
        case "{866D7D88-BC80-476C-A0BE-70DD83F00B17}":
          $this->LogMessage("SendMoveToInstance", "ESERA Shutter 1-Fach");
          switch ($Direction){
            case 1:
              $Direction = 2;
            break;

            case 2:
              $Direction = 1;
            break;
          }
          ESERA_MoveShutter($InstanceID, $Direction);
        break;

        // LCN Shutter
        case "{C81E019F-6341-4748-8644-1C29D99B813E}":
          $this->LogMessage("SendMoveToInstance", "LCN Shutter");
          switch ($Direction){
            case 1:
              LCN_ShutterMoveUp($InstanceID);
            break;

            case 2:
              LCN_ShutterMoveDown($InstanceID);
            break;
          }
        break;

        // LCN Relay
        case "{2D871359-14D8-493F-9B01-26432E3A710F}":
          $this->LogMessage("SendMoveToInstance", "LCN Relay");
          switch ($Direction){
            case 1:
              LCN_SwitchRelay($InstanceID, FALSE);
            break;

            case 2:
              LCN_SwitchRelay($InstanceID, TRUE);
            break;
          }
          break;

          default:
            $this->LogMessage("SendMoveToInstance", "Shutter Instance not supported");
            IPS_LogMessage("SendMoveToInstance", "Shutter Instance not supported");
        break;
        }
    }
    private function LogMessage($Sender, $Message){
      $this->SendDebug($Sender, $Message, 0);
    }
    private function CreateVariableProfile($ProfileName, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
      if (!IPS_VariableProfileExists($ProfileName)) {
        IPS_CreateVariableProfile($ProfileName, $ProfileType);
        IPS_SetVariableProfileText($ProfileName, "", $Suffix);
        IPS_SetVariableProfileValues($ProfileName, $MinValue, $MaxValue, $StepSize);
        IPS_SetVariableProfileDigits($ProfileName, $Digits);
        IPS_SetVariableProfileIcon($ProfileName, $Icon);
      }
    }
    protected function SetEmulationResetTimer($active){
      if ($active){
        $Now = new DateTime();
        $Target = new DateTime();
        $Target->modify('+1 day');
        $Target->setTime(0,0,30);
        $Diff =  $Target->getTimestamp() - $Now->getTimestamp();
        $Interval = $Diff *1000;
        $this->SetTimerInterval("EmulationResetTimer", $Interval);
      }
      else{
        $this->SetTimerInterval("EmulationResetTimer", 0);
      }
    }
}
?>
