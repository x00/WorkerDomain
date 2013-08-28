<?php if (!defined('APPLICATION')) exit();

/**
 *  @@ WorkerDomainSettingsDomain @@
 *
 *  Links Settings Worker to the worker collection
 *  and retrieves it. Auto initialising.
 *
 *  Provides a simple way for other workers, or
 *  the plugin file to call it method and access its
 *  properties.
 *
 *  A worker will reference the Settings work like so:
 *  $this->Plgn->Settings()
 *
 *  The plugin file can access it like so:
 *  $this->Settings()
 *
 *  @abstract
 */

abstract class WorkerDomainSettingsDomain extends WorkerDomainAPIDomain {

/**
 * The unique identifier to look up Worker
 * @var string $WorkerName
 */

  private $WorkerName = 'Settings';

  /**
   *  @@ Settings @@
   *
   *  Settings Worker Domain address,
   *  links and retrieves
   *
   *  @return void
   */

  public function Settings(){
    $WorkerName = $this->WorkerName;
    $WorkerClass = $this->GetPluginIndex().$WorkerName;
    return $this->LinkWorker($WorkerName,$WorkerClass);
  }
}

/**
 *  @@ WorkerDomainSettings @@
 *
 *  The worker used to handle the backend
 *  settings interactions.
 *
 */

class WorkerDomainSettings {

  /**
   *  @@ WorkerDomainLink @@
   *
   *  Basic settings menu item and link
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function Settings_MenuItems($Sender) {
    $Menu = $Sender->EventArguments['SideMenu'];
    $Menu->AddItem('WorkerDomain', T('Worker Domain'));
    $Menu->AddLink('WorkerDomain', T('Settings'), 'settings/workerdomain', 'Garden.Settings.Manage');
  }

  /**
   *  @@ Settings_Controller @@
   *
   *  Used to load shared resources,
   *  then dispatched to method using:
   *  $this->Plgn->Utility()->MiniDispatcher($Sender,'Settings',...);
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */


  public function Settings_Controller($Sender){
    // general requirement across all settings/workerdomain/* pages
    $Sender->Permission('Garden.Settings.Manage');
    $Sender->AddCssFile('workerdomain.css','plugins/WorkerDomain');
    $Sender->AddSideMenu();
    // dispatch to specific settings page
    $this->Plgn->Utility()->MiniDispatcher($Sender,'Settings');
  }

  /**
   *  @@ SettingsController_Index @@
   *
   *  Represents the index page of the settings, served by mini-dispatcher
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function SettingsController_Index($Sender){
    if($Sender->Form->IsPostBack() != False){
      // basic validation
      $Validation = new Gdn_Validation();
      $Validation->ApplyRule('URI', 'Required');
      $Validation->ApplyRule('URI', 'UrlString', 'You must enter a valid URI');
      $Validation->ApplyRule('LinkName', 'Required');
      $Validation->AddRule('LinkName','regex:`^[\w ]+$`i');
      $Validation->ApplyRule('LinkName', 'LinkName', 'Name contains invalid characters');
      $FormValues = $Sender->Form->FormValues();
      $Validation->Validate($FormValues);
      $Sender->Form->SetValidationResults($Validation->Results());
      if(!$Sender->Form->ErrorCount()){
        // save date using API
        $this->Plgn->API()->SetProperties($FormValues);
      }
    }
    $Sender->SetData('Title', T($this->Plgn->PluginInfo['Name']));
    $Sender->SetData('Description', T($this->Plgn->PluginInfo['Description']));
    // set data for forum
    $Sender->Form->SetData($this->Plgn->API()->GetProperties());
    $Sender->View = $this->Plgn->Utility()->ThemeView('settings');
    $Sender->Render();
  }

}
