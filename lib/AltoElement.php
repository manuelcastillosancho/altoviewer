<?php

/**
 * ALTO File Viewer
 *
 * @package    AltoViewer
 * @author     Dan Field <dof@llgc.org.uk>
 * @copyright  Copyright (c) 2010 National Library of Wales / Llyfrgell Genedlaethol Cymru. (http://www.llgc.org.uk)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License 3
 * @version    $Id$
 * @link       http://www.loc.gov/standards/alto/
 * 
 **/
 
class AltoElement 
{
    protected $_id;
    
    protected $_type;

    protected $_hPos;
    
    protected $_vPos;

    protected $_height;

    protected $_width;

    protected $_content = '';
    
    /**
     * @param DOMElement $element ALTO Element 
     */
    public function __construct($element) 
    {
        $this->_type = $element->tagName;
        $this->_id = $element->getAttribute('ID');
        $this->_hPos = (float) $element->getAttribute('HPOS');
        $this->_vPos = (float) $element->getAttribute('VPOS');
        $this->_height = (float) $element->getAttribute('HEIGHT');
        $this->_width = (float) $element->getAttribute('WIDTH');
        $this->_content = $this->_extractContent($element);
    }
    
    /**
     * Scale Elements vertically and horizontally
     * @param mixed $vScale Vertical Scale ratio 
     * @param mixed $hScale Horizontal Scale ratio 
     */
    public function scale($vScale, $hScale) 
    {
        $this->_hPos   = floor($this->_hPos  * (float) $hScale);
        $this->_vPos   = floor($this->_vPos  * (float) $vScale);
        $this->_height = ceil($this->_height * (float) $vScale);
        $this->_width  = ceil($this->_width  * (float) $hScale);
    }
    
    /**
     * Get Horizontal Position of Element
     * @return int
     */
    public function getHPos() 
    {
        return $this->_hPos;
    }
    
    /**
     * Get Vertical Position of Element 
     * @return int
     */
    public function getVPos() 
    {
        return $this->_vPos;
    }

    /**
     * Get Height of Element 
     * @return int
     */
    public function getHeight() 
    {
        return $this->_height;
    }
    
    /**
     * Get Width of Element 
     * @return int
     */
    public function getWidth() 
    {
        return $this->_width;
    }

    /**
     * Get textual content associated with the element
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Extract text content from an ALTO element
     * @param DOMElement $element
     * @return string
     */
    protected function _extractContent($element)
    {
        if ($element->hasAttribute('CONTENT')) {
            return trim($element->getAttribute('CONTENT'));
        }

        $textParts = array();
        $strings = $element->getElementsByTagName('String');
        foreach ($strings as $string) {
            if ($string->hasAttribute('CONTENT')) {
                $textParts[] = trim($string->getAttribute('CONTENT'));
            }
        }

        return trim(implode(' ', $textParts));
    }
}
