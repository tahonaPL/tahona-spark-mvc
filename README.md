# README #

### What is this repository for? ###

PHP framework with autoloading and annotations. All configuration is handled by annotations and beans.
Framework is designed to work with APCu cache.

* Version
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### Quick Start###

app/public/index.php
```php
error_reporting(E_ALL);

define("__ROOT__", __DIR__ . "/../../");
define("__VENDOR__", "../../vendor");

require __VENDOR__ . "/autoload.php";

$engine = new Engine("przemek_config", __ROOT__ . "app");
$engine->run();

```
### Configuration ###
app/src/MyAppConfig.php

```php
/**
 * @Configuration()
 * @EnableApcuBeanCache("code1")
 */
class MyAppConfig {
}
```

### Controller ###
app/src/MyAppController.php
```php
class MyAppController extends Controller {

    /**
     * @Path("/index")
     */
    public function indexAction() {
        return new PlainViewModel("Hello World");
    }

    /**
     * @Path("/get")
     */
    public function getAction() {
        return new JsonViewModel(array(
            "user"=>"TODO"
        ));
    }

    /**
     * @Path("/newView")
     */
     public function showNewViewAction() {
        return new ViewModel(array(
            "user"=>"TODO"
        ));
     }

}
```

Go to localhost/get or localhost/index;

### View ###

apc/view/{controller package}/{controllerName (without "Controller")}/{action}.tpl

1. For app/src/MyAppController@showNewViewAction we get:
   apc/view/myapp/showNewView.tpl
2. For app/src/some/serious/package/*controller*/MyAppController@showNewView*Action* we get:
apc/view/some/serious/package/myapp/showNewView.tpl

Keywords action and controller are deleted by default.

### Apcu Bean Cache ###
if @EnableApcuBeanCache annotation is added with @Configuration the only way to reset beans and init them
once more is by requestin localhost:80?reset (GET parameter "reset").


### Mailer ###
* @EnableMailer -TODO
* spark.mailer.enabled (true/false)- property


### @Annotations ###
The heart of Spark Framework.

* @Component
* @Service,@Repository,@Configuration
* @PostConstruct -
* @Inject

### FluentData ###
FluentData

### Parametry ###
$this->config

Paramerty:
app.path - ścieżka do katalogu /app
src.path - ścieżka do katalogu /app/src

### Multiple DataBase connection ###

Handle multiple connections.
```
 @RepositoryData(managerName="dataSource", managerName="entityManager") //default
 @RepositoryData(managerName="dataSourceSuper", managerName="exodusManager")

```



```php
    /**
     * @Bean
     */
    public function dataSource() {
        $dbConfig = new DataSource();
        $dbConfig->setDbname("tahona");
        $dbConfig->setHost("127.0.0.1");
        $dbConfig->setUsername("root");
        $dbConfig->setPassword("test");
        return $dbConfig;
    }
    /**
     * @Bean
     */
    public function dataSourceSuper() {
        $dbConfig = new DataSource();
        $dbConfig->setDbname("house");
        $dbConfig->setHost("127.0.0.1");
        $dbConfig->setUsername("root");
        $dbConfig->setPassword("test");
        return $dbConfig;
    }
```

Note: for override injection use @OverrideInject annotation

```
@OverrideInject(oldName="entityManager", newName="houseManager")
```
### Internalization ###

Bean definition
```php

    /**
    * @Bean
    */
    public function anyPrefixNameMessageResource() {
        return new LangResourcePath(array(
            "pl"=>array(
                "/house/house_pl.properties"
            ),
            "cz"=> array(...),
            "en"=>array(...),
        ));
    }
```

Where "pl","cz","en" are cookie value with key "lang";

* Property file
/house/house_pl.properties

```
core.thank.you.message=Thank You %s
```

* Use in php
```php
    /**
     * @Inject
     * @var langMessageResource
     */
    private $langMessageResource;


    ...

    $this->langMessageResource->get("core.thank.you.message", array("John"));

```

* Use in smarty

```
{lang code="core.thank.you.message" value=["John"]}
```
Results: Thank You John

### @Path ###

Annotation Defonition


```php
@Path(path="/login/{test}", method="get")

```

Fetch path paramether in Controller class;
```php
$this->getParam("test");

```

### MULTI fetch by component   ###


Example is for dynami menu module that will popup when new project or classes are added.
```php

    /**
    * @Inject
    */
    private $beanProvider;

    public function getMenuModules() {
        return $beanProvider->getByType(MenuModule::class);
    }
```

### Interceptors ###

```php
/**
* @Component
*/
class UserInterceptor implements HandlerInterceptor {

    /**
    * @Inject
    */
    private $someUserHolder;

    public function preHandle(Request $request) {
        //Do something like redirect or add values to request or auto-conversion(id to entity);
    }

    public function postHandle(Request $request, ViewModel $viewModel) {
        $viewModel->add("loggedUser", $someUserHolder->getUserFromSession())
    }
}
```

### Instalation - Composer ###

```
{
    "autoload": {
        "psr-4": {"": "app/src/"}
	},

	"require": {
        "smarty/smarty": "3.1.27",
        "tahona/spark-mvc": "*"
    }
}
```
