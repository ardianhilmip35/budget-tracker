<?php

namespace App\Http\Controllers;

use App\Models\BudgetProfile;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\CycleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function monthly(Request $request, CycleService $cycles): StreamedResponse
    {
        $cycle = $cycles->resolve($request->query('cycle'));
        $profile = $this->activeProfile();
        $transactions = $this->transactionsFor($cycle);

        $spreadsheet = new Spreadsheet();
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ringkasan');

        $summarySheet->fromArray([
            ['REKAP BUDGET BULANAN'],
            ['Siklus', $cycle['label']],
            ['Profil', $profile->name],
            ['THP', $profile->thp],
            [],
            ['Kategori', 'Budget', 'Realisasi', 'Sisa'],
        ], null, 'A1');

        $byCategory = $transactions->groupBy('category')->map->sum('amount');
        $row = 7;
        foreach ($profile->budgets() as $key => $item) {
            $realization = (int) ($byCategory[$key] ?? 0);
            $summarySheet->fromArray([
                $item['label'],
                (int) $item['budget'],
                $realization,
                (int) $item['budget'] - $realization,
            ], null, 'A'.$row);
            $row++;
        }
        $summarySheet->fromArray(['TOTAL', "=SUM(B7:B13)", "=SUM(C7:C13)", "=SUM(D7:D13)"], null, 'A14');

        $detail = $spreadsheet->createSheet();
        $detail->setTitle('Transaksi');
        $detail->fromArray(['Tanggal', 'Kategori', 'Keterangan', 'Nominal', 'Metode', 'Catatan'], null, 'A1');
        $r = 2;
        foreach ($transactions as $tx) {
            $detail->fromArray([
                $tx->transaction_date->format('Y-m-d'),
                $this->categoryLabel($tx->category),
                $tx->subcategory,
                $tx->amount,
                $tx->payment_method,
                $tx->note,
            ], null, 'A'.$r++);
        }

        $this->styleWorkbook($spreadsheet);

        return $this->download($spreadsheet, 'budget-'.$cycle['key'].'.xlsx');
    }

    public function history(CycleService $cycles): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Bulanan');
        $sheet->fromArray([
            'Bulan Siklus', 'Mulai', 'Akhir', 'Kos', 'Operasional Jakarta',
            'Allianz Jiwa/CI', 'Dana Darurat', 'Saham BMRI', 'Tabungan Emas',
            'Uang Bebas', 'Total',
        ], null, 'A1');

        $firstDate = Transaction::query()->min('transaction_date');
        $lastDate = Transaction::query()->max('transaction_date');
        $row = 2;

        if ($firstDate) {
            $firstKey = $cycles->cycleKeyForDate(CarbonImmutable::parse($firstDate, 'Asia/Jakarta'));
            $lastKey = $cycles->cycleKeyForDate(CarbonImmutable::parse($lastDate, 'Asia/Jakarta'));
            $cursor = CarbonImmutable::createFromFormat('Y-m-d', $firstKey.'-01', 'Asia/Jakarta');
            $end = CarbonImmutable::createFromFormat('Y-m-d', $lastKey.'-01', 'Asia/Jakarta');

            while ($cursor <= $end) {
                $cycle = $cycles->resolve($cursor->format('Y-m'));
                $items = $this->transactionsFor($cycle);
                $sum = $items->groupBy('category')->map->sum('amount');

                $sheet->fromArray([
                    $cursor->translatedFormat('F Y'),
                    $cycle['start']->format('Y-m-d'),
                    $cycle['end_exclusive']->subDay()->format('Y-m-d'),
                    (int) ($sum['kos'] ?? 0),
                    (int) ($sum['operasional'] ?? 0),
                    (int) ($sum['allianz'] ?? 0),
                    (int) ($sum['dana_darurat'] ?? 0),
                    (int) ($sum['bmri'] ?? 0),
                    (int) ($sum['emas'] ?? 0),
                    (int) ($sum['uang_bebas'] ?? 0),
                    (int) $items->sum('amount'),
                ], null, 'A'.$row++);

                $cursor = $cursor->addMonth();
            }
        }

        $detail = $spreadsheet->createSheet();
        $detail->setTitle('Semua Transaksi');
        $detail->fromArray(['Tanggal', 'Kategori', 'Keterangan', 'Nominal', 'Metode', 'Catatan'], null, 'A1');
        $r = 2;
        foreach (Transaction::query()->orderBy('transaction_date')->orderBy('id')->get() as $tx) {
            $detail->fromArray([
                $tx->transaction_date->format('Y-m-d'),
                $this->categoryLabel($tx->category),
                $tx->subcategory,
                $tx->amount,
                $tx->payment_method,
                $tx->note,
            ], null, 'A'.$r++);
        }

        $this->styleWorkbook($spreadsheet);

        return $this->download($spreadsheet, 'riwayat-budget.xlsx');
    }

    private function activeProfile(): BudgetProfile
    {
        $id = (int) Setting::getValue('active_profile_id', '1');
        return BudgetProfile::query()->find($id) ?? BudgetProfile::query()->firstOrFail();
    }

    private function transactionsFor(array $cycle)
    {
        return Transaction::query()
            ->where('transaction_date', '>=', $cycle['start']->toDateString())
            ->where('transaction_date', '<', $cycle['end_exclusive']->toDateString())
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }

    private function categoryLabel(string $key): string
    {
        return [
            'kos' => 'Kos',
            'operasional' => 'Operasional Jakarta',
            'allianz' => 'Allianz Jiwa/CI',
            'dana_darurat' => 'Dana Darurat',
            'bmri' => 'Saham BMRI',
            'emas' => 'Tabungan Emas',
            'uang_bebas' => 'Uang Bebas',
        ][$key] ?? $key;
    }

    private function styleWorkbook(Spreadsheet $spreadsheet): void
    {
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $lastColumn = $sheet->getHighestColumn();
            $lastRow = $sheet->getHighestRow();
            $sheet->freezePane('A2');
            $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true);
            $sheet->getStyle('A1:'.$lastColumn.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8EEF9');
            $sheet->getStyle('A1:'.$lastColumn.$lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
    }

    private function download(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
