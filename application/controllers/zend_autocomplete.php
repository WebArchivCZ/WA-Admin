<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package Core
 *
 * Generates a file to aid Zend Studio's code completion.
 * Kohana uses class suffixes which aren't used when the class is instantiated.
 * This script builds a lookup table of class names minus the suffix
 *
 * @author Peter Bowyer <peter@mapledesign.co.uk>
 * @thanks Maple Design Ltd - http://www.mapledesign.co.uk/code/
 * @version 1.2 (Kohana 2.2 compatible)
 * @todo Strip out comments before scanning files, so phrases like
 * 'interface for finding, creating, and deleting cached' don't mess up the regex!
 * The ReflectionParameter class would be the 'proper' way to tackle this problem...
 * See: http://uk3.php.net/manual/en/language.oop5.reflection.php#language.oop5.reflection.reflectionparameter
 */
class Zend_ide_Controller extends Controller {

	public function index()
	{
	  $out = "<?php\n".
           "// auto-generated by Kohana-Zend Autocomplete\n".
           "// Developed by Peter Bowyer, Maple Design (http://www.mapledesign.co.uk/code/\n".
           "// Last generated: ".date('Y-m-d H:i:s T')."\n\n";
	  $endings = array('_Controller', '_Core');
		$files = Kohana::list_files('', true);
		#print_r($files);
		// Regex taken from Symfony
		// Now used as backup for classes that don't have a __construct() method
    $regex2 = '~^[\w|\040]*(class|interface)[\040]+([\w]+)~mi';
    $regex = '~[\w|\040]*(class|interface)[\040]+([\w]+).+?function\s+__construct[\040]*\([\040]*(.*?)[\040]*\)\s*\n~si';
    foreach ($files as $file)
    {
      if (!is_dir($file) && !strstr($file, 'vendor')) {
        // We exclude external libraries in 'vendor' directories
        $file_contents = file_get_contents($file);
        preg_match_all($regex, $file_contents, $classes);
        #if (strpos($file, 'URI') !== false)
        #print_r($classes);

        if (!isset($classes[2][0]) || (isset($classes[2][0]) && strlen($classes[2][0]) == 0)) {
        	// No __construct method. Run the other regex.
        	preg_match_all($regex2, $file_contents, $classes);

          for ($i = 0; $i < count($classes[2]); $i++) {

            // See if we have a suffix to remove
            foreach ($endings as $ending) {
            	if (preg_match("!$ending\$!i", $classes[2][$i])) {
            	  $newclass = preg_replace("!$ending\$!i", '', $classes[2][$i]);
            		$out .= "// File $file:\nClass $newclass extends {$classes[2][$i]} {}\n\n";
            	}
            }
          }
        } else {
          for ($i = 0; $i < count($classes[2]); $i++) {

            // See if we have a suffix to remove
            foreach ($endings as $ending) {
            	if (preg_match("!$ending\$!i", $classes[2][$i])) {
            	  $newclass = preg_replace("!$ending\$!i", '', $classes[2][$i]);
            		$out .= "// File $file:\nClass $newclass extends {$classes[2][$i]} { function __construct({$classes[3][$i]}); }\n\n";
            	}
            }
          }
        }

      }
    }

    file_put_contents(APPPATH.'/cache/zend_autocomplete.php', $out);

    print APPPATH.'/cache/zend_autocomplete.php was successfully generated';
	}
}