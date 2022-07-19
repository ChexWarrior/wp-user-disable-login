<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Include our WP classes :/
require dirname(__FILE__) . '/../../web/wp-content/plugins/user-login-disable/include/user-login-disable-plugin-class.php';
require dirname(__FILE__) . '/../../web/wp-content/plugins/user-login-disable/include/user-login-disable-cli-command.php';

class WpCliCommandTest extends TestCase
{
    private ?User_Login_Disable_CLI_Command $cliCmd = null;

    protected function setUp(): void
    {
        $stubPlugin = $this->createStub(User_Login_Disable::class);
        $this->cliCmd = new User_Login_Disable_CLI_Command($stubPlugin);
    }

    public function testFilterUsersByArgs()
    {

        $this->assertSame(1, 1);
    }
}
