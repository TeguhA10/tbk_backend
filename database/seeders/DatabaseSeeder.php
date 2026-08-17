<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coa;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Category::count() > 0) {
            return;
        }

        // 1. Create Categories
        $salaryCat = Category::create(['name' => 'Salary', 'type' => 'income']);
        $otherIncomeCat = Category::create(['name' => 'Other Income', 'type' => 'income']);
        $familyExpCat = Category::create(['name' => 'Family Expense', 'type' => 'expense']);
        $transportExpCat = Category::create(['name' => 'Transport Expense', 'type' => 'expense']);
        $mealExpCat = Category::create(['name' => 'Meal Expense', 'type' => 'expense']);

        // 2. Create Chart of Accounts (COA)
        $coa401 = Coa::create(['code' => '401', 'name' => 'Gaji Karyawan', 'category_id' => $salaryCat->id]);
        $coa402 = Coa::create(['code' => '402', 'name' => 'Gaji Ketua MPR', 'category_id' => $salaryCat->id]);
        $coa403 = Coa::create(['code' => '403', 'name' => 'Profit Trading', 'category_id' => $otherIncomeCat->id]);

        $coa601 = Coa::create(['code' => '601', 'name' => 'Biaya Sekolah', 'category_id' => $familyExpCat->id]);
        $coa602 = Coa::create(['code' => '602', 'name' => 'Bensin', 'category_id' => $transportExpCat->id]);
        $coa603 = Coa::create(['code' => '603', 'name' => 'Parkir', 'category_id' => $transportExpCat->id]);
        $coa604 = Coa::create(['code' => '604', 'name' => 'Makan Siang', 'category_id' => $mealExpCat->id]);
        $coa605 = Coa::create(['code' => '605', 'name' => 'Makana Pokok Bulanan', 'category_id' => $mealExpCat->id]);

        // 3. Create Sample Transactions matching Excel data
        // --- 2022-01 ---
        Transaction::create(['date' => '2022-01-05', 'coa_id' => $coa401->id, 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000]);
        Transaction::create(['date' => '2022-01-20', 'coa_id' => $coa401->id, 'description' => 'Gaji Di Perusahaan A (Bonus)', 'debit' => 0, 'credit' => 7000000]);
        Transaction::create(['date' => '2022-01-15', 'coa_id' => $coa402->id, 'description' => 'Gaji Ketum', 'debit' => 0, 'credit' => 5500000]);
        Transaction::create(['date' => '2022-01-10', 'coa_id' => $coa601->id, 'description' => 'Biaya SPP Sekolah', 'debit' => 500000, 'credit' => 0]);
        Transaction::create(['date' => '2022-01-12', 'coa_id' => $coa602->id, 'description' => 'Bensin Anak & Kantor', 'debit' => 200000, 'credit' => 0]);
        Transaction::create(['date' => '2022-01-18', 'coa_id' => $coa604->id, 'description' => 'Makan Siang Tim', 'debit' => 150000, 'credit' => 0]);

        // --- 2022-02 ---
        Transaction::create(['date' => '2022-02-01', 'coa_id' => $coa401->id, 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 12000000]);
        Transaction::create(['date' => '2022-02-14', 'coa_id' => $coa403->id, 'description' => 'Profit Trading Saham', 'debit' => 0, 'credit' => 6000000]);
        Transaction::create(['date' => '2022-02-05', 'coa_id' => $coa601->id, 'description' => 'Uang Buku & Kegiatan Sekolah', 'debit' => 3500000, 'credit' => 0]);
        Transaction::create(['date' => '2022-02-10', 'coa_id' => $coa602->id, 'description' => 'Bensin Bulanan', 'debit' => 200000, 'credit' => 0]);
        Transaction::create(['date' => '2022-02-12', 'coa_id' => $coa603->id, 'description' => 'Parkir Langganan', 'debit' => 50000, 'credit' => 0]);
        Transaction::create(['date' => '2022-02-15', 'coa_id' => $coa604->id, 'description' => 'Makan Siang Kantor', 'debit' => 200000, 'credit' => 0]);
        Transaction::create(['date' => '2022-02-20', 'coa_id' => $coa605->id, 'description' => 'Belanja Sembako Bulanan', 'debit' => 100000, 'credit' => 0]);

        // --- 2022-03 ---
        Transaction::create(['date' => '2022-03-01', 'coa_id' => $coa401->id, 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 12000000]);
        Transaction::create(['date' => '2022-03-10', 'coa_id' => $coa403->id, 'description' => 'Profit Trading Crypto', 'debit' => 0, 'credit' => 3500000]);
        Transaction::create(['date' => '2022-03-04', 'coa_id' => $coa601->id, 'description' => 'Uang Pangkal & Kursus', 'debit' => 4500000, 'credit' => 0]);
        Transaction::create(['date' => '2022-03-11', 'coa_id' => $coa602->id, 'description' => 'Bensin Tol', 'debit' => 175000, 'credit' => 0]);
        Transaction::create(['date' => '2022-03-15', 'coa_id' => $coa603->id, 'description' => 'Parkir Mall & Kantor', 'debit' => 50000, 'credit' => 0]);
        Transaction::create(['date' => '2022-03-22', 'coa_id' => $coa605->id, 'description' => 'Makana Pokok Bulanan Rumah', 'debit' => 175000, 'credit' => 0]);
    }
}
