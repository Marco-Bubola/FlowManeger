<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fluxo de Caixa - {{ $ano }}</title>
    <style>
        body {
            font-family: sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Fluxo de Caixa - {{ $ano }}</h1>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Categoria</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashbooks as $cashbook)
                <tr>
                    <td>{{ $cashbook->date }}</td>
                    <td>{{ $cashbook->description }}</td>
                    <td>R$ {{ number_format($cashbook->value, 2, ',', '.') }}</td>
                    <td>{{ $cashbook->type->desc_type }}</td>
                    <td>{{ $cashbook->category->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
