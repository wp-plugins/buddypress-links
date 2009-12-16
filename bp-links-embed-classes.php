<?php
/**
 * BP_Links Embed base classes
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */

/**
 * Define exception classes so we can try/catch them and report errors
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
abstract class BP_Links_Embed_Exception extends Exception {}

/**
 * Exceptions which are fatal and should be handled gracefully
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
final class BP_Links_Embed_Fatal_Exception extends BP_Links_Embed_Exception {}

/**
 * Exceptions which should be displayed to the user
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
final class BP_Links_Embed_User_Exception extends BP_Links_Embed_Exception {}

/**
 * Services that support embedding must adhere to this interface
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
interface BP_Links_Embed_From_Data
{
	/**
	 * Construct this service from an embed data object
	 *
	 * @param BP_Links_Embed_Data $embed_data
	 * @return boolean
	 */
	public function from_data( BP_Links_Embed_Data $embed_data );

	/**
	 * Return title
	 *
	 * @return string
	 */
	public function title();

	/**
	 * Return description
	 *
	 * @return string
	 */
	public function description();

	/**
	 * Return URL of image (also used to create avatar)
	 *
	 * @return string
	 */
	public function image_url();

	/**
	 * Return URL of thumbnail image
	 *
	 * @return string
	 */
	public function image_thumb_url();
	
	/**
	 * Return URL of large thumbnail image
	 * 
	 * If there is no large thumbnail available, then just return the standard one!
	 *
	 * @return string
	 */
	public function image_large_thumb_url();

	/**
	 * Return HTML embed code (for gallery)
	 *
	 * @return string
	 */
	public function html();

	/**
	 * Return service name
	 *
	 * @return string
	 */
	public function service_name();
}

/**
 * Services that support embedding from a URL must adhere to this interface
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
interface BP_Links_Embed_From_Url
	extends BP_Links_Embed_From_Data
{
	/**
	 * Construct/populate data for/from a URL
	 *
	 * @param string $url
	 * @return boolean
	 */
	public function from_url( $url );

	/**
	 * Return regex pattern to match the service URL
	 *
	 * This pattern must be compatible with PHP PCRE and the Javascript RegExp() object
	 *
	 * @example /^http:\/\/(www\.)?foo.org\/view/
	 * @return string
	 */
	public function from_url_pattern();
	
	/**
	 * Return URL (web page containing original content)
	 *
	 * @return string
	 */
	public function url();
}

/**
 * Services that support embedding from HTML must adhere to this interface
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
interface BP_Links_Embed_From_Html
	extends BP_Links_Embed_From_Data
{
	/**
	 * Construct/populate data from HTML
	 *
	 * @param string $html
	 * @return boolean
	 */
	public function from_html( $html );
}

/**
 * Services that support embedding from XML must adhere to this interface
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
interface BP_Links_Embed_From_Xml
	extends BP_Links_Embed_From_Data
{
	/**
	 * Construct/populate data from XML
	 *
	 * @param string $xml
	 * @return boolean
	 */
	public function from_xml( $xml );
}

/**
 * Services that support embedding from oEmbed must adhere to this interface
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
interface BP_Links_Embed_From_Oembed
	extends BP_Links_Embed_From_Data
{
	/**
	 * Construct/populate data from oEmbed
	 *
	 * @param string $oembed
	 * @return boolean
	 */
	public function from_oembed( $oembed );
}

/**
 * Services that support embedding from JSON must adhere to this interface
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
interface BP_Links_Embed_From_Json
	extends BP_Links_Embed_From_Data
{
	/**
	 * Construct/populate data from JSON
	 *
	 * @param string $json
	 * @return boolean
	 */
	public function from_json( $json );
}

/**
 * Singleton embed service registry and prototype object factory
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
final class BP_Links_Embed
{
	// service interface prefix
	const INTERFACE_PREFIX = 'BP_Links_Embed_';

	// valid service interface suffixes
	const INTERFACE_URL = 'From_Url';
	const INTERFACE_HTML = 'From_Html';
	const INTERFACE_XML = 'From_Xml';
	const INTERFACE_OEMBED = 'From_Oembed';
	const INTERFACE_JSON = 'From_Json';

	/**
	 * Singleton instance
	 * 
	 * @var BP_Links_Embed 
	 */
	private static $instance;

	/**
	 * An array of registered embed services (prototype objects)
	 *
	 * @see BP_Links_Embed_Service
	 * @var array
	 */
	private $services = array();

	/**
	 * Constructor, denied!
	 */
	private function __construct() {}

	/**
	 * Register an embed service (object)
	 * 
	 * @param BP_Links_Embed_Service $service
	 * @return void
	 */
	final public function register_service( BP_Links_Embed_Service $service )
	{
		// make sure service hasn't been registered already
		foreach ( $this->services as $service_protoype ) {
			if ( get_class( $service ) === get_class( $service_protoype ) ) {
				return true;
			} elseif ( $service->key() === $service_protoype->key() ) {
				// service key already registered, possible hijack attempt, what do we do?
				throw new BP_Links_Embed_Fatal_Exception( sprintf( 'Service %s has already been registered.', get_class( $service_protoype ) ) );
			}
		}

		// append service to registry
		$this->services[$service->key()] = $service;
		return true;
	}

	/**
	 * Loop through registered services and try to locate using callback method
	 *
	 * @param string $interface
	 * @param string $string
	 * @return BP_Links_Embed_Service|false
	 */
	final public function locate_service( $interface, $string )
	{
		// need a non-empty string to continue
		if ( empty( $string ) === true || is_string( $string ) === false ) {
			throw new BP_Links_Embed_Fatal_Exception( 'Second argument must be a string' );
		}

		// ALWAYS switch through these for security reasons
		// and to handle default string cleaning
		switch ( $interface ) {
			case self::INTERFACE_URL:
			case self::INTERFACE_XML:
			case self::INTERFACE_OEMBED:
			case self::INTERFACE_JSON:
			case self::INTERFACE_HTML:
				$string_clean = trim( $string );
				break;
			default:
				throw new BP_Links_Embed_Fatal_Exception( 'Invalid interface' );
		}

		// name of the service interface, and locate method
		$service_interface = self::INTERFACE_PREFIX . $interface;
		$locate_method = strtolower( $interface );

		// loop through all registered services
		foreach ( $this->services as $service_protoype ) {
			// if service supports the required interface, try to construct
			if ( $service_protoype instanceof $service_interface ) {
				// clone it!!! otherwise a reference to the registered object will be returned
				$service = clone $service_protoype;
				// exec locate method, return service on success
				if ( call_user_func( array( $service, $locate_method ), $string_clean ) ) {
					return $service;
				}
			}
		}

		// no service could handle the string with given method
		return false;
	}

	/**
	 * Try to load a service from embed data object or serialized string
	 *
	 * @param BP_Links_Embed_Data|string $embed_data_mixed
	 * @param string $service_key providing this will speed up loading
	 * @return BP_Links_Embed_Service|false
	 */
	final public function load_service( $embed_data_mixed, $service_key = null )
	{
		// did we get a key?
		if ( empty( $service_key ) === false && strlen( $service_key ) == 32 ) {

			// look for service in registry
			if ( array_key_exists( $service_key, $this->services ) && $this->services[$service_key] instanceof BP_Links_Embed_Service ) {

				// clone it!!!
				$service = clone $this->services[$service_key];

				// load data based on type
				if ( $embed_data_mixed instanceof BP_Links_Embed_Data ) {
					$load_result = $service->from_data( $embed_data_mixed );
				} elseif ( is_string( $embed_data_mixed ) === true ) {
					$load_result = $service->import_data( $embed_data_mixed );
				} else {
					throw new BP_Links_Embed_Fatal_Exception( 'Invalid data received' );
				}

				// if load was sucessful, return the service object
				return ( $load_result === true ) ? $service : false;
			}
	
			// service not found in registry
			return null;
			
		} else {
			// no key provided, use search method
			return $this->load_service_search( $embed_data_mixed );
		}

	}

	/**
	 * Try to load a service when we don't have the service key
	 *
	 * @param BP_Links_Embed_Data|string $embed_data_mixed
	 * @return BP_Links_Embed_Service|false
	 */
	private function load_service_search( $embed_data_mixed )
	{
		// if we have a string, unserialize it
		if ( is_string( $embed_data_mixed ) === true ) {
			$embed_data = unserialize( base64_decode( $embed_data_mixed ) );
		}

		// if we have a valid embed data object, try to load service
		if ( $embed_data instanceof BP_Links_Embed_Data ) {

			// loop through services and try to match keys
			foreach ( $this->services as $service_protoype ) {
				// clone it!!!
				$service = clone $service_protoype;
				// if keys match, try to hydrate the service
				if ( $service->key() === $embed_data->key() ) {
					return ( $service->from_data( $embed_data ) ) ? $service : false;
				}
			}
			
			// no service key match found
			return null;
			
		} else {
			throw new BP_Links_Embed_Fatal_Exception( 'Invalid data received' );
		}
	}

	/**
	 * Return array of URL regex patterns for services that support an interface
	 *
	 * @param string $interface
	 * @return array
	 */
	final public function get_service_patterns( $interface )
	{
		// the array of patterns to return
		$patterns = array();

		// ALWAYS switch through these for security reasons
		switch ( $interface ) {
			case self::INTERFACE_URL:
				break;
			default:
				throw new BP_Links_Embed_Fatal_Exception( 'Invalid interface' );
		}

		// name of the service interface, and locate method
		$service_interface = self::INTERFACE_PREFIX . $interface;
		$locate_method = strtolower( $interface ) . '_pattern';

		// loop through all registered services
		foreach ( $this->services as $service ) {
			// if service supports the required interface, get pattern
			if ( $service instanceof $service_interface ) {
				// exec pattern method and append to array
				$patterns[] = call_user_func( array( $service, $locate_method ) );
			}
		}

		return $patterns;
	}

	/**
	 * Get singleton instance
	 *
	 * @return BP_Links_Embed
	 */
	final public static function GetInstance()
	{
		if ( empty( self::$instance ) ) {
			self::$instance = new BP_Links_Embed();
			// register natively supported services
			self::$instance->register_service( new BP_Links_Embed_Service_PicApp() );
			self::$instance->register_service( new BP_Links_Embed_Service_Fotoglif() );
			self::$instance->register_service( new BP_Links_Embed_Service_YouTube() );
			self::$instance->register_service( new BP_Links_Embed_Service_Flickr() );
			self::$instance->register_service( new BP_Links_Embed_Service_MetaCafe() );
		}
		return self::$instance;
	}

	/**
	 * Register an embed service object statically
	 *
	 * @param BP_Links_Embed_Service $service
	 * @return void
	 */
	final public static function RegisterService( BP_Links_Embed_Service $service )
	{
		return self::GetInstance()->register_service( $service );
	}

	/**
	 * Try to statically load a service when all we have is the embed data object or serialized string thereof
	 *
	 * @param BP_Links_Embed_Data|string $embed_data_mixed embed data object or serialized string thereof
	 * @return BP_Links_Embed_Service|false
	 */
	final public static function LoadService( $embed_data_mixed )
	{
		return self::GetInstance()->load_service( $embed_data_mixed );
	}

	/**
	 * Attempt to load an embed service object from a URL string
	 *
	 * @param string $url
	 * @return BP_Links_Embed_Service|false
	 */
	final public static function FromUrl( $url )
	{
		return self::GetInstance()->locate_service( self::INTERFACE_URL, $url );
	}

	/**
	 * Return array of URL regex patterns for services that support URL embedding
	 *
	 * @return array
	 */
	final public static function FromUrlPatterns()
	{
		return self::GetInstance()->get_service_patterns( self::INTERFACE_URL );
	}

	/**
	 * Attempt to load an embed service object from HTML markup
	 *
	 * @param string $html
	 * @return BP_Links_Embed_Service|false
	 */
	final public static function FromHtml( $html )
	{
		return self::GetInstance()->locate_service( self::INTERFACE_HTML, $html );
	}

	/**
	 * Attempt to load an embed service object from XML markup
	 *
	 * @param string $xml
	 * @return BP_Links_Embed_Service|false
	 */
	final public static function FromXml( $xml )
	{
		return self::GetInstance()->locate_service( self::INTERFACE_XML, $xml );
	}

	/**
	 * Attempt to load an embed service object from oEmbed markup
	 *
	 * @param string $oembed
	 * @return BP_Links_Embed_Service|false
	 */
	final public static function FromOembed( $oembed )
	{
		return self::GetInstance()->locate_service( self::INTERFACE_OEMBED, $oembed );
	}

	/**
	 * Attempt to load an embed service object from a JSON encoded string
	 *
	 * @param string $json
	 * @return BP_Links_Embed_Service|false
	 */
	final public static function FromJson( $json )
	{
		return self::GetInstance()->locate_service( self::INTERFACE_JSON, $json );
	}
}

/**
 * Embed data storage class
 *
 * The intented use of this class is for caching SMALL bits of API or other
 * meta data for later use. DO NOT use for session or any kind of temp data.
 * Hashing is used to check if the data has been modified after serialization,
 * but since the hash is public, its possible to simply change the serialized
 * data and then generate a new hash. This is more of a sanity check than
 * a security measure.
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
final class BP_Links_Embed_Data
{
	/**
	 * Service key
	 *
	 * @var string
	 */
	protected $k;

	/**
	 * Data hash
	 *
	 * @var string
	 */
	protected $h;

	/**
	 * Properties to store
	 *
	 * @var array
	 */
	protected $p = array();

	/**
	 * An embed service must pass itself to the constructor for key exchange
	 *
	 * @param BP_Links_Embed_Service $service
	 */
	final public function __construct( BP_Links_Embed_Service $service )
	{
		$this->k = $service->key();
	}

	/**
	 * Return key that was received from the service
	 *
	 * @return string
	 */
	final public function key()
	{
		return $this->k;
	}

	/**
	 * Facilitate setting overload properties, with constraints
	 *
	 * @param string $p property to set, must be 1 to 12 word chars in length
	 * @param string $v value
	 */
	final public function __set( $p, $v )
	{
		if ( preg_match( '/^\w{1,24}$/', $p ) ) {
			$this->p[$p] = $v;
		} else {
			throw new BP_Links_Embed_Fatal_Exception( 'Property name must contain only word chars and be 1 to 24 chars in length!' );
		}
	}

	/**
	 * Facilitate getting overload properties
	 *
	 * @param string $p property to get
	 * @return mixed
	 */
	final public function __get( $p )
	{
        if ( array_key_exists( $p, $this->p ) ) {
            return $this->p[$p];
        } else {
			throw new BP_Links_Embed_Fatal_Exception( sprintf( 'Property name "%s" has not been set!', $p ) );
		}
	}

	/**
	 * Ensure that isset works on overloaded properties
	 *
	 * @param tring $p property to check
	 * @return boolean
	 */
    final public function __isset( $p )
	{
        return isset( $this->p[$p] );
    }

	/**
	 * Ensure that unset works on overloaded properties
	 *
	 * @param tring $p property to unset
	 * @return boolean
	 */
    final public function __unset( $p )
	{
        unset( $this->p[$p] );
    }

	/**
	 * When serialize() is called on this object, make hash and specify members to store.
	 *
	 * @return array
	 */
    final public function __sleep()
    {
		// calculate hash of serialized properties array
		$this->h = md5( serialize( $this->p ) );

		// only serialize these members
        return array( 'k', 'h', 'p' );
    }

	/**
	 * When unserialize() is called on this object, check hash.
	 *
	 * @return array
	 */
    final public function  __wakeup()
    {
		// calculate hash of serialized properties array
		$hash = md5( serialize( $this->p ) );

		// does it match the stored hash?
		if ( $hash !== $this->h ) {
			// nope, possible hijack
			throw new BP_Links_Embed_Fatal_Exception( 'Data was modified.' );
		}
    }
}

/**
 * An abstract embed service
 *
 * @package BP_Links
 * @author Marshall Sorenson
 */
abstract class BP_Links_Embed_Service
	implements BP_Links_Embed_From_Data
{
	/**
	 * Unique key which is an MD5 hash of the class name
	 *
	 * To `hijack` another service, all you have to do is write
	 * a class with a name who's hash matches the other service's
	 * class name hash, and register your service first. You can mess
	 * with their data and output all you want until they register their
	 * service and a fatal exception is thrown.  Have fun.
	 * 
	 * @var string
	 */
	private $key;

	/**
	 * @var BP_Links_Embed_Data
	 */
	private $data;

	/**
	 * Constructor, denied!
	 * Override one or more of the from_*() methods to contruct
	 */
	final public function __construct() {}

	/**
	 * Return api key set by concrete embed service class.
	 *
	 * @return string
	 */
	final public function key()
	{
		// check if set, and set (cache) if necessary
		if ( !$this->key ) {
			// MD5 hash of the class name
			$this->key = md5( get_class( $this ) );
		}
		return $this->key;
	}

	/**
	 * Make data object available to concrete classes
	 *
	 * @return BP_Links_Embed_Data
	 */
	final protected function data()
	{
		// initialize data object if necessary
		if ( !$this->data instanceof BP_Links_Embed_Data ) {
			$this->data = new BP_Links_Embed_Data( $this );
		}
		return $this->data;
	}

	/**
	 * Return byte stream representation of data
	 *
	 * @return string|null serialized object that is base64 encoded
	 */
	final public function export_data()
	{
		if ( !empty( $this->data ) ) {
			return base64_encode( serialize( $this->data ) );
		} else {
			return null;
		}
	}

	/**
	 * Import byte stream representation of data
	 *
	 * @param $string serialized object that is base64 encoded
	 * @return boolean
	 */
	final public function import_data( $string )
	{
		// need a non-empty string to continue
		if ( empty( $string ) === false && is_string( $string ) === true ) {

			// resurrect object
			$embed_data = unserialize( base64_decode( $string ) );
			
			// if we have a valid embed data object, try to load data
			if ( $embed_data instanceof BP_Links_Embed_Data ) {
				return $this->from_data( $embed_data );
			} else {
				throw new BP_Links_Embed_Fatal_Exception( 'Invalid data received' );
			}
		} else {
			throw new BP_Links_Embed_Fatal_Exception( 'Argument must be a string' );
		}
	}

	/**
	 * Construct this service from an embed data object
	 *
	 * @param BP_Links_Embed_Data $embed_data
	 */
	final public function from_data( BP_Links_Embed_Data $embed_data )
	{
		// do keys match?
		if ( $this->key() === $embed_data->key() ) {
			$this->data = $embed_data;
			return true;
		}
		
		return false;
	}

	//
	// optional concrete methods
	//

	public function image_width() { return false; }
	public function image_height() { return false; }
	public function avatar_class() { return false; }
	public function avatar_play_video() { return false; }
	public function avatar_play_photo() { return false; }
	public function avatar_max_width() { return false; }
	public function avatar_max_height() { return false; }
	public function avatar_only() { return false; }


	//
	// api helpers
	//

	/**
	 * Fetch remote API raw data from a URL
	 *
	 * @param string $url
	 * @return string
	 */
	final protected function api_fetch( $url )
	{
		// get RSS2 feed data for this video
		$response = wp_remote_get( $url );

		// only return data from a successful request
		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
			return wp_remote_retrieve_body( $response );
		} else {
			throw new BP_Links_Embed_User_Exception( $this->err_api_fetch() );
		}
	}

	//
	// string helpers
	//
	
	/**
	 * Prepare a string for processing by a service
	 *
	 * @param string $string
	 * @return string
	 */
	protected function clean_string( $string )
	{
		// just a trim
		return stripslashes( trim( $string ) );
	}

	/**
	 * Prepare a string for processing by a service
	 *
	 * @param string $string
	 * @return string
	 */
	protected function deep_clean_string( $string )
	{
		// start with a basic cleaning
		$ret_string = $this->clean_string( $string );
		// convert newlines, carriage returns, tabs and two or more spaces into one space
		$ret_string = preg_replace( '/[\n\r\t]+|\s{2,}/u', ' ', $ret_string );
		// all done
		return $ret_string;
	}
	
	//
	// error message helpers
	//

	final protected function err_embed_url()
	{
		return sprintf( __( 'The URL you entered is not a valid %1$s link.', 'buddypress-links' ), $this->service_name() );
	}

	final protected function err_embed_code()
	{
		return sprintf( __( 'The code you entered is not valid %1$s embedding code.', 'buddypress-links' ), $this->service_name() );
	}
	
	final protected function err_api_fetch()
	{
		return sprintf( __( 'Downloading content details from %1$s failed.', 'buddypress-links' ), $this->service_name() );
	}
}
?>
