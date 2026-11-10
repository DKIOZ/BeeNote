<table>
  <thead><tr>@foreach ($colonnes as $colonne)<th>{{ $colonne }}</th>@endforeach</tr></thead>
  <tbody>
    @forelse ($lignes as $ligne)
      <tr>
        @foreach ($cles as $cle)
          <td @class(['nowrap' => $cle === 'date'])>{{ $cle === 'date' ? \Illuminate\Support\Carbon::parse($ligne[$cle])->format('d/m/Y') : ($ligne[$cle] ?? '') }}</td>
        @endforeach
      </tr>
    @empty
      <tr><td colspan="{{ count($colonnes) }}" class="rien">Rien de noté sur cette période.</td></tr>
    @endforelse
  </tbody>
</table>
