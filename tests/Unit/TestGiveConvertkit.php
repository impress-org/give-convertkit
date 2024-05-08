<?php

namespace GiveConvertKit\Tests;

use Give\Tests\TestCase;
use Give_ConvertKit;

/**
 * @since 2.0.0
 */
class TestGiveConvertkit extends TestCase
{
    /**
     * @since 2.0.0
     */
    public function testReadMeVersionMatchesPluginVersion(): void
    {
        $readme = get_file_data(
            trailingslashit(GIVE_CONVERTKIT_DIR) . "readme.txt",
            [
                "Version" => "Stable tag"
            ]
        );

        $plugin = get_plugin_data(GIVE_CONVERTKIT_FILE);

        $this->assertEquals(GIVE_CONVERTKIT_VERSION, $readme['Version']);
        $this->assertEquals(GIVE_CONVERTKIT_VERSION, $plugin['Version']);
        $this->assertEquals($readme['Version'], $plugin['Version']);
    }

    /**
     * @since 2.0.0
     */
    public function testHasMinimumGiveWPVersion(): void
    {
        $this->assertSame('3.11.0', GIVE_CONVERTKIT_MIN_GIVE_VERSION);
    }

    /**
     * @since 2.0.0
     */
    public function testIsCompatibleWithGiveWP(): void
    {
        $this->assertFalse(version_compare(GIVE_VERSION, GIVE_CONVERTKIT_MIN_GIVE_VERSION, '<'));
    }

    /**
     * @since 2.0.0
     */
    public function testCheckRequirementsShouldReturnTrue(): void
    {
        $this->assertTrue(give(Give_ConvertKit::class)->get_environment_warning());
    }

    /**
     * @since 2.0.0
     */
    public function testReadMeRequiresPHPVersionMatchesPluginVersion(): void
    {
        $readme = get_file_data(
            trailingslashit(GIVE_CONVERTKIT_DIR) . "readme.txt",
            [
                "RequiresPHP" => "Requires PHP"
            ]
        );

        $plugin = get_plugin_data(GIVE_CONVERTKIT_FILE);

        $this->assertEquals($plugin['RequiresPHP'], $readme['RequiresPHP']);
    }

    /**
     * @since 2.0.0
     */
    public function testReadMeRequiresWPVersionMatchesPluginHeaderVersion(): void
    {
        $readme = get_file_data(
            trailingslashit(GIVE_CONVERTKIT_DIR) . "readme.txt",
            [
                "RequiresWP" => "Requires at least"
            ]
        );

        $plugin = get_plugin_data(GIVE_CONVERTKIT_FILE);

        $this->assertEquals($plugin['RequiresWP'], $readme['RequiresWP']);
    }
}