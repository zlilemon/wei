<?php
/**
 * Widget Framework
 *
 * @copyright   Copyright (c) 2008-2013 Twin Huang
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 */

namespace Widget;

/**
 * The base class for all services
 *
 * @author   Twin Huang <twinhuang@qq.com>
 */
abstract class Base
{
    /**
     * The service provider map
     *
     * @var array
     */
    protected $providers = array();

    /**
     * The service container object
     *
     * @var Widget
     */
    protected $widget;

    /**
     * Constructor
     *
     * @param array $options The property options
     * @throws \InvalidArgumentException When option "widget" is not an instance of "Widget\Widget"
     */
    public function __construct(array $options = array())
    {
        $this->setOption($options);

        if (!isset($this->widget)) {
            $this->widget = Widget::getContainer();
        } elseif (!$this->widget instanceof Widget) {
            throw new \InvalidArgumentException(sprintf('Option "widget" of class "%s" should be an instance of "Widget\Widget"', get_class($this)), 1000);
        }
    }

    /**
     * Set option property value
     *
     * @param string|array $name
     * @param mixed $value
     * @return $this
     */
    public function setOption($name, $value = null)
    {
        // Set options
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->setOption($k, $v);
            }
            return $this;
        }

        if (method_exists($this, $method = 'set' . $name)) {
            return $this->$method($value);
        } else {
            $this->$name = $value;
            return $this;
        }
    }

    /**
     * Returns option property value
     *
     * @param string $name The name of property
     * @return mixed
     */
    public function getOption($name)
    {
        if (method_exists($this, $method = 'get' . $name)) {
            return $this->$method();
        } else {
            return isset($this->$name) ? $this->$name : null;
        }
    }

    /**
     * Invoke the widget by the given name
     *
     * @param string $name The name of widget
     * @param array $args The arguments for widget's __invoke method
     * @return mixed
     */
    public function __call($name, $args)
    {
        return call_user_func_array($this->$name, $args);
    }

    /**
     * Get the widget object by the given name
     *
     * @param  string $name The name of widget
     * @return $this
     */
    public function __get($name)
    {
        return $this->$name = $this->widget->get($name, array(), $this->providers);
    }
}
