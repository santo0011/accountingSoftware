<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** One login form: each account lands in its own area without choosing a role. */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Password@123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public static function accounts(): array
    {
        return [
            'super admin' => ['admin@bizsetu.test', 'admin.dashboard'],
            'operations admin' => ['admin.ops@bizsetu.test', 'admin.dashboard'],
            'staff' => ['rahul.staff@bizsetu.test', 'admin.dashboard'],
            'customer' => ['customer@bizsetu.test', 'portal.dashboard'],
        ];
    }

    #[DataProvider('accounts')]
    public function test_each_role_is_sent_to_its_own_dashboard(string $email, string $home): void
    {
        $this->post(route('login'), ['login' => $email, 'password' => self::PASSWORD])
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertRedirect(route($home));
        $this->get(route($home))->assertOk();
    }

    public function test_professional_accounts_use_the_admin_panel(): void
    {
        $professional = User::where('user_type', User::TYPE_PROFESSIONAL)->where('status', 'active')->first();
        $this->assertNotNull($professional, 'Seeder should create at least one professional login.');

        $this->post(route('login'), ['login' => $professional->email, 'password' => self::PASSWORD]);
        $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_customer_cannot_be_sent_into_the_admin_panel_by_an_intended_url(): void
    {
        // A guest opens an admin page, gets bounced to login, then signs in as a customer.
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->post(route('login'), ['login' => 'customer@bizsetu.test', 'password' => self::PASSWORD])
            ->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertRedirect(route('portal.dashboard'));
    }

    public function test_wrong_password_shows_a_clear_error(): void
    {
        $this->from(route('login'))
            ->post(route('login'), ['login' => 'admin@bizsetu.test', 'password' => 'wrong-password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->get(route('login'))->assertOk()->assertSee('auth-alert error', false);
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        User::where('email', 'rahul.staff@bizsetu.test')->update(['status' => 'inactive']);

        $this->post(route('login'), ['login' => 'rahul.staff@bizsetu.test', 'password' => self::PASSWORD])
            ->assertSessionHasErrors(['login' => 'Your account is inactive. Please contact support.']);
        $this->assertGuest();
    }

    public function test_login_page_has_a_single_form_without_a_role_picker(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('name="login"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertDontSee('data-role=', false)
            ->assertDontSee('Login as');
    }
}
