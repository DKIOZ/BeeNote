<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Registre BeeNote</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1B2B25; }
  h1 { font-size: 18px; margin: 0 0 4px; color: #17382C; }
  p { margin: 0 0 12px; color: #3D4F47; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; background: #17382C; color: #fff; padding: 5px; }
  td { padding: 5px; border-bottom: 1px solid #CFD3C8; vertical-align: top; }
</style>
</head>
<body>
  <h1>Registre</h1>
  <p>{{ $proprietaire }} · du {{ $depuis }} au {{ $jusqu }} · édité le {{ now()->toDateString() }}</p>
  <table>
    <thead><tr><th>Date</th><th>Type</th><th>Ruche</th><th>Rucher</th><th>Détail</th></tr></thead>
    <tbody>
      @forelse ($lignes as $ligne)
        <tr>
          <td>{{ $ligne['date'] }}</td>
          <td>{{ $types[$ligne['type']] }}</td>
          <td>{{ $ligne['ruche'] }}</td>
          <td>{{ $ligne['rucher'] }}</td>
          <td>{{ $ligne['detail'] }}</td>
        </tr>
      @empty
        <tr><td colspan="5">Rien sur cette période.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
