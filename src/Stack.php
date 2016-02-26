<?php

namespace jlawrence\eos;

/**
 * Basic Stack Class.
 *
 * Created for use with eqEOS. May eventually be replaced with native
 * PHP functions `array_pop()`, `array_push()`, and `end()`
 *
 * @author Jon Lawrence <jlawrence11@gmail.com>
 * @copyright Copyright ©2005-2015 Jon Lawrence
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL 2.1 License
 * @package Math
 * @version 0.1
 */
class Stack {
    private $index;
    private $locArray;

    /**
     * Constructor
     *
     * Initializes the stack
     */
    public function __construct() {
        //define the private vars
        $this->locArray = array();
        $this->index = -1;
    }

    /**
     * Peek
     *
     * Will view the last element of the stack without removing it
     *
     * @return mixed An element of the array or false if none exist
     */
    public function peek() {
        if($this->index > -1)
            return $this->locArray[$this->index];
        else
            return false;
    }

    /**
     * Poke
     *
     * Will add an element to the end of the stack
     *
     * @param mixed $data Element to add
     */
    public function poke($data) {
        $this->locArray[++$this->index] = $data;
    }

    /**
     * Push
     *
     * Alias of {@see Stack::poke()}
     * Adds element to the stack
     *
     * @param mixed $data Element to add
     */
    public function push($data) {
        //alias for 'poke'
        $this->poke($data);
    }

    /**
     * Pop
     *
     * Retrieves an element from the end of the stack, and removes it from
     * the stack at the same time. If no elements, returns boolean false
     *
     * @return mixed Element at end of stack or false if none exist
     */
    public function pop() {
        if($this->index > -1)
        {
            $this->index--;
            return $this->locArray[$this->index+1];
        }
        else
            return false;
    }

    /**
     * Clear
     *
     * Clears the stack to be reused.
     */
    public function clear() {
        $this->index = -1;
        $this->locArray = array();
    }

    /**
     * Get Stack
     *
     * Returns the array of stack elements, keeping all, indexed at 0
     *
     * @return mixed Array of stack elements or false if none exist.
     */
    public function getStack() {
        if($this->index > -1)
        {
            return array_values($this->locArray);
        }
        else
            return false;
    }
}

?>