# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Quick summary
Amaizing

* Version
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### Quick Start###

app/public/index.php
```
error_reporting(E_ALL);

define("__ROOT__", __DIR__ . "/../../");
define("__VENDOR__", "../../vendor");

require __VENDOR__ . "/autoload.php";

$engine = new Engine("przemek_config", __ROOT__ . "app");
$engine->run();

```
### Configuration ###
app/src/MyAppConfig.php

```
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
     * @RequestPath("/index")
     */
    public function indexAction() {
        return new PlainViewModel("Hello World");
    }

    /**
     * @RequestPath("/get")
     */
    public function getAction() {
        return new JsonViewModel(array(
            "user"=>"TODO"
        ));
    }

    /**
     * @RequestPath("/newView")
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
@EnableMailer -TODO
spark.mailer.enabled (true/false)- property


### @Annotations @@@
The heart of Spark Framework.

@Component
@Service,@Repository,@Configuration
@PostConstruct -
@Inject


### FluentData ###
FluentData




### Parametry ###
$this->config

Paramerty:
app.path - ścieżka do katalogu /app




* Dependencies
* Database configuration
* How to run tests
* Deployment instructions

### Contribution guidelines ###

* Writing tests
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact