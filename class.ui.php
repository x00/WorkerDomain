<?php if (!defined('APPLICATION')) exit();

/**
 *  @@ WorkerDomainUIDomain @@
 *
 *  Links UI Worker to the worker collection
 *  and retrieves it. Auto initialising.
 *
 *  Provides a simple way for other workers, or
 *  the plugin file to call it method and access its
 *  properties.
 *
 *  A worker will reference the UI work like so:
 *  $this->Plgn->UI()
 *
 *  The plugin file can access it like so:
 *  $this->UI()
 *
 *  @abstract
 */

abstract class WorkerDomainUIDomain extends WorkerDomainSettingsDomain {

/**
 * The unique identifier to look up Worker
 * @var string $WorkerName
 */

  private $WorkerName = 'UI';

  /**
   *  @@ UI @@
   *
   *  UI Worker Domain address,
   *  links and retrieves
   *
   *  @return void
   */

  public function UI(){
    $WorkerName = $this->WorkerName;
    $WorkerClass = $this->GetPluginIndex().$WorkerName;
    return $this->LinkWorker($WorkerName,$WorkerClass);
  }

}

/**
 *  @@ WorkerDomainUI @@
 *
 *  The worker used to handle the main
 *  interactions.
 *
 */

class WorkerDomainUI {

  /**
   *  @@ WorkerDomain_Controller @@
   *
   *  Used to load shared resources,
   *  then dispatched to method using:
   *  $this->Plgn->Utility()->MiniDispatcher($Sender,'UI');
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function WorkerDomain_Controller($Sender) {
    $Sender->AddCssFile('workerdomain.css','plugins/WorkerDomain');
    $this->Plgn->Utility()->MiniDispatcher($Sender,'UI');
  }

  /**
   *  @@ WorkerDomain_Controller @@
   *
   *  Represents the index page, served by mini-dispatcher
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function Controller_Index($Sender){
    $Sender->SetData('Title',$this->Plgn->API()->GetProperty('LinkName'));
    $Sender->SetData('Message',$this->Plgn->API()->GetProperty('Message'));
    $Sender->View = $this->Plgn->Utility()->ThemeView('index');
    $Sender->Render();
  }

  /**
   *  @@ WorkerDomainLink @@
   *
   *  Adds top menu link
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function WorkerDomainLink($Sender) {
    $Sender->Menu->AddLink('WorkerDomain', $this->Plgn->API()->GetProperty('LinkName'), '/'.$this->Plgn->API()->GetProperty('URI'));
  }

}
