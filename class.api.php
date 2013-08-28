<?php if (!defined('APPLICATION')) exit();

/**
 *  @@ WorkerDomainAPIDomain @@
 *
 *  Links API Worker to the worker collection
 *  and retrieves it. Auto initialising.
 *
 *  Provides a simple way for other workers, or
 *  the plugin file to call it method and access its
 *  properties.
 *
 *  A worker will reference the API work like so:
 *  $this->Plgn->API()
 *
 *  The plugin file can access it like so:
 *  $this->API()
 *
 *  @abstract
 */

abstract class WorkerDomainAPIDomain extends WorkerDomainUtilityDomain {

/**
 * The unique identifier to look up Worker
 * @var string $WorkerName
 */

  private $WorkerName = 'API';

  /**
   *  @@ API @@
   *
   *  API Worker Domain address,
   *  links and retrieves
   *
   *  @return void
   */

  public function API(){
    $WorkerName = $this->WorkerName;
    $WorkerClass = $this->GetPluginIndex().$WorkerName;
    return $this->LinkWorker($WorkerName,$WorkerClass);
  }

}

/**
 *  @@ WorkerDomainAPI @@
 *
 *  The worker used for the internals
 *
 *  Also can be access by other plugin by
 *  hooking WorkerDomain_Loaded_Handler
 *  and accessing $Sender->Plgn->API();
 *
 */

class WorkerDomainAPI {

  /**
   *  Hold default properties prior to change.
   *
   *  @var array[string]string $DefaultProperties
   *
   */

  private $DefaultProperties = array(
    'LinkName' => 'Hello World',
    'URI' => 'helloworld',
    'Message' => 'Hello Cruel World!'
  );

  /**
   *  @@ Init @@
   *
   *  The intifunction calls the Loaded event
   *  to be used by other pluigns, who want to
   *  use this pluign's API.
   *
   *  @return void
   *
   */

  public function Init(){
    $this->Plgn->FireEvent('Loaded');
  }

  /**
   *  @@ GetProperty @@
   *
   *  basic getter example
   *
   *  @param string $PropertyName
   *  @param string $Default (optional) to retrieve if not set
   *
   *  @return mixed
   *
   */

  public function GetProperty($PropertyName, $Default = ''){
    $Default = $Default ? $Default : GetValue($PropertyName,$this->DefaultProperties,'');
    return C('Plugins.WorkerDomain.'.$PropertyName, $Default);
  }

  /**
   *  @@ GetProperties @@
   *
   *  gets all properties
   *
   *  @return mixed
   *
   */
  public function GetProperties(){
    return array_merge($this->DefaultProperties,C('Plugins.WorkerDomain', array()));
  }

  /**
   *  @@ SetProperty @@
   *
   *  basic setter example
   *
   *  @param string $PropertyName
   *  @param string $PropertyValue to set
   *
   *  @return bool FALSE on failure
   *
   */

  public function SetProperty($PropertyName, $PropertyValue){
    return SaveToConfig('Plugins.WorkerDomain.'.$PropertyName);
  }

  /**
   *  @@ SetProperty @@
   *
   *  uses an array of properties
   *
   *  @param string $Properties
   *
   *  @return bool FALSE on failure
   *
   */

  public function SetProperties($Properties){
    $Save = array();
    foreach($Properties As $PropertyName => $PropertyValue){
      if(in_array($PropertyName,array('TransientKey','hpt','Save')))
        continue;
      $Save['Plugins.WorkerDomain.'.$PropertyName] = $PropertyValue;
    }
    return SaveToConfig($Save);
  }

}
