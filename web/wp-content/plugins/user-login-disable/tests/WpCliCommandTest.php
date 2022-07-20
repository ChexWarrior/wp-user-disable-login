<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

// Include our WP classes :/
require dirname(__FILE__) . '/../../web/wp-content/plugins/user-login-disable/include/UserLoginDisablePlugin.php';
require dirname(__FILE__) . '/../../web/wp-content/plugins/user-login-disable/include/UserLoginDisableCmds.php';

class WpCliCommandTest extends TestCase
{
    private ?UserLoginDisableCmds $cliCmd = null;

    protected function setUp(): void
    {
        $stubPlugin = $this->createStub(UserLoginDisablePlugin::class);
        $this->cliCmd = new UserLoginDisableCmds($stubPlugin);
    }

    public function testFilterUsersByArgs()
    {
        
        $this->assertSame(1, 1);
    }
}
