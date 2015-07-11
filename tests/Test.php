<?php

/**
 * Created by PhpStorm.
 * User: bomboze
 * Date: 11/07/15
 * Time: 11:03
 */
class Test extends PHPUnit_Framework_TestCase
{
    # TODO: better tests
    public function testBasic()
    {
        $r = new ProcRunner\LocalRunner('redis-cli');

        $r->send("GET test\n");
        $this->assertEquals("1\n", $r->readLine());
        $r->send("SET test 1\nGet test\n");
        $this->assertEquals("OK\n", $r->readLine());
        $this->assertEquals("1\n", $r->readLine());

        $r->send("fds\n");
        $this->assertEquals('', $r->readStderrLineIfAny());
        $code = $r->close();

        $this->assertEquals(0, $code);
        $this->assertEquals(0, $r->getReturnValue());
    }

    public function testBasic2()
    {
        $result = (new \ProcRunner\LocalRunner("ls"))->readAll();
        $this->assertNotEquals('', $result);
    }
}
