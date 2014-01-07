<?php
namespace ZFToolTest\Diagnostics\Test;

use ZFTool\Diagnostics\Test\ProcessRunning;
use ZFToolTest\Diagnostics\TestAsset\AlwaysSuccessTest;

class ProcessRunningTest extends \PHPUnit_Framework_TestCase
{
    public function testLabels()
    {
        $label = md5(rand());
        $test = new AlwaysSuccessTest();
        $test->setLabel($label);
        $this->assertEquals($label, $test->getLabel());
    }
    
    /**
     * @covers ProcessRunning::run
     */
    public function testProcessRunning()
    {
        /**
         * @todo check existing service
         */

        $test = new ProcessRunning(PHP_INT_MAX); // improbable to achieve
        $result = $test->run();
        $this->assertInstanceOf('ZFTool\Diagnostics\Result\Failure', $result);

        $test = new ProcessRunning('dummyService'); // dummy service
        $result = $test->run();
        $this->assertInstanceOf('ZFTool\Diagnostics\Result\Failure', $result);
    }
    
    /**
     * @covers ProcessRunning::run
     */
    public function testProcessRunningInvalidArgument1()
    {
        $this->setExpectedException('ZFTool\Diagnostics\Exception\InvalidArgumentException');
        new ProcessRunning(-1);
    }
    
    /**
     * @covers ProcessRunning::run
     */
    public function testProcessRunningInvalidArgument2()
    {
        $this->setExpectedException('ZFTool\Diagnostics\Exception\InvalidArgumentException');
        new ProcessRunning(0);
    }
    
    /**
     * @covers ProcessRunning::run
     */
    public function testProcessRunningInvalidArgument3()
    {
        $this->setExpectedException('ZFTool\Diagnostics\Exception\InvalidArgumentException');
        new ProcessRunning(array('dummy'));
    }
}
