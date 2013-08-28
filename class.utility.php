<?php if (!defined('APPLICATION')) exit();

/**
 *  @@ WorkerDomainUtilityDomain @@
 *
 *  Links Utility Worker to the worker collection
 *  and retrieves it. Auto initialising.
 *
 *  Provides a simple way for other workers, or
 *  the plugin file to call it method and access its
 *  properties.
 *
 *  It also is a special Domain that holds the Workers
 *  collection, and LinkWorker method.
 *
 *  Also can be used for abstract methods which need to
 *  be implemented by the plugin class e.g. PlugnSetup
 *
 *  A worker will reference the Utility work like so:
 *  $this->Plgn->Utility()
 *
 *  The plugin file can access it like so:
 *  $this->Utility()
 *
 * @abstract
 */

abstract class WorkerDomainUtilityDomain extends Gdn_Plugin {

  /**
   * Holds a collection of Workers
   * @var array[string]class $Workers
   */
  protected $Workers = array();

  /**
   * The unique identifier to look up Worker
   * @var string $WorkerName
   */
  private $WorkerName = 'Utility';

  // important to call parent constructor to
  // ensure design pattern is working with
  // Garden's pluggable interface.
  function __construct() {
     parent::__construct();
  }

  /**
   *  @@ Utility @@
   *
   *  Utility Worker Domain address,
   *  links and retrieves
   *
   *  @return void
   */

  public function Utility(){
    $WorkerName = $this->WorkerName;
    $WorkerClass = $this->GetPluginIndex().$WorkerName;
    return $this->LinkWorker($WorkerName,$WorkerClass);
  }

  /**
   *  @@ LinkWorker @@
   *
   *  This method is used by the domain class to
   *  Link the Worker to the worker group, and
   *  retrieve. Auto-initialises the class
   *
   *  @param string $WorkerName
   *  @param string $WorkerClass
   *  @param mixed args.* any extra params to be passed to worker constructor.
   *
   *  @return void
   */

  public function LinkWorker($WorkerName,$WorkerClass){
    if(GetValue($WorkerName, $this->Workers))
      return $this->Workers[$WorkerName];
    $Args = func_get_args();
    switch(count($Args)){
      case 2;
        $Worker = new $WorkerClass();
        break;
      case 3:
        $Worker = new $WorkerClass($Args[2]);
        break;
      case 4:
        $Worker = new $WorkerClass($Args[2],$Args[3]);
        break;
      case 5:
        $Worker = new $WorkerClass($Args[2],$Args[3],$Args[4]);
        break;
      default:
        $Ref = new ReflectionClass($WorkerClass);
        $Worker = $Ref->newInstanceArgs($Args);
        break;
    }
    $Worker->Plgn = $this;
    return $this->Workers[$WorkerName] = $Worker;

  }

  /**
   *  @@ PluginSetup @@
   *
   *  Abstract method required for hotloading/setup
   *
   *  @return void
   */

  abstract public function PluginSetup();

}

/**
 *  @@ WorkerDomainUtility @@
 *
 *  the worker provided utility methods,
 *  and general useful stuff for plugin dev
 *
 */

class WorkerDomainUtility {

  private static $LoadMaps = array();


  /**
   *  @@ RegisterLoadMap @@
   *
   *  A simple way for registering Classes for auto-loading of class files
   *
   *  @param string $Matches the class name pattern to match
   *  @param string $Folder the folder name (can use sub-match substitution format)
   *  @param string $File the file name (can use sub-match substitution format)
   *  @param bool $LowercaseMatches default TRUE emain they will be inserted in string lowercased
   *
   *  @return void
   */

  public static function RegisterLoadMap($Match,$Folder,$File,$LowercaseMatches=TRUE){
    self::$LoadMaps[] = array(
      'Match' => $Match,
      'Folder' => $Folder,
      'File' =>$File,
      'LowercaseMatches' => $LowercaseMatches
    );
  }

  /**
   *  @@ LoadMapParse @@
   *
   *  Used by Load to replace strings with
   *  sub-patterns from class name match
   *
   *  e.g. ${Matches[n]} where n is the
   *  sub-pattern
   *
   *  @param array[]string $Matches
   *  @param string $Str the string for parsing
   *
   *  @return string
   */

  private static function LoadMapParse($Matches,$Str){
      foreach ($Matches As $MatchI => $MatchV){
        $Str = preg_replace('`\{?\$\{?Matches\['.$MatchI.'\]\}?`',$MatchV,$Str);
      }
      return $Str;
  }

  /**
   *  @@ Load @@
   *
   *  auto-load function which employs
   *  reg-exp pattern matching
   *
   *  @param string $Class name of class
   *
   *  @return void
   */

  public static function Load($Class){
    $Maps = self::$LoadMaps;
    foreach ($Maps As $Map){
      $Matches = array();

      if(preg_match($Map['Match'],$Class,$Matches)){

        if($Map['LowercaseMatches'])
          $Matches = array_map('strtolower',$Matches);

        $Map['Folder'] = self::LoadMapParse($Matches,$Map['Folder']);
        $Map['File'] = self::LoadMapParse($Matches,$Map['File']);
        require_once(PATH_PLUGINS.DS.'WorkerDomain'.($Map['Folder'] ? DS.$Map['Folder']: '').DS.$Map['File']);
        break;
      }
    }
  }

  /**
   *  @@ InitLoad @@
   *
   *  register auto-load function
   *
   *  @return void
   */

  public static function InitLoad(){
    //ensure
    spl_autoload_register('WorkerDomainUtility::Load');
  }

  /**
   *  @@ HotLoad @@
   *
   *  Pluggable dispatcher
   *  e.g. public function PluginNameController_Test_Create($Sender){}
   *
   *  Way to ensure any new db structure gets created
   *  And new setup is updated without re-enabling
   *
   * @param bool $Force do regardless of version change (optional) default FALSE
   *
   * @return void
   */

  public function HotLoad($Force =  FALSE) {
    if(C('Plugins.'.$this->Plgn->GetPluginIndex().'.Version')!=$this->Plgn->PluginInfo['Version']){
      $this->Plgn->PluginSetup();
      SaveToConfig('Plugins.'.$this->Plgn->GetPluginIndex().'.Version', $this->Plgn->PluginInfo['Version']);
    }
  }

  /**
   *  @@ MiniDispatcher @@
   *
   *  e.g. public function PluginNameController_Test_Create($Sender){}
   *
   *  or
   *
   *  public function Controller_Test($Sender){}
   *
   *  Internally
   *
   *  @param string $Sender current GDN_Controller
   *  @param string $ControllerClass current pseudo-controller
   *  @param string $PluggablePrefix prefix for other plugins to use to add controller methods
   *  @param string $LocalPrefix local prefix to use to add controller methods
   *  @throws NotFoundException if no callable method found
   *
   *  @return mixed FALSE on error or the callback method result.;
   */

  public function MiniDispatcher($Sender, $ControllerClass = 'UI', $PluggablePrefix = NULL, $LocalPrefix = NULL){
    $PluggablePrefix = $PluggablePrefix ? $PluggablePrefix : $this->Plgn->GetPluginIndex().$ControllerClass.'Controller_';
    $LocalPrefix = $LocalPrefix ? $LocalPrefix : $ControllerClass.'Controller_';
    $Sender->Form = new Gdn_Form();

    $Plugin = $this;

    $ControllerMethod = '';
    if(count($Sender->RequestArgs)){
      list($MethodName) = $Sender->RequestArgs;
    }else{
      $MethodName = 'Index';
    }

    $DeclaredClasses = get_declared_classes();

    $TempControllerMethod = $LocalPrefix.$MethodName;
    $ControllerClass = is_object($ControllerClass) ? $ControllerClass : $Plugin->Plgn->$ControllerClass();
    if (method_exists($ControllerClass, $TempControllerMethod)){
      $ControllerMethod = $TempControllerMethod;
    }

    if(!$ControllerMethod){
      $TempControllerMethod = $PluggablePrefix.$MethodName.'_Create';

      foreach ($DeclaredClasses as $ClassName) {
        if (Gdn::PluginManager()->GetPluginInfo($ClassName)){
          $CurrentPlugin = Gdn::PluginManager()->GetPluginInstance($ClassName);
            if($CurrentPlugin && method_exists($CurrentPlugin, $TempControllerMethod)){
              $ControllerClass = $CurrentPlugin;
              $ControllerMethod = $TempControllerMethod;
              break;
            }
        }
      }

    }
    if (method_exists($ControllerClass, $ControllerMethod)) {
      $Sender->Plgn = $Plugin;
      return call_user_func(array($ControllerClass,$ControllerMethod),$Sender);
    } else {
      throw NotFoundException();
    }
  }


  /**
   *  @@ ThemeView @@
   *
   *  Set view that can be copied over to current theme
   *  e.g. view -> current_theme/views/plugins/PluginName/view.php
   *
   *  @param string $View name of file minus the .php
   *
   *  @return string
   */

  public function ThemeView($View){
    $ThemeViewLoc = CombinePaths(array(
      PATH_THEMES, Gdn::Controller()->Theme, 'views', $this->Plgn->GetPluginFolder()
    ));

    if(file_exists($ThemeViewLoc.DS.$View.'.php')){
      $View=$ThemeViewLoc.DS.$View.'.php';
    }else{
      $View=$this->Plgn->GetView($View.'.php');
    }


    return $View;

  }

  /**
   *  @@ DynamicRoute @@
   *
   *  Add a route on the fly
   *
   *  Typically set in Base_BeforeLoadRoutes_Handler
   *
   *  @param string $Routes loaded
   *  @param string $Route RegExp of route
   *  @param string $Destination to rout to
   *  @param string $Type of redirect (optional), default 'Internal' options Internal,Temporary,Permanent,NotAuthorized,NotFound
   *  @param bool $OneWay if an Internal request prevents direct access to destination  (optional), default FALSE
   *
   *  @return void
   */

  public function DynamicRoute(&$Routes, $Route, $Destination, $Type = 'Internal', $Oneway = FALSE){
    $Key = str_replace('_','/',base64_encode($Route));
    $Routes[$Key] = array($Destination, $Type);
    if($Oneway && $Type == 'Internal'){
      if(strpos(strtolower($Destination), strtolower(Gdn::Request()->Path()))===0){
        Gdn::Dispatcher()->Dispatch('Default404');
        exit;
      }
    }
  }
}
