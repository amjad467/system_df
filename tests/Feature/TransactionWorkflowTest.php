<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_barcode_generation_format_and_uniqueness(): void
    {
        $barcode1 = BarcodeService::generateNextBarcode();
        $barcode2 = BarcodeService::generateNextBarcode();

        $this->assertMatchesRegularExpression('/^\d{10}$/', $barcode1);
        $this->assertNotEquals($barcode1, $barcode2);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('user_login', 'admin')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_auditor_can_return_transaction_with_reason(): void
    {
        $auditor = User::where('user_login', 'auditor')->first();
        $transaction = Transaction::first();

        $response = $this->actingAs($auditor)->post(route('transactions.return', $transaction->id), [
            'return_reason' => 'کێشە لە زانیاری ئۆتۆمبێل هەیە'
        ]);

        $response->assertRedirect();
        $transaction->refresh();
        $this->assertTrue((bool)$transaction->is_returned);
        $this->assertEquals('کێشە لە زانیاری ئۆتۆمبێل هەیە', $transaction->return_reason);
    }

    public function test_auditor_cannot_audit_uninspected_transaction(): void
    {
        $auditor = User::where('user_login', 'auditor')->first();
        $transaction = Transaction::first();
        $transaction->update(['is_inspected' => false, 'is_audited' => false]);

        $response = $this->actingAs($auditor)->post(route('transactions.audit', $transaction->id));
        $response->assertSessionHas('error');
        $transaction->refresh();
        $this->assertFalse((bool)$transaction->is_audited);
    }

    public function test_admin_can_revert_payment_step_and_clear_receipt(): void
    {
        $admin = User::where('user_login', 'admin')->first();
        $transaction = Transaction::first();
        $transaction->update([
            'is_paid' => true,
            'receipt_37a_number' => 'REC-12345'
        ]);

        $response = $this->actingAs($admin)->post(route('transactions.revert_step', $transaction->id), [
            'step' => 'payment',
            'reversion_reason' => 'دیاری هەڵە لە پڕکردنەوەی ژمارەی پسوولە'
        ]);

        $response->assertRedirect();
        $transaction->refresh();
        $this->assertFalse((bool)$transaction->is_paid);
        $this->assertNull($transaction->receipt_37a_number);
    }

    public function test_auto_creates_pledge_for_booklet_issue(): void
    {
        $dataEntry = User::where('user_login', 'dataentry')->first();

        $response = $this->actingAs($dataEntry)->post(route('transactions.store'), [
            'transaction_type_id' => 1, // دەرهێنانی دەفتەر (نوێ)
            'visitor_name' => 'شێرزاد کەریم عەلی',
            'plate_number' => '33 A 12345',
            'model_year' => '2024',
            'chassis_number' => 'WBAP09182371892',
            'num_years' => 1,
        ]);

        $response->assertRedirect();
        
        $mainTx = Transaction::where('visitor_name', 'شێرزاد کەریم عەلی')->where('transaction_type_id', 1)->first();
        $this->assertNotNull($mainTx);

        $pledgeTx = Transaction::where('parent_transaction_id', $mainTx->id)->whereIn('transaction_type_id', [4, 6])->first();
        $this->assertNotNull($pledgeTx);
        $this->assertEquals('شێرزاد کەریم عەلی', $pledgeTx->visitor_name);
        $this->assertNotEquals($mainTx->barcode, $pledgeTx->barcode);
    }

    public function test_requires_booklet_logic_for_transaction_types(): void
    {
        $bookletTypes = [1, 2, 3, 4, 7];
        $nonBookletTypes = [5, 6, 8];

        foreach ($bookletTypes as $typeId) {
            $tx = new Transaction(['transaction_type_id' => $typeId]);
            $this->assertTrue($tx->requiresBooklet(), "Type {$typeId} should require booklet printing.");
        }

        foreach ($nonBookletTypes as $typeId) {
            $tx = new Transaction(['transaction_type_id' => $typeId]);
            $this->assertFalse($tx->requiresBooklet(), "Type {$typeId} should NOT require booklet printing.");
        }
    }

    public function test_role_restriction_prevents_unauthorized_stage_passing(): void
    {
        $cashier = User::where('user_login', 'cashier')->first();
        $inspector = User::where('user_login', 'inspector')->first();
        $transaction = Transaction::first();
        $transaction->update(['is_inspected' => true, 'is_audited' => false, 'is_paid' => false]);

        // Cashier attempting to audit should be blocked
        $response = $this->actingAs($cashier)->post(route('transactions.audit', $transaction->id));
        $response->assertSessionHas('error');
        $transaction->refresh();
        $this->assertFalse((bool)$transaction->is_audited);

        // Inspector attempting to pay should be blocked
        $response = $this->actingAs($inspector)->post(route('transactions.pay', $transaction->id), [
            'receipt_37a_number' => 'REC-999'
        ]);
        $response->assertSessionHas('error');
        $transaction->refresh();
        $this->assertFalse((bool)$transaction->is_paid);
    }

    public function test_category_b_transaction_allows_submit_without_booklet_print(): void
    {
        $cashier = User::where('user_login', 'cashier')->first();
        $transaction = Transaction::first();
        // Set to Type 6 (Pledge - Category B, no booklet needed)
        $transaction->update([
            'transaction_type_id' => 6,
            'is_inspected' => true,
            'is_audited' => true,
            'is_paid' => true,
            'receipt_37a_number' => 'REC-7788',
            'is_booklet_completed' => false,
            'is_submitted' => false,
        ]);

        $this->assertFalse($transaction->requiresBooklet());

        $response = $this->actingAs($cashier)->post(route('transactions.submit', $transaction->id));
        $response->assertRedirect();
        $transaction->refresh();
        $this->assertTrue((bool)$transaction->is_submitted);
    }

    public function test_estimator_receipt_print_rules(): void
    {
        $inspector = User::where('user_login', 'inspector')->first();
        $auditor = User::where('user_login', 'auditor')->first();
        $transaction = Transaction::first();

        // 1. Uninspected: Inspector cannot print yet
        $transaction->update(['is_inspected' => false, 'is_audited' => false]);
        $response = $this->actingAs($inspector)->get(route('transactions.print_receipt', $transaction->id));
        $response->assertSessionHas('error');

        // 2. Inspected but NOT audited: Inspector CAN print
        $transaction->update(['is_inspected' => true, 'is_audited' => false]);
        $response = $this->actingAs($inspector)->get(route('transactions.print_receipt', $transaction->id));
        $response->assertStatus(200);

        // 3. Audited: Inspector CANNOT print anymore
        $transaction->update(['is_inspected' => true, 'is_audited' => true]);
        $response = $this->actingAs($inspector)->get(route('transactions.print_receipt', $transaction->id));
        $response->assertSessionHas('error');

        // 4. Non-estimator (auditor) cannot print estimator receipt
        $response = $this->actingAs($auditor)->get(route('transactions.print_receipt', $transaction->id));
        $response->assertSessionHas('error');
    }

    public function test_data_entry_routing_slip_can_be_printed(): void
    {
        $dataEntry = User::where('user_login', 'dataentry')->first();
        $transaction = Transaction::first();

        $response = $this->actingAs($dataEntry)->get(route('transactions.print_data_entry', $transaction->id));
        $response->assertStatus(200);
        $response->assertSee($transaction->barcode);
    }

    public function test_blocked_receipt_37a_number_prevents_reuse(): void
    {
        $cashier = User::where('user_login', 'cashier')->first();
        $admin = User::where('user_login', 'admin')->first();
        $transaction = Transaction::first();

        $transaction->update(['is_audited' => true, 'is_paid' => true, 'receipt_37a_number' => 'BLOCKED-REC-999']);

        $this->actingAs($admin)->post(route('transactions.cancel', $transaction->id), [
            'cancellation_reason' => 'پووچەڵکردنەوەی فەرمی'
        ]);

        $this->assertDatabaseHas('blocked_receipt_numbers', [
            'receipt_number' => 'BLOCKED-REC-999'
        ]);

        $tx2 = Transaction::where('id', '!=', $transaction->id)->first();
        $tx2->update(['is_audited' => true, 'is_paid' => false]);

        $response = $this->actingAs($cashier)->post(route('transactions.pay', $tx2->id), [
            'receipt_37a_number' => 'BLOCKED-REC-999'
        ]);

        $response->assertSessionHas('error');
    }

    public function test_fee_calculation_for_name_and_booklet_change_keeps_fixed_fee_when_years_change(): void
    {
        // Type 3 (Name Change), Type 4 (Booklet Replacement), Type 7 (Name & Booklet Change)
        foreach ([3, 4, 7] as $typeId) {
            $fee1Yr = \App\Services\FeeCalculatorService::calculate($typeId, 1);
            $fee3Yr = \App\Services\FeeCalculatorService::calculate($typeId, 3);

            $this->assertEquals(0, $fee1Yr['pay_amount_years']);
            $this->assertEquals(0, $fee3Yr['pay_amount_years']);
            $this->assertEquals($fee1Yr['total_pay'], $fee3Yr['total_pay'], "Total pay for type {$typeId} must not change when years change.");

            $startDate = \Carbon\Carbon::parse($fee1Yr['start_date']);
            $this->assertEquals($startDate->copy()->addYears(1)->toDateString(), $fee1Yr['end_date']);
            $this->assertEquals($startDate->copy()->addYears(3)->toDateString(), $fee3Yr['end_date']);
        }
    }

    public function test_cancellation_and_inspection_types_clear_unneeded_fields(): void
    {
        $dataEntry = User::where('user_login', 'dataentry')->first();

        // Test Type 5 (Cancellation)
        $response = $this->actingAs($dataEntry)->post(route('transactions.store'), [
            'transaction_type_id' => 5,
            'visitor_name' => 'کاروان فایەق سەعید',
            'visitor_name_eng' => 'KARWAN FAYEQ',
            'second_driver_name' => 'سامان فایەق',
            'plate_number' => '44 B 99881',
            'model_year' => '2022',
            'chassis_number' => 'KMH8821931029102',
            'num_years' => 2,
            'start_date' => '2026-10-06',
            'end_date' => '2028-10-06',
            'zedabar' => 'باشە',
            'kamukurty' => 'باشە',
            'bary_gshty' => 'باشە',
            'no_nusraw_puchal' => 'CUST-887766',
            'date_nusraw_puchal' => '2026-10-01',
        ]);

        $response->assertRedirect();
        $tx = Transaction::where('visitor_name', 'کاروان فایەق سەعید')->first();
        $this->assertNotNull($tx);
        $this->assertNull($tx->start_date);
        $this->assertNull($tx->end_date);
        $this->assertNull($tx->zedabar);
        $this->assertNull($tx->kamukurty);
        $this->assertNull($tx->bary_gshty);
        $this->assertNull($tx->second_driver_name);
        $this->assertNull($tx->visitor_name_eng);
        $this->assertEquals('CUST-887766', $tx->no_nusraw_puchal);
        $this->assertEquals('2026-10-01', $tx->date_nusraw_puchal->format('Y-m-d'));
    }
}

