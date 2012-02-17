<?php

require_once dirname(__FILE__) . '/../../../libs/Qwin.php';
require_once dirname(__FILE__) . '/../../../libs/Qwin/Uuid.php';

/**
 * Test class for Qwin_Uuid.
 * Generated by PHPUnit on 2012-01-18 at 09:09:17.
 */
class Qwin_UuidTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Qwin_Uuid
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Qwin_Uuid;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers Qwin_Uuid::call
     */
    public function testCall() {
        $uuid = $this->object->call();

        $isUuid = $uuid->isRegex($uuid, '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/');

        $this->assertEquals(true, $isUuid);
    }
}
