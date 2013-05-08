<?php
/**
 * PHP YAML FrontMatter Class
 * An easy to use class for handling YAML frontmatter in PHP.
 *
 * @author David D'hont <info@daviddhont.com>
 * @package YAML-FrontMatter
 * @license http://unlicense.org UnLicense
 * @link https://github.com/Blaxus
 * @link http://daviddhont.com/donate/
 */
class FrontMatter
{
    private $data;

    /**
     * Constructor method, checks a file and then puts the contents into custom strings for usage
     * @param [string] $file The input file
     */
    public function __construct($file)
    {
        $file = $this->Read($file);
        $this->yaml_separator = "---\n";
        $fm = $this->FrontMatter($file);

        foreach($fm as $key => $value)
        {
            $this->data[$key] = $value;
        }
    }

    /**
     * test
     */
    public function fetch($key)
    {
        return $this->data[$key];
    }

    // CUSTOM --
    /**
     * fetchKeys method returns an array of all meta data without the content
     * @return [array] collection of all meta keys provided to FrontMatter
     */
    public function fetchKeys() {

      // Cache the keys so we don't edit the native object data
      $keys = $this->data;

      // Remove $data[content] from the keys so we only have the meta data
      array_pop($keys);

      return $keys;
    }
    // -- CUSTOM

    /**
     * FrontMatter method, rturns all the variables from a YAML Frontmatter input
     * @param  [string] $input The input string
     * @return [array]  $final returns all variables in an array
     */
    function FrontMatter($input)
    {
        if (!$this->startsWith($input, $this->yaml_separator)) {
          // No front matter
          // Store Content in Final array
          $final['content'] = $input;
          // Return Final array
          return $final;
        }

        // Explode Seperators. At most, make three pieces out of the input file
        $document = explode($this->yaml_separator,$input, 3);


        switch( sizeof($document) ) {
          case 0:
          case 1:
            // Empty document
            $front_matter = "";
            $content = "";
            break;
          case 2:
            // Only front matter given
            $front_matter = $document[1];
            $content = "";
            break;
          default:
            // Normal document
            $front_matter = $document[1];
            $content = $document[2];
        }

        // Split lines in front matter to get variables
        $front_matter = explode("\n",$front_matter);
        foreach($front_matter as $variable)
        {
            // Explode so we can see both key and value
            $var = explode(": ",$variable);

            // Ignore empty lines
            if (count($var) > 1) {

              // Store Key and Value
              $key = $var[0];
              $val = $var[1];

              // Store Content in Final array
              $final[$key] = $val;
            }
        }

        // Store Content in Final array
        $final['content'] = $content;

        // Return Final array
        return $final;
    }

    /**
     * A convenience wrapper around strpos to check the start of a string
     * From http://stackoverflow.com/a/860509/270334
     * @return [string] starts with $needle
     */
    private function startsWith($haystack,$needle,$case=true)
    {
       if($case)
           return strpos($haystack, $needle, 0) === 0;
       return stripos($haystack, $needle, 0) === 0;
    }

    /**
     * Read Method, Read file and returns it's contents
     * @return [string] $data returned data
     */
    protected function Read($file)
    {
        // Open File
        $fh = fopen($file, 'r');

        // Read Data
        $data = fread($fh, filesize($file));

        // Fix Data Stream to be the exact same format as PHP's strings
        $data = str_replace(array("\r\n", "\r", "\n"), "\n", $data);

        // Close File
        fclose($fh);

        // Return Data
        return $data;
    }
}