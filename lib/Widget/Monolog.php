<?php
/**
 * Widget Framework
 *
 * @copyright   Copyright (c) 2008-2013 Twin Huang
 * @license     http://www.opensource.org/licenses/apache2.0.php Apache License
 */

namespace Widget;

use Monolog\Logger as MonologLogger;

/**
 * The wrapper for Monolog
 *
 * @author      Twin Huang <twinhuang@qq.com>
 */
class Monolog extends AbstractWidget
{
    /**
     * The name of channel
     *  
     * @var string
     */
    protected $name = 'widget';
    
    /**
     * The default log level
     * 
     * @var int
     */
    protected $level = MonologLogger::DEBUG;
    
    /**
     * The monolog handlers
     * 
     * @var array
     */
    protected $handlers = array(
        'stream' => array(
            'stream' => 'log/widget.log',
            'level' => MonologLogger::DEBUG,
        ),
    );

    /**
     * The Monolog logger object
     * 
     * @var Monolog\Logger 
     */
    protected $logger;

    /**
     * Constructor
     * 
     * @param array $options
     * @throws Exception\InvalidArgumentException When log handlder not found
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        
        // Create a logger channel
        $logger = $this->logger = new MonologLogger($this->name);

        // Add hanlders
        foreach ($this->handlers as $name => $parameters) {
            switch (true) {
                case is_array($parameters) :
                    $class = '\Monolog\Handler\\' . ucfirst($name) . 'Handler';
                    $logger->pushHandler($this->instance($class, $parameters));
                    break;
                
                case $parameters instanceof \Monolog\Handler\HandlerInterface :
                    $logger->pushHandler($parameters);
                    break;
                
                default :
                    throw new Exception\InvalidArgumentException(sprintf('Log handler "%s" not found', $name));
            }
        }
    }
    
    /**
     * Get monolog logger object or add a log record
     * 
     * @param string $message The log message
     * @return \Monolog\Logger|boolen Returns Logger obejct when $message is null, otherwise returns log result
     */
    public function __invoke($level = null, $message = null, array $context = array())
    {
        !isset($level) && $level = $this->level;
        
        return $message ? $this->logger->addRecord($level, $message, $context) : $this->logger;
    }
}