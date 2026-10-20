<?php

namespace App\Services\Products;

/**
 * Lê uma planilha simples (CSV ou XLSX, só a primeira aba) como lista de
 * linhas. Sem dependências: o phpexcel do composer é antigo demais.
 */
class SheetReader
{
    /** @return array<int, array<int, string>> */
    public function read(string $path, string $extension): array
    {
        $rows = strtolower($extension) === 'xlsx' ? $this->readXlsx($path) : $this->readCsv($path);

        // Tira linhas totalmente vazias e espaços nas pontas
        return array_values(array_filter(
            array_map(fn ($r) => array_map(fn ($c) => trim((string) $c), $r), $rows),
            fn ($r) => implode('', $r) !== ''
        ));
    }

    private function readCsv(string $path): array
    {
        $text = (string) file_get_contents($path);
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        // Excel no Windows salva CSV em Windows-1252
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }
        $first = strtok($text, "\n") ?: '';
        $sep = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
        if (substr_count($first, "\t") > max(substr_count($first, ';'), substr_count($first, ','))) {
            $sep = "\t";
        }

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $text);
        rewind($fh);
        $rows = [];
        while (($r = fgetcsv($fh, 0, $sep, '"', '')) !== false) {
            $rows[] = $r;
        }
        fclose($fh);

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Não deu para abrir a planilha. Salve de novo como .xlsx ou .csv.');
        }

        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                // Texto simples (<t>) ou com formatação (<r><t>)
                $shared[] = isset($si->t) ? (string) $si->t : implode('', array_map(fn ($r) => (string) $r->t, iterator_to_array($si->r, false)));
            }
        }

        $sheetPath = 'xl/worksheets/sheet1.xml';
        if (($wb = $zip->getFromName('xl/workbook.xml')) !== false && ($rels = $zip->getFromName('xl/_rels/workbook.xml.rels')) !== false) {
            $wbx = simplexml_load_string($wb);
            $first = $wbx->sheets->sheet[0] ?? null;
            $rid = $first ? (string) $first->attributes('r', true)->id : '';
            foreach (simplexml_load_string($rels)->Relationship as $rel) {
                if ((string) $rel['Id'] === $rid) {
                    $sheetPath = 'xl/' . ltrim(str_replace('/xl/', '', (string) $rel['Target']), '/');
                }
            }
        }

        $xml = $zip->getFromName($sheetPath);
        $zip->close();
        if ($xml === false) {
            throw new \RuntimeException('A planilha não tem nenhuma aba com dados.');
        }

        $rows = [];
        foreach (simplexml_load_string($xml)->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $c) {
                $col = $this->columnIndex(preg_replace('/\d+/', '', (string) $c['r']));
                $type = (string) $c['t'];
                $value = match ($type) {
                    's' => $shared[(int) $c->v] ?? '',
                    'inlineStr' => (string) ($c->is->t ?? ''),
                    'b' => ((string) $c->v) === '1' ? 'sim' : 'não',
                    default => (string) $c->v,
                };
                $line[$col] = $value;
            }
            if ($line) {
                $max = max(array_keys($line));
                $rows[] = array_map(fn ($i) => $line[$i] ?? '', range(0, $max));
            }
        }

        return $rows;
    }

    private function columnIndex(string $letters): int
    {
        $n = 0;
        foreach (str_split(strtoupper($letters)) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return max(0, $n - 1);
    }
}
