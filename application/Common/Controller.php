<?php
/**
 * Controller
 *
 * AciionController is controller with some default action,such as index,list,
 * add,edit,delete,view and so on.
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
 * @package     Common
 * @subpackage  Controller
 * @author      Twin Huang <twinh@yahoo.cn>
 * @copyright   Twin Huang
 * @license     http://www.opensource.org/licenses/apache2.0.php Apache License
 * @version     $Id$
 * @since       2010-7-28 15:19:18
 */

class Common_Controller extends Qwin_Application_Controller
{
    protected $_asc;

    /**
     * Qwin_Request对象
     * @var Qwin_Request
     */
    public $request;

    /**
     * Qwin_Url对象
     * @var object
     */
    public $url;

    /**
     * 全局配置数组
     * @var array
     */
    protected $config;

    /**
     * 会话对象
     * @var object
     */
    protected $session;

    /**
     * 用户数据数组
     * @var array
     */
    public $member;

    /**
     * Qwin_Application_Language语言子对象
     * @var object
     */
    protected $_lang;

    /**
     * Qwin_Application_Metadata元数据子对象
     * @var object
     */
    protected $_meta;


    /**
     * Qwin_Application_Model模型子对象
     * @var object
     */
    protected $_model;

    /**
     * 元数据助手,负责元数据的获取,转换,检查等
     * @var Qwin_Application_Metadata
     */
    public $metaHelper;

    /**
     * 视图对象
     * @var Qwin_Application_View
     */
    public $view;

    /**
     * 初始化各类和数据
     */
    public function __construct($option = null)
    {
        if (false === $option) {
            return true;
        }
        $this->request  = Qwin::run('-request');
        $this->url      = Qwin::run('-url');
        $this->config   = Qwin::run('-config');
        $this->_asc     = $this->config['asc'];
        $this->session  = Qwin::run('-session');
        $this->member   = $this->session->get('member');
        $this->view     = Qwin::run('-view');

        // 元数据管理助手,负责元数据的获取和转换
        $this->metaHelper = Qwin::run('Qwin_Application_Metadata');

        $languageResult = Qwin::run('Common_Service_Language')->getLanguage($this->config['asc']);
        $languageName = $languageResult['data'];
        $languageClass = $this->config['asc']['namespace'] . '_' . $this->config['asc']['module'] . '_Language_' . $languageName;
        $this->_lang = Qwin::run($languageClass);
        if(null == $this->_lang)
        {
            $languageClass = 'Common_Language_' . $languageName;
            $this->_lang = Qwin::run($languageClass);
        }
        Qwin::addMap('-lang', $languageClass);

        $this->_meta = $this->metaHelper->getMetadataByAsc($this->config['asc']);

        $this->_viewOption['class'] = 'Common_View';

         /**
         * 访问控制
         */
        $this->_isAllowVisited();
    }

    /**
     * 是否有权限浏览该页面
     *
     * @return boolen
     */
    protected function _isAllowVisited()
    {
        $session = $this->session;
        $member = $session->get('member');
        $metaHelper = Qwin::run('Qwin_Application_Metadata');

        // 未登陆则默认使用游客账号
        if(null == $member)
        {
            $asc = array(
                'namespace' => 'Common',
                'module' => 'Member',
                'controller' => 'Member',
            );
            $result = $metaHelper
                ->getQueryByAsc($asc, array('db', 'view'))
                ->where('username = ?', 'guest')
                ->fetchOne();
            $member = $result->toArray();
            
            $session
                ->set('member',  $member)
                ->set('permisson', $member['group']['permission'])
                ->set('style', $member['theme'])
                ->set('language', $member['language']);
        }

        // 逐层权限判断
        $asc = $this->config['asc'];
        $permission = @unserialize($member['group']['permission']);
        if(isset($permission[$asc['namespace']]))
        {
            return true;
        }
        if(isset($permission[$asc['namespace'] . '|' . $asc['module']]))
        {
            return true;
        }
        if(isset($permission[$asc['namespace'] . '|' . $asc['module'] . '|' . $asc['controller']]))
        {
            return true;
        }
        if(isset($permission[$asc['namespace'] . '|' . $asc['module'] . '|' . $asc['controller'] . '|' . $asc['action']]))
        {
            return true;
        }

        if('guest' == $member['username'])
        {
            $url = Qwin::run('-url');
            $url->to($url->createUrl(array(
                    'module' => 'Member',
                    'controller' => 'Log',
                    'action' => 'Login',
                )));
        } else {
            $this
                ->view->setRedirectView($this->_lang->t('MSG_PERMISSION_NOT_ENOUGH'))
                ->loadView()
                ->display();
            exit;
        }
        return false;
    }

    /**
     * 显示验证错误的信息,当验证结果不为true时调用该方法
     *
     * @param Qwin_Validator_Result $result 验证结果
     * @param Qwin_Metadata $meta 元数据
     * @param boolen $dispaly 是否显示错误视图
     * @return string 错误信息
     */
    public function showValidateError(Qwin_Validator_Result $result, Qwin_Metadata $meta, $dispaly = true)
    {
        if(!is_array($result->field))
        {
            $fieldTitle = $this->_lang->t($meta['field'][$result->field]['basic']['title']);
        } else {
            $fieldTitle = $this->_lang->t($meta['metadata'][$result->field[0]]['field'][$result->field[1]]['basic']['title']);
        }
        $message = $this->_lang->t('MSG_ERROR_FIELD')
                . $fieldTitle
                . '<br />'
                . $this->_lang->t('MSG_ERROR_MSG')
                . $this->metaHelper->format($this->_lang->t($result->message), $result->param);
        if($dispaly)
        {
            $this->view->setRedirectView($message);
        }
        return $message;
    }
}
