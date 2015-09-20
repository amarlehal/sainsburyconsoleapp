<?php

/**
 *
 * @author amarjitsingh
 * @name IndexController
 * @copyright <amarjit singh lehal2@hotmail.com>
 * @namespace Application\Controller
 * @package Sainsbuyrs Groccery console app
 * @version $1
 *
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Math\Rand;
use Zend\Console\Console;
use Zend\Console\Exception\RuntimeException as ConsoleException;
use Zend\Http\Client as HttpClient;
use Zend\Dom\Query;


class IndexController extends AbstractActionController
{

	public function indexAction()
	{
		return new ViewModel();
	}

    public function generatejsonAction()
    {
    	$request = $this->getRequest();

    	// Make sure that we are running in a console and the user has not tricked our
    	// application into running this action from a public web server.
    	if (!$request instanceof ConsoleRequest){
    		throw new \RuntimeException('You can only use this action from a console!');
    	}

    	// Get user email from console and check if the user used --verbose or -v flag
    	$verbose     = $request->getParam('verbose') || $request->getParam('v');

    	if (!$verbose) {
    		return $this->JsonGeneratorPlugin()->getGrocceryJsonData();
    	} else {
    		$json = $this->JsonGeneratorPlugin()->getGrocceryJsonData();
    		return "Done! The Json data for groccery items \n $json \n. The data is complete. \n";
    	}
   }
}
