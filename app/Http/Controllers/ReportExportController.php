<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\VendasExport;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReportExportController extends Controller
{
    /**
     * Export vendas as CSV or XLSX
     * Example: /reports/vendas/export?format=csv&client_id=1&from=2025-01-01&to=2025-10-01
     */
    public function exportVendas(Request $request)
    {
        $format = strtolower($request->query('format', 'csv'));
        $allowed = ['csv', 'xlsx'];
        if (!in_array($format, $allowed)) {
            $format = 'csv';
        }

        $filters = [
            'client_id' => $request->query('client_id'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];

        $export = new VendasExport($filters);

        // Ensure filename extension matches format. If user requested xlsx but the
        // Maatwebsite package is not installed, we'll fallback to CSV and use .csv
        // extension to avoid misleading file types.
        if ($format === 'xlsx' && !class_exists('\Maatwebsite\\Excel\\Facades\\Excel')) {
            $format = 'csv';
        }

        $filename = 'vendas_' . now()->format('Ymd_His') . '.' . $format;

        // If Maatwebsite\Excel facade is available, use it to stream the requested format.
        $excelFacade = '\\Maatwebsite\\Excel\\Facades\\Excel';
        if (class_exists($excelFacade) && is_callable([$excelFacade, 'download'])) {
            try {
                return forward_static_call_array([$excelFacade, 'download'], [$export, $filename]);
            } catch (\Throwable $e) {
                // If resolving the facade or performing the download fails (for example
                // the package was not properly registered), log and fall back to CSV.
                report($e);
            }
        }

        // Fallback: create temporary CSV using Collection
        try {
            $collection = $export->collection();

            // Ensure exports directory exists in storage/app
            Storage::disk('local')->makeDirectory('exports');

            // Force .csv filename for fallback
            $path = 'exports/' . preg_replace('/\.(xlsx|csv)$/i', '.csv', $filename);

            // Generate CSV in memory using a temp stream to avoid direct fopen on filesystem
            $tmp = fopen('php://temp', 'r+');
            if ($tmp === false) {
                throw new \RuntimeException('Unable to open temporary memory stream for CSV generation');
            }

            // Write UTF-8 BOM so Excel opens UTF-8 CSV correctly
            fwrite($tmp, "\xEF\xBB\xBF");

            // Use headings() if available, otherwise derive from first row
            $headings = [];
            if (method_exists($export, 'headings')) {
                $headings = $export->headings();
            } elseif ($collection->isNotEmpty()) {
                $headings = array_keys((array) $collection->first());
            }

            if (!empty($headings)) {
                fputcsv($tmp, $headings);
            }

            foreach ($collection as $row) {
                fputcsv($tmp, (array) $row);
            }

            rewind($tmp);
            $contents = stream_get_contents($tmp);
            fclose($tmp);

            // Store the file using Laravel Storage (local disk)
            $putResult = Storage::disk('local')->put($path, $contents);
            if ($putResult !== true && $putResult !== 1) {
                // put can return true on success; if not, report and error
                report(new \RuntimeException('Storage::put failed for path: ' . $path));
                return response()->json(['error' => 'Unable to save export file.'], 500);
            }

            // Verify file exists
            if (!Storage::disk('local')->exists($path)) {
                report(new \RuntimeException('Export file not found after write: ' . $path));
                return response()->json(['error' => 'Unable to generate export file.'], 500);
            }

            // Return as download and delete after send using Storage helper
            try {
                // Resolve the actual filesystem path for the configured 'local' disk.
                // The 'local' disk in this app is configured to storage_path('app/private'),
                // so use Storage::disk('local')->path($path) to get the correct on-disk path.
                $fullPath = Storage::disk('local')->path($path);
                if (!file_exists($fullPath)) {
                    report(new \RuntimeException('Export file missing on disk: ' . $fullPath));
                    return response()->json(['error' => 'Unable to generate export file.'], 500);
                }

                $resp = response()->download($fullPath);
                if (method_exists($resp, 'deleteFileAfterSend')) {
                    $resp->deleteFileAfterSend(true);
                }
                return $resp;
            } catch (\Throwable $e) {
                report($e);
                return response()->json(['error' => 'Unable to serve export file.'], 500);
            }
        } catch (Throwable $e) {
            // If something goes wrong during fallback generation, return a 500 with message.
            report($e);
            return response()->json(['error' => 'Unable to generate export file.'], 500);
        }
    }
}
