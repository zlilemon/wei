<?php
/**
 * Widget Framework
 *
 * @copyright   Copyright (c) 2008-2013 Twin Huang
 * @license     http://www.opensource.org/licenses/apache2.0.php Apache License
 */

namespace Widget\Validator;

/**
 * @package     Widget
 * @author      Twin Huang <twinh@yahoo.cn>
 */
class File extends AbstractRule
{
    protected $message = 'This value must be an existing file';
    
    /**
     * Returns the path or true
     * 
     * @var bool 
     */
    protected $abs = true;
    
    /**
     * Determine the object source is a file path, check with the include_path.
     * 
     * @return string|bool
     */
    public function __invoke($file)
    {
        if (!is_string($file)) {
            return false;
        }

        // check directly if it's absolute path
        if ('/' == $file[0] || '\\' == $file[0] || ':' == $file[1]) {
            if (is_file($file)) {
                return $this->abs ? $file : true;
            }
        }

        $full = stream_resolve_include_path($file);
        if ($full) {
            return $this->abs ? $full : true;
        }

        return false;
    }
}
