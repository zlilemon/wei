<?php

namespace WeiTest;

class ViewTest extends TestCase
{
    /**
     * @var \Wei\View
     */
    protected $object;

    public function setUp()
    {
        parent::setUp();

        $this->object->setDirs(__DIR__ . '/Fixtures');
    }

    public function testInvoker()
    {
        $view = $this->object;

        // Render by invoker
        $content = $view('layout.php', array(
            'content' => __METHOD__
        ));
        $this->assertContains(__METHOD__, $content);
    }

    public function testAssign()
    {
        $view = $this->object;
        $value = __METHOD__;

        $view->assign('content', $value);
        $content = $view->render('layout.php');

        $this->assertContains($value, $content);
    }

    public function testAssinArray()
    {
        $view = $this->object;

        $view->assign(array(
            'key' => 'value',
            'key2' => 'value2',
        ));

        $this->assertEquals('value', $view->get('key'));
        $this->assertEquals('value2', $view->get('key2'));
        $this->assertNull($view->get('not-defined-key'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFileNotFoundException()
    {
        $this->object->render('not-found-file');
    }

    public function testLayout()
    {
        $content = $this->object->render('view.php');

        $this->assertContains('layout start', $content);
        $this->assertContains('layout end', $content);
    }

    public function testDisplay()
    {
        ob_start();
        $this->object->display('layout.php', array('content' => 'test'));
        $content = ob_get_clean();

        $expected = $this->object->render('layout.php', array('content' => 'test'));

        $this->assertEquals($expected, $content);
    }

    public function testGetExtension()
    {
        $this->assertEquals('.php', $this->object->getExtension());
    }

    public function testGetVarWei()
    {
        $this->assertEquals($this->wei, $this->object->get('wei'));
        $this->assertEquals($this->wei->view, $this->object->get('view'));
    }

    public function testArrayAccess()
    {
        $view = $this->view;

        $view['name'] = 'value';
        $this->assertEquals('value', $view['name']);

        $view['name'] = 'value2';
        $this->assertEquals('value2', $view['name']);

        unset($view['name']);
        $this->assertNull($view['name']);

        $this->assertFalse(isset($view['name']));
    }

    public function testReferenceArrayAccess()
    {
        $view = $this->view;

        $view['items'] = array();
        $view['items'][] = 'item 1';
        $view['items'][] = 'item 2';

        $this->assertEquals('item 1', $view['items'][0]);
        $this->assertEquals('item 2', $view['items'][1]);
    }
}
