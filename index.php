<?php
use WebtudorBlog\Controller\AbstractController;
use WebtudorBlog\Controller\ErrorController;


class Blog {
    /**
     * Automatically loads a class from the classes directory. Namespace\Classname becomes classes/Namespace/Classname.php
     *
     * @param string $className
     *
     * @return void
     */
    public function autoload($className) {
        $file = __DIR__ . '/classes/' . strtr($className, array('\\' => DIRECTORY_SEPARATOR)) . '.php';
        require($file);
    }

    public function init() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function debug() {
        error_reporting( E_ALL );
        ini_set('display_errors', 1);
    }
}

$blog = new Blog();
$blog->init();
$blog->debug();

//Define an easy to reference folder root
define('PROJECT_ROOT', __DIR__);

// Split the request URI part into pieces
// e.g. /posts/some-post becomes ['', 'posts', 'some-post']
$url = explode('/', $_SERVER['REQUEST_URI']);


//A list of all controllers
$controllers = array('WebtudorBlog\\Controller\\BlogController');

//Output correct headers for UTF-8 encoding
header('Content-Type: text/html;charset=utf-8');

//Loop over all controllers and find the one that is actually going to handle our request.
foreach ($controllers as $controllerClass) {
	/**
	 * @var AbstractController $controllerClass
	 */
	//Get the action from the controller
    $action = $controllerClass::getAction($url);

    if ($action !== null) {
		//Found an action, let's use it!

		//Instantiate new class
		$controller = new $controllerClass();
		//Create the function name as do + the action name, first letter upper case
		$functionName = 'do' . ucfirst($action);

		//Call the action and output the returned content
		echo $controller->$functionName($url);

		//Exit the foreach loop
		break;
	}
}


// If no functionName is set, we didn't found any actions. Show the error page.
if (!isset($functionName)) {
	header('HTTP/1.1 404 Not Found');
	$controller = new ErrorController();
	echo $controller->doNotFound($url);
}

