<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HajjPilgrim;
use App\Models\HajjPackage;
use App\Models\Customer;
use Illuminate\Support\Arr;

class HajjPilgrimSeeder extends Seeder
{
    public function run(): void
    {
        $hajjPackageIds = HajjPackage::pluck('id')->all();
        $customerIds    = Customer::pluck('id')->all();

        // Statuses come from HajjPilgrimRepository constants. payment_status
        // is NOT seeded — it is derived from the money by HajjPilgrimObserver,
        // which also raises each pilgrim's invoice and receipts the paid part.
        // The paid share below: 1.0 = settled, 0.4 = instalments running,
        // 0.0 = registered but nothing received yet.
        // pilgrim_no, name, passport_no, package_title, group_name, document_status, status, paid_share
        $rows = [
            ['PIL-1001', 'Abdul Karim',   'BD1234567', 'Umrah Premium', 'Group A', 'Verified',  'Confirmed',  1.0],
            ['PIL-1002', 'Salma Begum',   'BD1234568', 'Umrah Premium', 'Group A', 'Pending',   'Confirmed',  0.4],
            ['PIL-1003', 'Rahim Uddin',   'BD1234569', 'Hajj Standard', 'Group B', 'Pending',   'Registered', 0.4],
            ['PIL-1004', 'Ayesha Khatun', 'BD1234570', 'Hajj Standard', 'Group B', 'Verified',  'Confirmed',  1.0],
            ['PIL-1005', 'Jashim Mia',    'BD1234571', 'Umrah Economy', 'Group C', 'Pending',   'Registered', 0.0],
            ['PIL-1006', 'Nasrin Akter',  'BD1234572', 'Umrah Economy', 'Group C', 'Pending',   'Registered', 0.4],
            ['PIL-1007', 'Mizanur Rahman','BD1234573', 'Hajj VIP',      'Group B', 'Verified',  'Confirmed',  1.0],
            ['PIL-1008', 'Fatema Yasmin', 'BD1234574', 'Umrah Family',  'Group A', 'Pending',   'Registered', 0.0],
        ];

        foreach ($rows as $r) {
            $packageId = $hajjPackageIds ? Arr::random($hajjPackageIds) : null;
            $price     = (float) (HajjPackage::find($packageId)?->price ?? 250000);
            $paid      = round($price * $r[7], 2);

            HajjPilgrim::updateOrCreate(
                ['pilgrim_no' => $r[0]],
                [
                    'hajj_package_id' => $packageId,
                    'customer_id'     => $customerIds ? Arr::random($customerIds) : null,
                    'name'            => $r[1],
                    'passport_no'     => $r[2],
                    'package_title'   => $r[3],
                    'group_name'      => $r[4],
                    'document_status' => $r[5],
                    'status'          => $r[6],
                    'amount_paid'     => $paid,
                    'amount_due'      => round($price - $paid, 2),
                ]
            );
        }
    }
}
