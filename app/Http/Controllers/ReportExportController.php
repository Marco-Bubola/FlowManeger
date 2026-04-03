<?php

namespace App\Http\Controllers;

use App\Exports\DashboardBanksExport;
use App\Exports\DashboardCashbookExport;
use App\Exports\DashboardProductsExport;
use Illuminate\Http\Request;
use App\Exports\VendasExport;
use App\Services\Dashboard\DashboardFinanceMetricsService;
use App\Services\Dashboard\DashboardProductsMetricsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
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

    public function exportDashboardProducts(Request $request, DashboardProductsMetricsService $service)
    {
        $format = strtolower($request->query('format', 'pdf'));
        if (!in_array($format, ['pdf', 'csv', 'xlsx'], true)) {
            $format = 'pdf';
        }

        $selectedMonth = (int) $request->query('month', now()->month);
        $selectedYear = (int) $request->query('year', now()->year);
        $periodPreset = (string) $request->query('preset', 'month');
        $selectedChannel = (string) $request->query('channel', 'all');

        $metrics = $service->getMetrics(
            (int) $request->user()->id,
            $selectedMonth,
            $selectedYear,
            $periodPreset,
            $selectedChannel,
        );

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.dashboard-products-pdf', ['metrics' => $metrics])->setPaper('a4', 'portrait');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'dashboard_produtos_' . now()->format('Ymd_His') . '.pdf');
        }

        if ($format === 'xlsx') {
            try {
                Storage::disk('local')->makeDirectory('exports');
                $path = 'exports/dashboard_produtos_' . now()->format('Ymd_His') . '.xlsx';
                $fullPath = Storage::disk('local')->path($path);

                $this->createDashboardProductsWorkbook($fullPath, $metrics);

                $resp = response()->download($fullPath, basename($fullPath), [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);

                if (method_exists($resp, 'deleteFileAfterSend')) {
                    $resp->deleteFileAfterSend(true);
                }

                return $resp;
            } catch (Throwable $e) {
                report($e);
                $format = 'csv';
            }
        }

        $export = new DashboardProductsExport($metrics);

        $filename = 'dashboard_produtos_' . now()->format('Ymd_His') . '.' . $format;
        $excelFacade = '\\Maatwebsite\\Excel\\Facades\\Excel';

        if (class_exists($excelFacade) && is_callable([$excelFacade, 'download'])) {
            try {
                return forward_static_call_array([$excelFacade, 'download'], [$export, $filename]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $collection = $export->collection();
            Storage::disk('local')->makeDirectory('exports');
            $path = 'exports/' . preg_replace('/\.(xlsx|csv)$/i', '.csv', $filename);

            $tmp = fopen('php://temp', 'r+');
            if ($tmp === false) {
                throw new \RuntimeException('Unable to open temporary memory stream for CSV generation');
            }

            fwrite($tmp, "\xEF\xBB\xBF");
            fputcsv($tmp, $export->headings());

            foreach ($collection as $row) {
                fputcsv($tmp, (array) $row);
            }

            rewind($tmp);
            $contents = stream_get_contents($tmp);
            fclose($tmp);

            $putResult = Storage::disk('local')->put($path, $contents);
            if ($putResult !== true && $putResult !== 1) {
                report(new \RuntimeException('Storage::put failed for path: ' . $path));
                return response()->json(['error' => 'Unable to save export file.'], 500);
            }

            $fullPath = Storage::disk('local')->path($path);
            $resp = response()->download($fullPath);
            if (method_exists($resp, 'deleteFileAfterSend')) {
                $resp->deleteFileAfterSend(true);
            }

            return $resp;
        } catch (Throwable $e) {
            report($e);
            return response()->json(['error' => 'Unable to generate export file.'], 500);
        }
    }

    public function exportDashboardCashbook(Request $request, DashboardFinanceMetricsService $service)
    {
        $format = strtolower($request->query('format', 'pdf'));
        if (!in_array($format, ['pdf', 'csv', 'xlsx'], true)) {
            $format = 'pdf';
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $cofrinho = $request->query('cofrinho');
        $cofrinhoId = is_numeric($cofrinho) ? (int) $cofrinho : null;

        $metrics = $service->getCashbookMetrics(
            (int) $request->user()->id,
            $year,
            $month,
            $cofrinhoId,
        );

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.dashboard-cashbook-pdf', [
                'metrics' => $metrics,
                'year' => $year,
                'month' => $month,
            ])->setPaper('a4', 'portrait');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'dashboard_financeiro_cashbook_' . now()->format('Ymd_His') . '.pdf');
        }

        return $this->downloadTabularExport(
            new DashboardCashbookExport($metrics),
            'dashboard_financeiro_cashbook_' . now()->format('Ymd_His') . '.' . $format,
            $format,
        );
    }

    public function exportDashboardBanks(Request $request, DashboardFinanceMetricsService $service)
    {
        $format = strtolower($request->query('format', 'pdf'));
        if (!in_array($format, ['pdf', 'csv', 'xlsx'], true)) {
            $format = 'pdf';
        }

        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $metrics = $service->getBanksMetrics((int) $request->user()->id, $year, $month);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.dashboard-banks-pdf', [
                'metrics' => $metrics,
                'year' => $year,
                'month' => $month,
            ])->setPaper('a4', 'portrait');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'dashboard_financeiro_bancos_' . now()->format('Ymd_His') . '.pdf');
        }

        return $this->downloadTabularExport(
            new DashboardBanksExport($metrics),
            'dashboard_financeiro_bancos_' . now()->format('Ymd_His') . '.' . $format,
            $format,
        );
    }

    protected function downloadTabularExport(object $export, string $filename, string $format)
    {
        $excelFacade = '\\Maatwebsite\\Excel\\Facades\\Excel';

        if ($format === 'xlsx' && !class_exists($excelFacade)) {
            $format = 'csv';
            $filename = preg_replace('/\.xlsx$/i', '.csv', $filename) ?? $filename;
        }

        if (class_exists($excelFacade) && is_callable([$excelFacade, 'download'])) {
            try {
                return forward_static_call_array([$excelFacade, 'download'], [$export, $filename]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $collection = $export->collection();
            Storage::disk('local')->makeDirectory('exports');
            $path = 'exports/' . preg_replace('/\.(xlsx|csv)$/i', '.csv', $filename);

            $tmp = fopen('php://temp', 'r+');
            if ($tmp === false) {
                throw new \RuntimeException('Unable to open temporary memory stream for CSV generation');
            }

            fwrite($tmp, "\xEF\xBB\xBF");

            if (method_exists($export, 'headings')) {
                fputcsv($tmp, $export->headings());
            }

            foreach ($collection as $row) {
                fputcsv($tmp, (array) $row);
            }

            rewind($tmp);
            $contents = stream_get_contents($tmp);
            fclose($tmp);

            $putResult = Storage::disk('local')->put($path, $contents);
            if ($putResult !== true && $putResult !== 1) {
                report(new \RuntimeException('Storage::put failed for path: ' . $path));
                return response()->json(['error' => 'Unable to save export file.'], 500);
            }

            $fullPath = Storage::disk('local')->path($path);
            $resp = response()->download($fullPath);
            if (method_exists($resp, 'deleteFileAfterSend')) {
                $resp->deleteFileAfterSend(true);
            }

            return $resp;
        } catch (Throwable $e) {
            report($e);
            return response()->json(['error' => 'Unable to generate export file.'], 500);
        }
    }

    protected function createDashboardProductsWorkbook(string $fullPath, array $metrics): void
    {
        $sheets = [
            ['name' => 'Resumo', 'rows' => $this->buildSummarySheetRows($metrics)],
            ['name' => 'Kits', 'rows' => $this->buildKitsSheetRows($metrics)],
            ['name' => 'Cobertura', 'rows' => $this->buildCoverageSheetRows($metrics)],
            ['name' => 'Publicacoes', 'rows' => $this->buildPublicationsSheetRows($metrics)],
        ];

        $zip = new ZipArchive();
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Nao foi possivel criar o arquivo Excel.');
        }

        $sheetEntries = [];
        $sheetRels = [];
        $contentTypes = [];

        foreach ($sheets as $index => $sheet) {
            $sheetNumber = $index + 1;
            $zip->addFromString('xl/worksheets/sheet' . $sheetNumber . '.xml', $this->buildWorksheetXml($sheet['rows']));

            $sheetEntries[] = '<sheet name="' . $this->escapeXml($sheet['name']) . '" sheetId="' . $sheetNumber . '" r:id="rId' . $sheetNumber . '"/>';
            $sheetRels[] = '<Relationship Id="rId' . $sheetNumber . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $sheetNumber . '.xml"/>';
            $contentTypes[] = '<Override PartName="/xl/worksheets/sheet' . $sheetNumber . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml($contentTypes));
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($sheetEntries));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml($sheetRels));
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());
        $zip->close();
    }

    protected function buildSummarySheetRows(array $metrics): array
    {
        $rows = [
            ['Dashboard de Produtos', '', '', ''],
            ['Periodo', (string) data_get($metrics, 'periodLabel', '-'), 'Canal', (string) data_get(data_get($metrics, 'channelMetrics', []), data_get($metrics, 'selectedChannel', 'all') . '.label', 'Visao geral')],
            ['', '', '', ''],
            ['Indicador', 'Valor', 'Comparacao', 'Delta %'],
        ];

        foreach (data_get($metrics, 'periodSummary', []) as $item) {
            $rows[] = [
                (string) data_get($item, 'label', '-'),
                (float) data_get($item, 'value', 0),
                'vs periodo anterior',
                round((float) data_get($item, 'delta', 0), 2),
            ];
        }

        $rows[] = ['', '', '', ''];
        $rows[] = ['Canal ativo', '', '', ''];
        $rows[] = ['Indicador', 'Valor', '', ''];

        foreach (data_get(data_get($metrics, 'channelMetrics', []), data_get($metrics, 'selectedChannel', 'all') . '.cards', []) as $card) {
            $rows[] = [(string) data_get($card, 'label', '-'), (float) data_get($card, 'value', 0), '', ''];
        }

        $rows[] = ['', '', '', ''];
        $rows[] = ['Top produtos do canal', 'Unidades', 'Receita', 'Codigo'];

        foreach (data_get(data_get($metrics, 'channelMetrics', []), data_get($metrics, 'selectedChannel', 'all') . '.topProducts', []) as $product) {
            $rows[] = [
                (string) data_get($product, 'name', '-'),
                (int) data_get($product, 'units', 0),
                (float) data_get($product, 'revenue', 0),
                (string) data_get($product, 'product_code', '-'),
            ];
        }

        return $rows;
    }

    protected function buildKitsSheetRows(array $metrics): array
    {
        $rows = [
            ['Resumo de kits', '', '', ''],
            ['Kits vendidos', (int) data_get($metrics, 'kitsVendidosPeriodo', 0), 'Receita kits', (float) data_get($metrics, 'receitaKitsPeriodo', 0)],
            ['Componentes consumidos', (int) data_get($metrics, 'componentesConsumidosViaKits', 0), 'Produtos ligados a kits', (int) data_get($metrics, 'produtosLigadosKits', 0)],
            ['', '', '', ''],
            ['Kit', 'Codigo', 'Quantidade', 'Receita'],
        ];

        foreach (data_get($metrics, 'topKits', []) as $kit) {
            $rows[] = [
                (string) data_get($kit, 'name', '-'),
                (string) data_get($kit, 'product_code', '-'),
                (int) data_get($kit, 'total_vendido', 0),
                (float) data_get($kit, 'receita_total', 0),
            ];
        }

        return $rows;
    }

    protected function buildCoverageSheetRows(array $metrics): array
    {
        $rows = [
            ['Cobertura por produto', '', '', '', ''],
            ['Produto', 'Codigo', 'Estoque', 'Demanda/dia', 'Cobertura em dias'],
        ];

        foreach (data_get($metrics, 'coberturaProdutos', []) as $item) {
            $rows[] = [
                (string) data_get($item, 'name', '-'),
                (string) data_get($item, 'product_code', '-'),
                (int) data_get($item, 'stock_quantity', 0),
                (float) data_get($item, 'daily_demand', 0),
                data_get($item, 'coverage_days') !== null ? (float) data_get($item, 'coverage_days', 0) : 'Sem giro',
            ];
        }

        $rows[] = ['', '', '', '', ''];
        $rows[] = ['Cobertura por categoria', '', '', '', ''];
        $rows[] = ['Categoria', 'Estoque', 'Demanda total', 'Demanda/dia', 'Cobertura em dias'];

        foreach (data_get($metrics, 'coberturaCategorias', []) as $item) {
            $rows[] = [
                (string) data_get($item, 'category_name', '-'),
                (int) data_get($item, 'stock_quantity', 0),
                (int) data_get($item, 'effective_quantity', 0),
                (float) data_get($item, 'daily_demand', 0),
                data_get($item, 'coverage_days') !== null ? (float) data_get($item, 'coverage_days', 0) : 'Sem giro',
            ];
        }

        return $rows;
    }

    protected function buildPublicationsSheetRows(array $metrics): array
    {
        $rows = [
            ['Publicacoes e syncs', '', '', ''],
            ['Indicador', 'Valor', '', ''],
        ];

        foreach (data_get(data_get($metrics, 'marketplacePeriodMetrics', []), 'cards', []) as $card) {
            $rows[] = [(string) data_get($card, 'label', '-'), (int) data_get($card, 'value', 0), '', ''];
        }

        $rows[] = ['', '', '', ''];
        $rows[] = ['Tendencia mensal', 'ML', 'Shopee', ''];

        foreach (data_get(data_get($metrics, 'marketplacePeriodMetrics', []), 'trend', []) as $item) {
            $rows[] = [
                (string) data_get($item, 'label', '-'),
                (int) data_get($item, 'ml', 0),
                (int) data_get($item, 'shopee', 0),
                '',
            ];
        }

        return $rows;
    }

    protected function buildWorksheetXml(array $rows): string
    {
        $sheetData = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];
            foreach (array_values($row) as $columnIndex => $value) {
                $cellReference = $this->columnLetter($columnIndex + 1) . ($rowIndex + 1);
                $styleId = $rowIndex === 0 ? 1 : 0;

                if (is_int($value) || is_float($value)) {
                    $cells[] = '<c r="' . $cellReference . '" s="' . $styleId . '"><v>' . $value . '</v></c>';
                    continue;
                }

                $cells[] = '<c r="' . $cellReference . '" s="' . $styleId . '" t="inlineStr"><is><t>' . $this->escapeXml((string) $value) . '</t></is></c>';
            }

            $sheetData[] = '<row r="' . ($rowIndex + 1) . '">' . implode('', $cells) . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/>'
            . '<cols><col min="1" max="8" width="24" customWidth="1"/></cols>'
            . '<sheetData>' . implode('', $sheetData) . '</sheetData>'
            . '</worksheet>';
    }

    protected function buildContentTypesXml(array $sheetOverrides): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . implode('', $sheetOverrides)
            . '</Types>';
    }

    protected function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    protected function buildWorkbookXml(array $sheetEntries): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<workbookPr/>'
            . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="12000"/></bookViews>'
            . '<sheets>' . implode('', $sheetEntries) . '</sheets>'
            . '</workbook>';
    }

    protected function buildWorkbookRelsXml(array $sheetRelationships): string
    {
        $styleRelationId = count($sheetRelationships) + 1;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . implode('', $sheetRelationships)
            . '<Relationship Id="rId' . $styleRelationId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    protected function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    protected function columnLetter(int $columnNumber): string
    {
        $letter = '';

        while ($columnNumber > 0) {
            $modulo = ($columnNumber - 1) % 26;
            $letter = chr(65 + $modulo) . $letter;
            $columnNumber = (int) (($columnNumber - $modulo) / 26);
            $columnNumber--;
        }

        return $letter;
    }

    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
