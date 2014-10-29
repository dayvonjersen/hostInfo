<?php
/**
 *
 * XHTML5
 * a library file by [redacted]
 *
 * @author: [redacted]
 * @date: 6/28/2012 8:16:18 AM
 * @edit: 10/14/2013 9:35:00 PM 
 * @edit: Sat Oct  4 07:06:24 2014 --REMOVED ALL XML2ARRAY FUNCTIONALITY--
 */

class xhtml5 extends xmlWriter {
/**
 * formatting
 */
 private $indent     = true;
 const INDENT_STRING = '  ';
 const ENCODING      = 'utf-8';

/**
 * Variables
 * @access: private
 */
 private $output_html = false;

 /**
  * Constructor
  *
  * @access: public
  */
 public function __construct( $output_html = false, $indent = true ) {
  $this->indent = (bool) $indent;
  $this->output_html = (bool) $output_html;

  $this->openMemory();

  // Indentations for prettier output.
  $this->setIndent($this->indent);
  $this->setIndentString(self::INDENT_STRING);

  $this->startDocument('1.0',self::ENCODING);
  if($this->output_html)
   $this->writeDTD('html');
 }

 public function loadArray ( $html ) {
  reset($html);
  // xxx: bug, no attributes in root element
  // fixed 10/14/2013
  $root = current($html);
  $attr = $this->getAttributes($root);
  $this->addChild(key($html),$root,$attr);
 }

/**
 * __toString(void)
 *
 * @usage: <?=new xhtml5()?>
 * @access: public
 */
 public function __toString() {
  $this->writeComment("\r\n".'generated @ '.date('r')."\r\n");
  $this->endDocument();
  return $this->outputMemory();
 }

/**
 * 
 * null|array getAttributes(array &$value)
 *
 * @access: private
 */
 private function getAttributes(&$value) {
  // default return value
  $attributes = null;
  if(is_array($value) && isset($value['__attributes'])) {
   // assigns appropriate return value if key is present
   $attributes = $value['__attributes'];
   // removes the attribute array entirely so it doesn't get into the xml tree
   unset($value['__attributes']);
   // and if that was all this value had, let's destroy the array
   if(count($value) <= 1)
    $value = current($value);
  }
  return $attributes;
 }

 private function isRawXML(&$value) {
  if(is_array($value) && isset($value['__raw'])) {
   $value = current($value);
   return true;
  }
  return false;
 }

/**
 * addChild(string $element[, array|string|null $data [, array|null $attributes [, bool $write_raw_xml ]]])
 * 
 * 88mph
 *
 * @access: private
 */
 private function addChild($element, $data = null, $attributes = null) {
  $this->startElement((string)$element);
 
  // Apparently writeAttribute() has issues.
  if(is_array($attributes)) {
   foreach($attributes as $key => $value) {
    $this->startAttribute($key);
    $this->text($value);
    $this->endAttribute();
   }
  }

  $write_raw_xml = $this->isRawXML($data);

  // 1.1 jigawatts

  if(is_array($data)) {
   foreach($data as $child => $value) {

    // Lets check if this current value has attributes
    // and if so, let's take care of that now.
    $attributes = $this->getAttributes($value);
   
    // this function is not included in php, but it should be :)
    if(is_numeric_array($value)) {
      // so let's go through these similiarly named tags
      foreach($value as $v) {
       // and again, let's take care of attributes asap
       $a = $this->getAttributes($v);
       // and recurse
       $this->addChild($child,$v,$a);
      }
    } else {
     $this->addChild($child,$value,$attributes);
    }
   }
/**
 * edit 10/5/2014 6:04:15 AM
 * edit 10/6/2014 7:46:24 PM
 * previously used is_string(), which causes numbers not to print
 *
 * numbers are useful.
 *
 * Scalar variables are those containing an integer, float, string or boolean. Types array, object and resource are not scalar. 
 * 
 * is_scalar() does not consider NULL to be scalar. */
    } elseif(is_scalar($data)) {
   $write_raw_xml ? $this->writeRaw($data) : $this->text($data);
  }

  // And if $data was null? Well this will make sure we have a nice closed <tag/>
  $this->endElement();
 }
}
