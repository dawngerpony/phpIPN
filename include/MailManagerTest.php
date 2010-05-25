<?php
require_once 'PHPUnit/Framework.php';
require_once 'includes_phpunit.php';
 
class MailManagerTest extends PHPUnit_Framework_TestCase
{
    private $className = 'MailManager';

    public function setUp()
    {
        $this->confirmationMailParams = array('to'           => 'dafydd@dafyddjames.com',
                                              'first_name'   => 'Test',
                                              'last_name'    => 'User',
                                              'pa_ticket_id' => 'TEST-ID',
                                              'quantity'     => '3');

        $this->replaceTokensParams = array('first_name'   => 'Test',
                                           'last_name'    => 'User',
                                           'quantity'     => '3',
                                           'pa_ticket_id' => 'TEST-ID');
                                           
        $this->mail = SingletonFactory::getInstance()->getSingleton($this->className);
    }

    /**
     * Retrieves an instance of the MailManager from the SingletonFactory.
     */
    public function testMailManagerSingleton()
    {
        $m = SingletonFactory::getInstance()->getSingleton($this->className);
        $this->assertType($this->className, $m);
    }
    
    /**
     * Send a confirmation mail with invalid parameters.
     */
    public function testSendConfirmationMailWithInvalidParams()
    {
        unset($this->confirmationMailParams['first_name']);

        try
        {
            $this->mail->sendConfirmationMail($this->confirmationMailParams);
            // preceding line should throw an exception
            $this->fail("No exception thrown with invalid parameters");
        }
        catch (Exception $e)
        {
            // expected behaviour
        }
    }
    
    /**
     * Send a confirmation mail with valid parameters.
     */
    public function testSendConfirmationMailWithValidParams()
    {
        try
        {
            $this->mail->sendConfirmationMail($this->confirmationMailParams);
        }
        catch (Exception $e)
        {
            $this->fail("Exception was thrown with valid parameters. Message: " . $e->getMessage());
        }
    }
    
    /**
     * Test the replaceTokens() function.
     */
    public function testReplaceTokens()
    {
        $params = array('first_name'   => 'Test',
                        'last_name'    => 'User',
                        'quantity'     => '3',
                        'pa_ticket_id' => 'TEST-ID');

        $mailBody = "You ordered %quantity% tickets, %first_name% %last_name%, your ticket id is %pa_ticket_id%";
        $expectedMailBody = "You ordered {$params['quantity']} tickets, {$params['first_name']} {$params['last_name']}, your ticket id is {$params['pa_ticket_id']}";

        //print_r($params);
        
        try
        {
            $actualMailBody = $this->mail->replaceTokens($mailBody, $params);
            $this->assertEquals($actualMailBody, $expectedMailBody, "Mail bodies not equal! Expected = [$expectedMailBody], actual = [$actualMailBody]");
        }
        catch (Exception $e)
        {
            $this->fail("Exception was thrown with valid parameters: " . $e->getMessage());
        }
    }
}

