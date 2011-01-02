<?php
/**
 * Controller
 *
 * Copyright (c) 2008-2010 Twin Huang. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     Qwin
 * @subpackage  Application
 * @author      Twin Huang <twinh@yahoo.cn>
 * @copyright   Twin Huang
 * @license     http://www.opensource.org/licenses/apache2.0.php Apache License
 * @version     $Id$
 * @since       2009-11-24 20:45:11
 */

class Qwin_Application_Controller
{
    /**
     * 行为重置时,用于保存原来行为的名称
     * @var string
     */
    private $_lastAction;

    /**
     * 模型对象
     * @var object
     */
    protected $_model;

    /**
     * 元数据对象
     * @var object
     */
    protected $_meta;

    /**
     * 语言对象
     * @var object
     */
    protected $_lang;

    /**
     * 语言的名称
     * @var string
     */
    protected $_langName;

    protected $_validatorMessage;

    protected $_validatorField;

    /**
     *
     * @var array $_viewOption  视图配置
     *
     *      -- display          是否要加载视图
     *
     *      -- loaded           是否已经加载了视图
     *
     *      -- class            视图类名
     *
     *      -- layout           视图布局
     *
     *      -- element          视图元素
     *
     *      -- data             视图数据
     */
    protected $_viewOption = array(
        'display'   => true,
        'loaded'    => false,
        'class'     => 'Qwin_Application_View_Null',
        'layout'    => array(),
        'element'   => array(),
        'data'      => array(),
    );

    /**
     * 视图配置
     * @var array
     */
    protected $_view = array();

    public function setValidatorMessage($field = null, $message = null)
    {
        $this->_validatorField = $field;
        $this->_validatorMessage = $message;
        return $this;
    }

    /**
     * 设置新的行为
     *
     * @param string $newAction 新的行为名称
     * @return object 当前对象
     */
    public function setAction($newAction)
    {
        $this->_lastAction = $this->_set['action'];
        $this->_set['action'] = $newAction;
        return $this;
    }

    /**
     * 恢复为上一个行为
     *
     * @return string Action 的名称
     * @return object 当前对象
     */
    public function resetAction()
    {
        $this->_set['action'] = $this->_lastAction;
        return $this->_set['action'];
    }

    /**
     * 获取上一个行为名称,一般是原行为
     *
     * @return string
     */
    public function getLastAction()
    {
        return $this->_lastAction;
    }

    /**
     * 根据视图配置加载视图类
     *
     * @param array $view 视图配置
     * @return object 视图对象
     */
    public function loadView(array $view = null)
    {
        // 默认只加载一次
        if ($this->_viewOption['loaded']) {
            return false;
        }
        $this->_viewOption['loaded'] = true;

        // 合并视图配置
        $view = (array)$view;
        $this->_view = array_merge($this->_viewOption, $this->_view, $view);

        // 加载视图
        $view = Qwin::run($this->_view['class']);
        if (null == $view) {
            throw new Qwin_Application_Controller_Exception('The view class ' . $this->_view['class'] . 'is not exists');
        }

        // 设置视图
        !empty($this->_view['data']) && $view->mergeVarData($this->_view['data']);
        !empty($this->_view['element']) && $view->setElementList($this->_view['element']);
        !empty($this->_view['layout']) && $view->setLayout($this->_view['layout']);
        return $view;
    }

    /**
     * 执行 on 方法
     * 
     * @param string $method
     * @return object 当前对象
     */
    public function executeOnFunction($method)
    {
        if(method_exists($this, 'on' . $method))
        {
            $args = func_get_args();
            array_shift($args);
            call_user_func_array(array($this, 'on' . $method), $args);
        }
        return $this;
    }

    /**
     * 快速初始一个类
     * @param <type> $name
     * @return <type>
     */
    public function __get($name)
    {
        if('Qwin_' == substr($name, 0, 5))
        {
            return Qwin::run($name);
        }
        return null;
    }
}
