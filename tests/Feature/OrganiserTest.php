<?php

namespace Tests\Feature;

use App\Mail\NewOrganiserApplication;
use App\Mail\OrganiserApplicationDecided;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrganiserTest extends TestCase
{
    private function application(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Westlands Comedy',
            'business_type' => 'individual',
            'id_number'     => '12345678',
            'kra_pin'       => 'a012345678z',
            'phone'         => '0712345678',
            'email'         => 'shows@example.com',
            'location'      => 'Nairobi',
            'description'   => 'Monthly comedy nights in Westlands with around 300 guests each.',
            'payout_method' => 'mpesa',
            'payout_phone'  => '0712345678',
            'terms'         => '1',
        ], $overrides);
    }

    public function test_guest_sees_the_pitch_and_must_sign_up_to_apply(): void
    {
        $this->get(route('organisers.apply'))->assertOk()->assertSee('Create account');
        $this->post(route('organisers.store'), $this->application())->assertRedirect(route('login'));
        $this->assertSame(0, Merchant::count());
    }

    public function test_unverified_account_cannot_apply(): void
    {
        $user = $this->user('user', ['email_verified_at' => null]);

        $this->actingAs($user)->post(route('organisers.store'), $this->application())->assertRedirect(route('verification.notice'));
        $this->assertSame(0, Merchant::count());
    }

    public function test_client_applies_and_admins_are_told(): void
    {
        Mail::fake();
        $admin = $this->user('admin');
        $user  = $this->user();

        $this->actingAs($user)->post(route('organisers.store'), $this->application())
            ->assertRedirect(route('organisers.apply'))->assertSessionHasNoErrors();

        $application = Merchant::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $application->status);
        $this->assertSame('A012345678Z', $application->kra_pin);
        $this->assertFalse($user->fresh()->hasRole('merchant'));   // nothing unlocked until approved
        Mail::assertQueued(NewOrganiserApplication::class, fn ($m) => $m->hasTo($admin->email));

        $this->actingAs($user->fresh())->get(route('organisers.apply'))->assertSee('Under review');
    }

    public function test_bank_payouts_need_bank_details_and_kra_pin_is_checked(): void
    {
        $this->actingAs($this->user())->post(route('organisers.store'), $this->application([
            'payout_method' => 'bank', 'payout_phone' => null, 'kra_pin' => '123',
        ]))->assertSessionHasErrors(['kra_pin', 'bank_name', 'bank_account_name', 'bank_account_number']);
    }

    public function test_admin_approves_and_the_organiser_can_create_events(): void
    {
        Mail::fake();
        $user = $this->user();
        $this->actingAs($user)->post(route('organisers.store'), $this->application());
        $application = Merchant::where('user_id', $user->id)->first();

        $this->actingAs($user)->get(route('events.create'))->assertForbidden();

        $this->actingAs($this->user('admin'))->post(route('admin.organisers.approve', $application))->assertSessionHas('success');

        $this->assertSame('approved', $application->fresh()->status);
        $this->assertTrue($user->fresh()->hasRole('merchant'));
        Mail::assertQueued(OrganiserApplicationDecided::class, fn ($m) => $m->hasTo($user->email));
        $this->actingAs($user->fresh())->get(route('events.create'))->assertOk();
    }

    public function test_rejected_applicant_sees_the_reason_and_can_apply_again(): void
    {
        Mail::fake();
        $user = $this->user();
        $this->actingAs($user)->post(route('organisers.store'), $this->application());
        $application = Merchant::where('user_id', $user->id)->first();

        $this->actingAs($this->user('admin'))
            ->post(route('admin.organisers.reject', $application), ['note' => 'KRA PIN does not match the name'])
            ->assertSessionHas('success');

        $this->actingAs($user->fresh())->get(route('organisers.apply'))->assertSee('KRA PIN does not match the name');

        $this->actingAs($user->fresh())->post(route('organisers.store'), $this->application(['kra_pin' => 'A098765432B']));
        $this->assertSame('pending', $application->fresh()->status);
        $this->assertSame(1, Merchant::where('user_id', $user->id)->count());
    }

    public function test_suspending_removes_dashboard_access(): void
    {
        Mail::fake();
        $organiser = $this->user('merchant');
        $merchant  = Merchant::where('user_id', $organiser->id)->first();

        $this->actingAs($this->user('admin'))
            ->post(route('admin.organisers.suspend', $merchant), ['note' => 'Complaints from buyers'])
            ->assertSessionHas('success');

        $this->assertSame('suspended', $merchant->fresh()->status);
        $this->assertFalse($organiser->fresh()->hasRole('merchant'));
        $this->actingAs($organiser->fresh())->get(route('events.create'))->assertForbidden();
        // A suspended organiser can't reapply their way back in
        $this->actingAs($organiser->fresh())->post(route('organisers.store'), $this->application())->assertSessionHas('error');
        $this->assertSame('suspended', $merchant->fresh()->status);
    }

    public function test_only_admins_review_applications(): void
    {
        $merchant = Merchant::create(['user_id' => $this->user()->id, 'name' => 'Pending Co']);
        $merchant->forceFill(['status' => 'pending'])->save();

        $this->actingAs($this->user('merchant'))->get(route('admin.organisers.index'))->assertForbidden();
        $this->actingAs($this->user('merchant'))->post(route('admin.organisers.approve', $merchant))->assertForbidden();
        $this->actingAs($this->user('admin'))->get(route('admin.organisers.index'))->assertOk()->assertSee('Pending Co');
    }
}
