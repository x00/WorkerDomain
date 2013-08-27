# Worker Domain Pattern #

### About ###
This plugin is an example of the Worker Domain design pattern. It is a working example and I have been generous with annotating the code with documentation. 

This pattern was developed with pluggable APIs in mind. Particularly for the [Vanilla/Garden framework](http://vanillaforums.org/download), which this example uses. 

Place in the plugin directory an enable it the normal way.

You can access the "Hello World" by going to /helloworld and you can change the settings at /settings/workerdomain

However more interesting is the plugin code itself. 

### Intro ###

A common way of extending code besides overwriting, is to make use of event hooks and other special functions/methods that will be called by the framework's pluggable API. 

I use 'Hooks' broadly here to denote anything that inserts, overrides (overloads), or adds new functionality through documented means. So it can include an MVC framework a convention to create new controller methods dynamically for or 'magic' `__call` methods. 

This example was specifically designed with pluggable MVC frameworks in mind but may be adapted for others. 

There are two common styles of pluggable APIs, preregistered hooks, where functions and object methods are explicitly registered to an event like Wordpress, or named convention based implicit methods which is more in the the style of Vanilla/Garden.

Either way you often see a class (or file containing functions) specifically to be the interface with all these hookable events, which is the convention in Garden plugins or theme and application hooks files and what I have in mind for this example. 

I didn’t design this for Applications as a whole, but specifically for the increasingly popular way of extending, and adapting functionality within frameworks, and their applications. 

### Description ###

Without further ado, here is a spuedo-UML example of implementation of the pattern

![](https://dl.dropboxusercontent.com/u/15933183/WorkerDomain/WorkerDomain.png)

The purpose is both separation of concerns and a way of organising and making clear implicit code. What you have is the standard plugin class at the bottom left in pink `MyPlugin`, is what is directly employed by the framework, and its method will be called by the framework. 

Quite often this file doesn't just serve the purpose of interfacing with the pluggable API, it contain all kinds of other logic, which can make it quite clutter fasts, especially if your plugin is more than a dozen

The idea in this pattern is to keep this file lean, and basically only contain interfacing methods, if at all possible. 

Now within plugin there can be all sort of different tasks, and these categories of tasks can be intermingled. The idea of the patterns is to place task operations under `Workers`, which can collaborate together. 

Now Workers are grouped in a single collection, but their classes are not part of the plugins inheritance, they are are merely employed to do tasks. 

This means that each worker is capable of using its own design pattern an is not constrained by a particular implementation, or inheritance, as it is not part of the plugin object, it is simply loosely held by reference in a collection. 

Workers need a way of collaborating with each other, and also a way of interacting with the plugin instance. 

These two happen to be related, because the collection is held by the plugin instance. So each Worker has a back-reference to the plugin, set dynamically use a public property `->Plgn`.

To reference the plug-in it would be as simple as  `$this->Plgn`. However it is more long-winded to reference another worker,
`$this->Plgn->Workers[WorkerName]`.  However you won't have worry about this as you will see. 

But wait a minute...how do the workers even get employed, and put into the collection in the first place?  Well there is a Utility method called LinkWorker that does it. 

However this is not just called repeatedly in the plugin class, becuase this isn't really to do with the framework's pluggable interface, but a design pattern for the pluign as a whole. 

Instead you have Domain classes (shown on the left in yellow and cyan), which use inheritance of the plugin class as a mechanism of the pattern. These classes are supposed to be lean, and have a very specific purpose.

So instead of your plugin file extending `GND_Plugin` directly in this case there is an inheritance chain of Domain classes in-between `GND_Plugin` and the Plugin class. These files will have to be explicitly loaded as Garden auto-loader's will not do this, but I suggest using conventions outlined here. As Domains classes are supposed to be lean they do not pollute with unnecessary functionality, but simply using inheritance as mechanism to provide a Domain method to link the worker. 

Domains like the the name implies, provide a simple identifier for the Workers and Plugin class to reference Workers. This is simply a named Method which is the same as the worker reference string in the Worker collection.

**_Each Domain is responsible for linking a single Worker at a time._** Note this is one instance. It doesn't say that you couldn't reuse the same class for different Worker. The linking of the worker could be conditional, for example a different version of the framework, could Link a different worker or the same class with custom parameters send via the LinkWorker. 

This possibility of custom deployment is one of the reason why you have separate Domain classes, the other reason is simply to make it plain in the code, which is part of the reason for using a design pattern in the first place.  it would be easy to magically deploy the workers but the wouldn't be as clear, not to mention it would carry an overhead. 

A Domain classes are usually very lean they can go in the same file as a Worker, which is also for clarity, and simplifies loading. 

**_A Domain neither owns nor manages Workers once deployed._** its job is to link the Workers, and return the worker so they can collaborate together. 

The Workers are known by their short name with is usually a suffix of their class name. The convention for Worker class names is `PluginNameWorkerName` and the Domain class `PluginNameWorkerNameDomain` e.g. with `MyPlugin` plugin the `MyPluginAPI`would be for the Worker `API` the Domain would typically be called `MyPluginAPIDomain`, so in order for the plugin class to use a worker such as `API` it would be as simple as `$this->API()`, and in order for a Worker to use `API` it would be as simple as `$this->Plgn->API()`.

However as LinkWorker lets you put whatever Name and Class identifier as you like, you can do as you please, and this can be useful if you have a more complex scenario. After these two parameters, it also can take an arbitrary number of parameters, which is passed to the constructor of the Worker on initialisation, however if you are using many of them of too often, you are probably doing it wrong. 

In the case of `->API()`, it is referencing `->Workers['API']` internally. However if it is not initialised, LinkWorker will automatically do that for you, which is the advantage of using the Domain method of access. **_If a Worker is not used it is not initialised, and it is initialised automatically on first use._** If you do not use the domain method of acces, this will not happen. It is lazy loading if you like. 

As you remember `$this->Plgn` is the public back-reference to the plugin instance from a Worker (you don't have to explicitly declare `public $Plgn;`, but it shouldn't hurt). This means if you wish to use native plugin method such as GetView a worker could do $this->Plgn->GetView(). As it happens this example also includes a bunch of useful Utility functions for plugin development so I recommend `$this->Plgn->Utility()->ThemeView()` instead, which uses GetView internally, however this is not part of the design pattern just an implementation using the pattern with some code candy. 

Hooks can have parameters such as $Sender which can easily be passed to Worker methods, in order to do the work. Nothing unconventional about this. 

As you can see in the diagram the `UtilityDomain` is a little less lean becuase it contains the Worker collection and also the `LinkWorker` method used to like all the workers. This is becuase it both severs as a Domain for Utility Worker, but also holds all the workers and the method to link them. It doesn't have to be done this way but it is clear enough IMO. 

Now you can see in this example you have the Workers `Utility`, `API`, `Settings` and `UI`. shown on the right and the corresponding domain classes  shown on the left. This is simply a suggestion, you can have whatever Workers you like. You can add more Workers, so long as the chain is extended for the Domains. I will talk more about the specific implementation and how it relates to the Vanilla/Garden in a linked discussion.

The actual order of the Domain chain is less important, becuase workers are decoupled from the plugin, and as they are auto-initialised they are available to each other from the outset. However the order might be useful for visualising the extension. 

You could say Workers have a loose hierarchy, becuase some may never be directly employed by the plugin's hooks. However it is more accurate to say the Workers are collaborative, and delegated to specific sorts of tasks, and the hierarchy, so far as it exists, is fairly flat, though obviously by design API is more low level than UI in this example. 
