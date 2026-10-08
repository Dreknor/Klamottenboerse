<?php

namespace Tests\Feature;

use App\Model\User;
use App\Model\Interessenten;
use App\Model\VKnummer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class NavigationAndOfflineBufferTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_layout_loads_only_one_jquery_and_bootstrap_implementation_before_plugins(): void
    {
        $html = view('kasse.settings.index')->render();

        $this->assertSame(1, substr_count($html, '/js/app.js?v='));
        $this->assertStringNotContainsString('/js/lib/jquery/', $html);
        $this->assertStringNotContainsString('/js/lib/bootstrap/bootstrap.min.js', $html);
        $this->assertStringNotContainsString('/js/lib/popper/', $html);
        $this->assertLessThan(strpos($html, '/js/plugins.js'), strpos($html, '/js/app.js?v='));
    }

    public function test_each_seller_number_has_a_unique_dropdown_with_existing_actions(): void
    {
        $seller = new Interessenten(['vorname' => 'Maria', 'nachname' => 'Muster']);
        $seller->id = 42;
        $numbers = collect([201, 301, 401, 501, 601])->map(function ($number) use ($seller) {
            $vknummer = new VKnummer(['vknummer' => $number, 'reserviert_fuer' => $seller->id]);
            $vknummer->id = $number;
            $vknummer->setRelation('vergeben_an_Interessent', null);
            $vknummer->setRelation('reserviert_fuer_Interessent', $seller);
            $vknummer->setRelation('bisherigeVerkaeufer', collect());

            return $vknummer;
        });
        $html = view('vknummern.index', ['vknummern' => $numbers])->render();

        foreach ($numbers as $number) {
            $this->assertSame(1, substr_count($html, 'id="vknummer-toggle-'.$number->id.'"'));
            $this->assertSame(1, substr_count($html, 'id="vknummer-menu-'.$number->id.'"'));
            $this->assertStringContainsString('aria-controls="vknummer-menu-'.$number->id.'"', $html);
            $this->assertStringContainsString('aria-labelledby="vknummer-toggle-'.$number->id.'"', $html);
            $this->assertStringContainsString('/vknummern/'.$number->id.'/freiVergeben', $html);
            $this->assertStringContainsString('data-id="'.$number->id.'"', $html);
        }
        $this->assertSame(6, substr_count($html, 'data-toggle="dropdown"'));
        $this->assertStringNotContainsString('btnGroupDrop1', $html);
    }
}
