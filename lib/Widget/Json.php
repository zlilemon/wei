<?php
/**
 * Widget Framework
 *
 * @copyright   Copyright (c) 2008-2013 Twin Huang
 * @license     http://www.opensource.org/licenses/apache2.0.php Apache License
 */

namespace Widget;

/**
 * A widget to response json
 *
 * @author      Twin Huang <twinhuang@qq.com>
 */
class Json extends Response
{
    /**
     * The key name of code
     * 
     * @var string
     */
    protected $code = 'code';
    
    /**
     * The key name of message
     * 
     * @var string
     */
    protected $message = 'message';
    
    public function __invoke($message = null, $code = 0, array $append = array(), $jsonp = false)
    {
        $result = json_encode(array(
            $this->code => $code,
            $this->message => $message,
        ) + $append);
        
        if ($jsonp && $name = $this->request['callback']) {
            $this->header->set('Content-Type', 'application/javascript');
            $jsonp = $this->escape->js((string)$name);
            $result = $jsonp . '(' . $result . ')';
        } else {
            $this->header->set('Content-Type', 'application/json');
        }
        
        return parent::send($result);
    }
}