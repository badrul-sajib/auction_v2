<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    private const IMPORT_COLUMNS = ['name', 'email', 'phone', 'address'];

    public function sampleImport()
    {
        $rows = [
            ['name', 'email', 'phone', 'address'],
            ['Acme Traders', 'contact@acme.com', '01700000000', 'Dhaka, Bangladesh'],
            ['Ghorer Bazar', 'hello@ghorerbazar.com', '01800000000', 'Rangpur, Bangladesh'],
        ];

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="merchants-import-sample.csv"',
        ]);
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('status', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle, null, ',', '"', '');
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header ?: []);

        $rows = [];
        while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
            if (count(array_filter($row, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }
            $assoc = array_combine($header, array_pad($row, count($header), null));
            $rows[] = collect(self::IMPORT_COLUMNS)
                ->mapWithKeys(fn ($col) => [$col => trim((string) ($assoc[$col] ?? ''))])
                ->all();
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->with('status', 'No data rows found in the file.');
        }

        return view('pages.inventory.merchants.import-preview', [
            'rows' => $rows,
            'columns' => self::IMPORT_COLUMNS,
        ]);
    }

    public function import(Request $request)
    {
        $rows = $request->input('rows', []);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, &$created, &$updated, &$skipped) {
            foreach ($rows as $data) {
                $name = trim((string) ($data['name'] ?? ''));
                if ($name === '') {
                    $skipped++;
                    continue;
                }

                $merchant = Merchant::firstOrNew(['name' => $name]);
                $wasExisting = $merchant->exists;

                $merchant->fill([
                    'email' => filled($data['email'] ?? null) ? trim($data['email']) : null,
                    'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
                    'address' => filled($data['address'] ?? null) ? trim($data['address']) : null,
                ])->save();

                $wasExisting ? $updated++ : $created++;
            }
        });

        return redirect()->route('inventory.merchants.index')
            ->with('status', "Import complete: {$created} added, {$updated} updated, {$skipped} skipped.");
    }

    public function index()
    {
        $merchants = Merchant::withCount('products')->latest()->paginate(10);

        return view('pages.inventory.merchants.index', compact('merchants'));
    }

    public function create()
    {
        return view('pages.inventory.merchants.create', ['merchant' => new Merchant()]);
    }

    public function store(Request $request)
    {
        Merchant::create($this->validateData($request));

        return redirect()->route('inventory.merchants.index')->with('status', 'Merchant created.');
    }

    public function edit(Merchant $merchant)
    {
        return view('pages.inventory.merchants.edit', compact('merchant'));
    }

    public function update(Request $request, Merchant $merchant)
    {
        $merchant->update($this->validateData($request));

        return redirect()->route('inventory.merchants.index')->with('status', 'Merchant updated.');
    }

    public function destroy(Merchant $merchant)
    {
        $merchant->delete();

        return redirect()->route('inventory.merchants.index')->with('status', 'Merchant deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
