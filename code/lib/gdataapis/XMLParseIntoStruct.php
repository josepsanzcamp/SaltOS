<?php

/**
 * -------------------------------------------------------------------------
 * XMLParseIntoStruct class
 * -------------------------------------------------------------------------
 * PHP versions 4 and 5
 * -------------------------------------------------------------------------
 * This is based upon the one written by orbitphreak at yahoo dot com
 * posted at the user contributed notes of http://www.php.net/xml#59742.
 * -------------------------------------------------------------------------
 */

/**
 * XMLParseIntoStruct class
 *
 * Parse XML data into an hashed structure.
 * This is based upon the one written by orbitphreak at yahoo dot com
 * posted at the user contributed notes of http://www.php.net/xml#59742.
 *
 * @author     orbitphreak at yahoo dot com
 * @author     ucb.rcdtokyo http://www.rcdtokyo.com/ucb/
 * @see        http://www.php.net/xml#59742
 *
 * Basic usage:
 * <code>
 * $xml = file_get_contents('./file.xml');
 * $parser = new XMLParseIntoStruct($xml);
 * $parser->parse();
 * var_dump($parser->getResult());
 * </code>
 */
class XMLParseIntoStruct
{
    /**
     * @var array
     * @access protected
     */
    var $result;

    /**
     * @var object
     * @access protected
     */
    var $parser;

    /**
     * @var string
     * @access protected
     */
    var $xml_data;

    /**
     * @var int
     * @access protected
     */
    var $error_code;

    /**
     * @param  string
     * @access public
     */
    function XMLParseIntoStruct($xml_data = '')
    {
        $this->__construct($xml_data);
    }

    /**
     * @param  string
     * @access public
     */
    function __construct($xml_data = '')
    {
        if ($xml_data) {
            $this->setData($xml_data);
        }
        $this->result = array();
        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, true);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, true);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'openHandler', 'closeHandler');
        xml_set_character_data_handler($this->parser, 'dataHandler');
    }

    /**
     * @param  string
     * @return void
     * @access public
     */
    function parse($xml_data = '')
    {
        if ($xml_data) {
            $this->setData($xml_data);
        }
        if (false === xml_parse($this->parser, $this->xml_data)) {
            $this->error_code = xml_get_error_code($this->parser);
            xml_parser_free($this->parser);
            return false;
        }
        xml_parser_free($this->parser);
        return true;
    }

    /**
     * @return array
     * @access public
     */
    function getResult()
    {
        return $this->result;
    }

    /**
     * @param  string
     * @return void
     * @access public
     */
    function setData($xml_data)
    {
        $this->xml_data = $xml_data;
    }

    /**
     * @param  object  $parser
     * @param  string  $name
     * @param  array   $attribs
     * @return void
     * @access public
     */
    function openHandler($parser, $name, $attribs)
    {
        $array['name'] = $name;
        if ($attribs) {
            $array['attributes'] = $attribs;
        }
        $this->result[] = $array;
    }

    /**
     * @param  object  $parser
     * @param  string  $name
     * @return void
     * @access public
     */
    function closeHandler($parser, $name)
    {
        if (count($this->result) > 1) {
            $array = array_pop($this->result);
            $index = count($this->result) - 1;
            $this->result[$index]['child'][] = $array;
        }
    }

    /**
     * @param  object  $parser
     * @param  string  $data
     * @return void
     * @access public
     */
    function dataHandler($parser, $data)
    {
        $index = count($this->result) - 1;
        if (!isset($this->result[$index]['content'])) {
            $this->result[$index]['content'] = $data;
        } else {
            $this->result[$index]['content'] .= $data;
        }
    }
}

?>
