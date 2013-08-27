<?php if (!defined('APPLICATION')) exit();
// Define the plugin:
$PluginInfo['WorkerDomain'] = array(
   'Name' => 'Worker Domain',
   'Description' => "Example of a plugin using the Worker Domain design pattern.",
   'SettingsUrl' => '/dashboard/settings/workerdomain',
   'Version' => '0.1b',
   'RequiredApplications' => array('Vanilla' => '2.0.18'),
   'Author' => 'Paul Thomas',
   'AuthorEmail' => 'dt01pqt_pt@yahoo.com',
   'AuthorUrl' => 'http://www.vanillaforums.org/profile/x00'
);

/**
 *  @@ WorkerDomainLoad function @@
 *
 *  A callback for spl_autoload_register
 *
 *  Will load class.[name].php for WorkerDomain[Name]
 *  or WorkerDomain[Name]Domain
 *
 *  @param string $Class class name to be matched
 *
 *  @return void
 */

function WorkerDomainLoad($Class){
  $Match = array();
  if(preg_match('`^WorkerDomain(.*)`',$Class,$Match)){
    $File = strtolower(preg_replace('`Domain$`','',$Match[1]));
    include_once(PATH_PLUGINS.DS.'WorkerDomain'.DS.'class.'.$File.'.php');
  }
}

// auto load worker/domain classes.
spl_autoload_register('WorkerDomainLoad');

// Initialise loader to be use by various libraries an architecture
WorkerDomainUtility::InitLoad();

// Simple way of auto-loading a library based on regexp pattern matching
// WorkerDomainUtility::RegisterLoadMap('`^.*SomeThing$`','library/something','class.{$Matches[0]}.php');


/**
 *  @@ WorkerDomain @@
 *
 *  The plugin class which is referenced by
 *  Garden's pluggable interface.
 *
 *  The plugin hook uses workers, which often
 *  collaborate together on the tasks in hand.
 */

//<<<< must be flush no indentation !!!!
class WorkerDomain extends WorkerDomainUIDomain{

  /*
   * !! WorkerDomain UI Hooks !!
   */

  /**
   *  @@ VanillaController_WorkerDomain_Create @@
   *
   *  Create a VanillaController method to use as a base
   *  we will use dynamic routing to create psuedo-controller,
   *  and also to have a custom url scheme
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function VanillaController_WorkerDomain_Create($Sender){
    // link to controller mini-dispatch
    $this->UI()->WorkerDomain_Controller($Sender);
  }

  /**
   *  @@ Base_Render_Before @@
   *
   *  Hook every page, for menu link
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function Base_Render_Before($Sender) {
    // menu link to example page
    $this->UI()->WorkerDomainLink($Sender);
  }

  /*
   * !! WorkerDomain Settings Hooks !!
   */

  /**
   *  @@ Base_GetAppSettingsMenuItems_Handler @@
   *
   *  Hook GetAppSettingsMenuItems to add menu/items
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */
  public function Base_GetAppSettingsMenuItems_Handler($Sender){
    // dashboard menu/items
    $this->Settings()->Settings_MenuItems($Sender);
  }


  /**
   *  @@ SettingsController_WorkerDomain_Create @@
   *
   *  Create a setting base in dashboard to use
   *  for dispatch
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function SettingsController_WorkerDomain_Create($Sender){
    // link to settings controller mini-dispatch
    $this->Settings()->Settings_Controller($Sender);
  }

  /*
   * !! WorkerDomain Utility Hooks/Setup !!
   */

  /**
   *  @@ Base_BeforeControllerMethod_Handler @@
   *
   *  Init this plugins API, before controller methods
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */

  public function Base_BeforeControllerMethod_Handler($Sender) {
    $this->API()->Init();
  }

  /**
   *  @@ Base_BeforeLoadRoutes_Handler @@
   *
   *  Hook BeforeLoadRoutes to use dynamic router
   *
   *  @param Gdn_Controller $Sender
   *  @param mixed[string] $Args
   *
   *  @return void
   */

  public function Base_BeforeLoadRoutes_Handler($Sender, &$Args){
    // parsing desired uri, and blocking external access to vanilla/workerdomain
    $this->Utility()->DynamicRoute($Args['Routes'],'^'.$this->API()->GetProperty('URI').'(/.*)?$','vanilla/workerdomain$1','Internal', TRUE);
  }

  /**
   *  @@ Base_BeforeLoadRoutes_Handler @@
   *
   *  Hook BeforeDispatch to implemnt HotLoad method
   *
   *  @param Gdn_Controller $Sender
   *
   *  @return void
   */
  public function Base_BeforeDispatch_Handler($Sender){
    // hot load of setup on version update without re-enabling plugin
    $this->Utility()->HotLoad();
  }

  /**
   *  @@ Setup @@
   *
   *  pluggable API Setup method
   *
   *  @return void
   */
  public function Setup() {
    // force hotload on plugin enable
    $this->Utility()->HotLoad(TRUE);
  }

  /**
   *  @@ PluginSetup @@
   *
   *  required abstract method used by HotLoad
   *
   *  @return void
   */
  public function PluginSetup(){
    // put structure / and other setup here for hot-loading
  }

}
