
<?php

namespace Curl;

class CaseInsensitiveArray implements \ArrayAccess, \Countable, \Iterator
{

    /**
     * @var mixed[] Data storage with lower-case keys
     * @see offsetSet()
     * @see offsetExists()
     * @see offsetUnset()
     * @see offsetGet()
     * @see count()
     * @see current()
     * @see next()
     * @see key()
     */
    private $data = array();

    /**
     * @var string[] Case-Sensitive keys.
     * @see offsetSet()
     * @see offsetUnset()
     * @see key()
     */
    private $keys = array();

    /**
     * Construct
     *
     * Allow creating either an empty Array, or convert an existing Array to a
     * Case-Insensitive Array.  (Caution: Data may be lost when converting Case-
     * Sensitive Arrays to Case-Insensitive Arrays)
     *
     * @param mixed[] $initial (optional) Existing Array to convert.
     *
     * @return CaseInsensitiveArray
     *
     * @access public
     */
    public function __construct(array $initial = null)
    {
        if ($initial !== null) {
            foreach ($initial as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
    }

    /**
     * Offset Set
     *
     * Set data at a specified Offset.  Converts the offset to lower-case, and
     * stores the Case-Sensitive Offset and the Data at the lower-case indexes
     * in $this->keys and @this->data.
     *
     * @see https://secure.php.net/manual/en/arrayaccess.offseteset.php
     *
     * @param string $offset The offset to store the data at (case-insensitive).
     * @param mixed $value The data to store at the specified offset.
     *
     * @return void
     *
     * @access public
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $offsetlower = strtolower($offset);
            $this->data[$offsetlower] = $value;
            $this->keys[$offsetlower] = $offset;
        }
    }

    /**
     * Offset Exists
     *
     * Checks if the Offset exists in data storage.  The index is looked up with
     * the lower-case version of the provided offset.
     *
     * @see https://secure.php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param string $offset Offset to check
     *
     * @return bool If the offset exists.
     *
     * @access public
     */
    public function offsetExists($offset)