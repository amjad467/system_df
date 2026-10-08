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

    public function test_lookup_previous_api_returns_matching_results(): void
    {
        $admin = User::where('user_login', 'admin')->first();
        $tx = Transaction::first();

        $response = $this->actingAs($admin)->getJson(route('transactions.lookup_previous_api', ['query' => $tx->plate_number]));
        $response->assertStatus(200);
        $response->assertJsonFragment(['plate_number' => $tx->plate_number]);
    }

    public function test_expired_booklets_report_endpoint(): void
    {
        $admin = User::where('user_login', 'admin')->first();
        
        $response = $this->actingAs($admin)->get(route('transactions.expired_booklets', ['status' => 'all']));
        $response->assertStatus(200);
        $response->assertSee('ڕاپۆرتی دەفتەرە بەسەرچووەکان');
    }

    public function test_renewal_links_parent_transaction(): void
    {
        $dataEntry = User::where('user_login', 'dataentry')->first();
        $parentTx = Transaction::first();

        $response = $this->actingAs($dataEntry)->post(route('transactions.store'), [
            'parent_transaction_id' => $parentTx->id,
            'transaction_type_id' => 2, // Renewal (تازەکردنەوە)
            'visitor_name' => $parentTx->visitor_name,
            'plate_number' => $parentTx->plate_number,
            'model_year' => $parentTx->model_year ?? '2023',
            'chassis_number' => $parentTx->chassis_number ?? 'KMH12345678901234',
            'num_years' => 1,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $newTx = Transaction::where('parent_transaction_id', $parentTx->id)->where('transaction_type_id', 2)->first();
        $this->assertNotNull($newTx);
        $this->assertEquals($parentTx->id, $newTx->parent_transaction_id);
    }

    public function test_phone_number_and_driver_details_in_audit_and_search(): void
    {
        $user = User::where('user_login', 'auditor')->first();

        $tx = Transaction::create([
            'barcode' => '202610090099',
            'transaction_type_id' => 1,
            'visitor_name' => 'ئارام کەریم عەلی',
            'visitor_name_eng' => 'Aram Karim Ali',
            'phone_number' => '07701234567',
            'second_driver_name' => 'سەردار قادر محەمەد',
            'second_driver_name_eng' => 'Sardar Qadir Muhammad',
            'plate_number' => '45678',
            'plate_type_id' => 1,
            'traffic_directorate_id' => 1,
            'car_make_id' => 1,
            'car_color_id' => 1,
            'model_year' => '2022',
            'chassis_number' => 'WBA12345678901234',
            'piston_count' => 4,
            'num_years' => 1,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'is_inspected' => true,
            'is_audited' => false,
            'user_input' => 'test_user',
        ]);

        // 1. Auditor view displays all first and second driver names (Kurdish + English) and phone
        $response = $this->actingAs($user)->get(route('transactions.show', $tx->id));
        $response->assertStatus(200);
        $response->assertSee('ئارام کەریم عەلی');
        $response->assertSee('Aram Karim Ali');
        $response->assertSee('07701234567');
        $response->assertSee('سەردار قادر محەمەد');
        $response->assertSee('Sardar Qadir Muhammad');

        // 2. Scan / Search by phone number redirects directly to transaction show
        $scanResponse = $this->actingAs($user)->post(route('transactions.scan'), [
            'barcode' => '07701234567',
        ]);
        $scanResponse->assertRedirect(route('transactions.show', $tx->id));

        // 3. Search in transactions index finds the record
        $indexResponse = $this->actingAs($user)->get(route('transactions.index', ['search' => '07701234567']));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('07701234567');
        $indexResponse->assertSee('ئارام کەریم عەلی');
    }

    public function test_booklet_print_inactive_until_booklet_number_assigned(): void
    {
        $admin = User::where('user_login', 'admin')->first();

        // Transaction requiring booklet, is_paid = true, but booklet_number is NULL
        $tx = Transaction::create([
            'barcode' => '202610091122',
            'transaction_type_id' => 1, // دەرهێنانی دەفتەر (Requires Booklet)
            'visitor_name' => 'بەهمەن ئەحمەد حەسەن',
            'plate_number' => '99887',
            'plate_type_id' => 1,
            'traffic_directorate_id' => 1,
            'car_make_id' => 1,
            'car_color_id' => 1,
            'model_year' => '2023',
            'chassis_number' => 'WBA99887766554433',
            'piston_count' => 6,
            'num_years' => 1,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
            'is_inspected' => true,
            'is_audited' => true,
            'is_paid' => true,
            'receipt_37a_number' => 'REC-9988',
            'booklet_number' => null,
            'is_booklet_completed' => false,
            'user_input' => 'test_user',
        ]);

        $this->assertTrue($tx->requiresBooklet());
        $this->assertNull($tx->booklet_number);

        // 1. Direct attempt to print booklet before receiving booklet number must redirect with error
        $printResponse = $this->actingAs($admin)->get(route('transactions.print_booklet', $tx->id));
        $printResponse->assertRedirect();
        $printResponse->assertSessionHas('error');

        // 2. show.blade.php should display disabled booklet print button
        $showResponse = $this->actingAs($admin)->get(route('transactions.show', $tx->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('چاپی دەفتەر (ناچالاکە - بێ ژمارە)');

        // 3. Complete booklet assignment
        $completeResponse = $this->actingAs($admin)->post(route('transactions.complete_booklet', $tx->id), [
            'booklet_number' => 'DF-998811',
        ]);
        $completeResponse->assertRedirect();
        $tx->refresh();
        $this->assertEquals('DF-998811', $tx->booklet_number);
        $this->assertTrue((bool)$tx->is_booklet_completed);

        // 4. Now booklet print is active and returns HTTP 200
        $activePrintResponse = $this->actingAs($admin)->get(route('transactions.print_booklet', $tx->id));
        $activePrintResponse->assertStatus(200);
        $activePrintResponse->assertSee('DF-998811');

        // 5. show.blade.php now displays active print button with booklet number
        $updatedShowResponse = $this->actingAs($admin)->get(route('transactions.show', $tx->id));
        $updatedShowResponse->assertStatus(200);
        $updatedShowResponse->assertSee('چاپی دەفتەر (DF-998811)');
    }
}

