<?php

namespace Tests\Feature;

use App\Model\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class NavigationAndOfflineBufferTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        view()->share('errors', new ViewErrorBag());

        $this->actingAs(new User([
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'kasse' => 1,
            'verwaltung' => 1,
        ]));
    }

    public function test_navigation_uses_accessible_submenu_buttons(): void
    {
        $html = view('kasse.settings.index')->render();

        $this->assertStringContainsString('id="side-menu"', $html);
        foreach (['interessenten', 'boerse', 'listen', 'settings'] as $menu) {
            $this->assertStringContainsString(
                'class="side-menu-toggle" aria-controls="menu-'.$menu.'" aria-expanded="false"',
                $html
            );
            $this->assertStringContainsString('<ul id="menu-'.$menu.'" hidden>', $html);
        }
        $this->assertStringContainsString('css/navigation.css?v=', $html);
        $this->assertStringContainsString('js/side-menu.js?v=', $html);
    }

    public function test_cashier_view_keeps_sale_feedback_without_buffer_controls(): void
    {
        $html = view('kasse.home', [
            'warenkorb' => new LengthAwarePaginator([], 0, 15),
            'summe' => 0,
        ])->render();

        $this->assertStringNotContainsString('id="offline-status"', $html);
        $this->assertStringNotContainsString('id="sync-offline-sales"', $html);
        $this->assertStringContainsString('id="offline-sale-feedback"', $html);
        $this->assertStringContainsString('role="status" hidden', $html);
        $this->assertStringContainsString('js/offline-kasse.js?v=', $html);
    }

    public function test_cashier_settings_show_buffer_controls_and_load_sync_script(): void
    {
        $html = view('kasse.settings.index')->render();

        $this->assertStringContainsString('Offline-Pufferspeicher', $html);
        $this->assertStringContainsString('id="offline-status" role="status"', $html);
        $this->assertStringContainsString('id="sync-offline-sales"', $html);
        $this->assertStringContainsString('js/offline-kasse.js?v=', $html);
    }
}
