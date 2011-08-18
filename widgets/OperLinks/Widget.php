<?php
/**
 * Widget
 *
 * Copyright (c) 2008-2011 Twin Huang. All rights reserved.
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
 * @author      Twin Huang <twinh@yahoo.cn>
 * @copyright   Twin Huang
 * @license     http://www.opensource.org/licenses/apache2.0.php Apache License
 * @version     $Id$
 * @since       2011-03-27 02:05:30
 */

class OperLinks_Widget extends Qwin_Widget_Abstract
{
//    protected $_defaults = array(
//        'view'  => null,
//        'data'  => array(),
//        'pk'    => null,
//    );

    public function render($view = null)
    {
        $module = $view['module'];
        $action = $view['action'];
        // 模块的Url形式
        $moduleUrl = $module->getUrl();
        $url = $this->_url;
        $lang = $this->_lang;
        $controller = Controller_Widget::getByModule($moduleUrl, false);
        $varList = get_class_vars($controller);
        if (isset($varList['_unableActions'])) {
            $unableActions = (array)$varList['_unableActions'];
        } else {
            $unableActions = array();
        }
        $link = array();
        $data = isset($view['data']) ? $view['data'] : array();
        $id = isset($view['id']) ? $view['id'] : null;

        // 上一记录，下一记录
        /*if ('edit' == $action || 'view' == $action) {
            $link['next'] = array(
                'url'   => $url->url($moduleUrl, $action, array($id => $data[$id], 'forward' => 'next')),
                'title' => $lang['ACT_NEXT'],
                'icon'  => 'ui-icon-circle-triangle-e',
                'class' => 'qw-fr',
            );
            $link['prev'] = array(
                'url'   => $url->url($moduleUrl, $action, array($id => $data[$id], 'forward' => 'prev')),
                'title' => $lang['ACT_PREV'],
                'icon'  => 'ui-icon-circle-triangle-w',
                'class' => 'qw-fr',
            );
        }*/

        if (!in_array('index', $unableActions) && method_exists($controller, 'actionIndex')) {
            $link['index'] = array(
                'url'   => $url->url($moduleUrl, 'index'),
                'title' => $lang->t('ACT_INDEX'),
                'icon'  => 'ui-icon-note',
            );
        }

        if (!in_array('add', $unableActions) && method_exists($controller, 'actionAdd')) {
            $link['add'] = array(
                'url'   => $url->url($moduleUrl, 'add'),
                'title' => $lang->t('ACT_ADD'),
                'icon'  => 'ui-icon-plus',
            );
        }

        if ('add' != $action) {
            if (!in_array('edit', $unableActions) && method_exists($controller, 'actionEdit')) {
                $link['edit'] = array(
                    'url'   => $url->url($moduleUrl, 'edit', array($id => $data[$id])),
                    'title' => $lang->t('ACT_EDIT'),
                    'icon'  => 'ui-icon-tag',
                );
            }

            if (!in_array('view', $unableActions) && method_exists($controller, 'actionView')) {
                $link['view'] = array(
                    'url'   => $url->url($moduleUrl, 'view', array($id => $data[$id])),
                    'title' => $lang->t('ACT_VIEW'),
                    'icon'  => 'ui-icon-lightbulb',
                );
            }

            if (!in_array('add', $unableActions) && method_exists($controller, 'actionAdd')) {
                $link['copy'] = array(
                    'url'   => $url->url($moduleUrl, 'add', array($id => $data[$id])),
                    'title' => $lang->t('ACT_COPY'),
                    'icon'  => 'ui-icon-transferthick-e-w',
                );
            }

             if (!in_array('delete', $unableActions) && method_exists($controller, 'actionDelete')) {
                $meta = Meta_Widget::getByModule($moduleUrl);
                if (!isset($meta['page']['useTrash'])) {
                    $icon = 'ui-icon-close';
                    $jsLang = 'MSG_CONFIRM_TO_DELETE';
                } else {
                    $icon = 'ui-icon-trash';
                    $jsLang = 'MSG_CONFIRM_TO_DELETE_TO_TRASH';
                }
                 $link['delete'] = array(
                    'url'   => 'javascript:if(confirm(qwin.lang.' . $jsLang . ')){window.location=\'' . $url->url($moduleUrl, 'delete', array($id => $data[$id])) . '\'};',
                    'title' => $lang->t('ACT_DELETE'),
                    'icon'  => $icon,
                );
             }
        }
        $link['return'] = array(
            'url'   => 'javascript:history.go(-1);',
            'title' => $lang->t('ACT_RETURN'),
            'icon'  => 'ui-icon-arrowthickstop-1-w',
        );
        
        // 如果当前行为存在选项卡视图,加载该视图,否则直接输出默认选项卡内容
        $class = $module->getClass() . '_OperLinksWidget';
        if(class_exists($class)) {
            $object = new $class;
            // TODO
            $file = $view->decodePath('<resource><theme>/<defaultPackage>/element/<module>/<controller>/<action>-formlink<suffix>');
            return $object->render(array(
                'link'      => $link,
                'file'      => $file,
                'object'    => $this,
                'param'     => $param,
            ), $view);
        } else {
            return $this->renderLink($link, $view, false);
        }
    }

    /**
     * 输出表单链接
     *
     * @param object $view 视图对象
     * @param bool $echo 是否输出视图
     * @return string
     */
    public function renderLink($link, $view, $echo = true)
    {
        $output = '';
        foreach ($link as $row) {
            !isset($row['class']) && $row['class'] = null;
            $output .= '<a class="qw-anchor" class="' . $row['class'] . '" href="' . $row['url'] . '" data="{icons:{primary:\'' . $row['icon'] . '\'}}">' . $row['title'] . '</a>&nbsp;';
        }
        if ($echo) {
            require $view->decodePath('<resource><theme>/<defaultPackage>/element/basic/output<suffix>');
        } else {
            return $output;
        }
    }
}
