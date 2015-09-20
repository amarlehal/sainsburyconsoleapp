<?php

/**
 *
 * @author amarjitsingh
 * @name JsonGeneratorPlugin
 * @copyright <amarjit singh lehal2@hotmail.com>
 * @namespace Application\Controller\Plugin
 * @package Sainsbuyrs Groccery console app
 * @version $1
 *
 */
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Http\Client as HttpClient;
use Zend\Dom\Query;
use Zend\Db\Sql\Ddl\Column\Float;
use Zend\Dom\Css2Xpath;
use Zend\Server\Reflection\Node;

class JsonGeneratorPlugin extends AbstractPlugin {
	const GROCCERYURL = 'http://www.sainsburys.co.uk/webapp/wcs/stores/servlet/gb/groceries/get-ideas/features/pc10-great-offers-on-selected-veg?langId=44&storeId=10151&krypto=WI1Et0XGenGAQffuR%2BWpiOir4U%2B7QuiNf1xKG%2FbX%2Fs%2BkgW2D35U4%2Fs315qPY5G9qPSTsfL24ca4t%0AcFrYvYR2%2FJJ9dNYB%2Fsbb7%2F%2F1NMlOzQVsePQEEIUdQUJbsFUR988a&ddkey=http:gb/groceries/get-ideas/features/pc10-great-offers-on-selected-veg';

	public function getGrocceryJsonData() {
		$result = $this->getNewResponse ( '' );
		// content of the web
		$body = $result->getBody ();
		$dom = new Query ( $body );
		// get div with class="gridItem" and h3 a 's NodeList
		$contents = $dom->execute ( '.gridItem' );
		$content = '';
		$grocceryProductArray = array ();
		foreach ( $contents as $key => $r ) {

			// per h3 NodeList, has element with tagName = 'a'
			// DOMElement get Element with tagName = 'a'
			$helement = $r->getElementsByTagName ( "h3" )->item ( 0 );
			$aelement = $helement->getElementsByTagName ( "a" )->item ( 0 );
			if ($aelement->hasAttributes ()) {

				// get a tag link for the item details and make a new call for description
				$descriptionResult = $this->getNewResponse ( $aelement->getAttributeNode ( 'href' )->nodeValue );
				$array ['title'] = trim ( $aelement->textContent );
				$descriptionBody = $descriptionResult->getBody ();
				$descdom = new Query ( $descriptionBody );
				// get div with class="productText"
				$desccontents = $descdom->execute ( 'div .productText' );
				$kb = ( int ) (mb_strlen ( $descriptionBody, 'UTF-8' ) / 1024);
				$array ['size'] = $kb . 'kb';
				foreach ( $desccontents as $node => $value ) {
					$hpdelement = $value->getElementsByTagName ( "p" )->item ( 0 );
					$array ['description'] = trim ( $hpdelement->textContent );
				}
			}

			$priceElement = $this->getElementByClass ( $r, 'div', 'pricing', $offset = 0 );
			$pelement = $priceElement->getElementsByTagName ( "p" )->item ( 0 );
			$array ['price'] = $this->parsePrice ( trim ( $pelement->textContent ) );
			$grocceryProductArray ['results'] [] = $array;
		}
		// sum the prices to get total
		$grocceryProductArray ['total'] = $this->getProductsPriceTotal ( $grocceryProductArray );

		return \Zend\Json\Json::encode ( $grocceryProductArray );
	}

	/**
	 * calculate total
	 */
	public function getProductsPriceTotal(array $grocceryProductArray) {
		return array_sum ( array_map ( function ($element) {
			return $element ['price'];
		}, $grocceryProductArray ['results'] ) );
	}

	/**
	 *
	 * @param string $url
	 * @return insatnce of Zend\Http\Client
	 */
	public function getNewResponse($url = NULL) {
		$client = new HttpClient ();

		/**
		 * Incase of 2nd call , use product url
		 */
		if ($url != NULL) {
			$client->setUri ( $url );
		}

		/**
		 * check if it is 2nd call then use the header cookies so that we can use the same session to avoid
		 */
		/**
		 * sending more traffic to the site for the performance point of view
		 */
		if (isset ( $_SESSION ['cookiejar'] ) && $_SESSION ['cookiejar'] instanceof \Zend\Http\Cookies) {

			$cookieJar = $_SESSION ['cookiejar'];
		} else {
			// set Curl Adapter
			$client->setAdapter ( 'Zend\Http\Client\Adapter\Curl' );

			// $response = $this->getResponse ();

			// keep the conntection alive for multiple calls
			$client->setOptions ( array (
					'keepalive' => true
			) );

			// set content-type
			// $httpResponse->getHeaders ()->addHeaderLine ( 'content-type', 'text/html; charset=utf-8' );
			$client->setUri ( SELF::GROCCERYURL );

			$result = $client->send ();
			$cookieJar = \Zend\Http\Cookies::fromResponse ( $result, SELF::GROCCERYURL );

			$_SESSION ['cookiejar'] = $cookieJar;

			$client->resetParameters ();

			return $result;
		}

		$connectionCookies = $cookieJar->getMatchingCookies ( $client->getUri () );

		// set the cookies for the 2nd call
		$client->setCookies ( $this->getConntetionCookies ( $connectionCookies ) );

		$response = $client->send ();

		return $response;
	}

	/**
	 *
	 * @param
	 *        	$connectionCookies
	 * @return array
	 */
	protected function getConntetionCookies($connectionCookies) {
		$cookieArray = array ();

		if (count ( $connectionCookies )) {
			foreach ( $connectionCookies as $name => $value ) {
				$cookieArray [$value->getName ()] = $value->getValue ();
			}
		}

		return $cookieArray;
	}

	/**
	 *
	 * @name parsePrice
	 * @param string $priceString
	 * @tutorial this method is used to parse and format product price
	 * @return Float
	 * @access protected
	 */
	protected function parsePrice($priceString = NULL) {
		list ( $price, $unit ) = explode ( '/', $priceString );
		$price = substr ( $price, 2 );

		return number_format ( ( float ) ($price), 2 );
	}

	/**
	 *
	 * @name getElementByClass
	 * @tutorial this method is used to get the price node
	 * @param nodelist $parentNode
	 * @param node $tagName
	 * @param Css2Xpath $className
	 * @param number $offset
	 * @return Node
	 */
	public function getElementByClass(&$parentNode, $tagName, $className, $offset = 0) {
		$response = false;

		$childNodeList = $parentNode->getElementsByTagName ( $tagName );
		$elementCount = 0;
		for($i = 0; $i < $childNodeList->length; $i ++) {
			$temp = $childNodeList->item ( $i );

			if (stripos ( $temp->getAttribute ( 'class' ), $className ) !== false) {

				if ($elementCount == $offset) {
					$response = $temp;
					break;
				}

				$elementCount ++;
			}
		}

		return $response;
	}
}