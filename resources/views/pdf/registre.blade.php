@php
  $jour = fn ($date) => \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
  // Le nom d'équipe donné d'office (« Camille's Team ») n'est pas un nom d'exploitation.
  $exploitation = $equipe && ! str_ends_with($equipe, "'s Team") ? $equipe : null;
@endphp
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Registre d'élevage apicole</title>
<style>
  @page { margin: 32px 34px 44px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1B2B25; }
  h1 { font-size: 20px; margin: 0 0 2px; color: #17382C; }
  h2 { font-size: 13px; margin: 18px 0 6px; color: #17382C; border-bottom: 2px solid #17382C; padding-bottom: 3px; }
  h3 { font-size: 11px; margin: 12px 0 4px; color: #17382C; }
  p { margin: 0 0 8px; color: #3D4F47; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; background: #17382C; color: #fff; padding: 4px 5px; font-size: 9px; }
  td { padding: 4px 5px; border-bottom: 1px solid #CFD3C8; vertical-align: top; }
  .fiche td { border: 0; padding: 3px 0; }
  .fiche td.cle { width: 32%; color: #3D4F47; }
  .fiche td.ligne { border-bottom: 1px dotted #9AA39C; height: 14px; }
  .rappel { background: #F3F0E4; border-left: 3px solid #C7932F; padding: 8px 10px; margin: 12px 0; }
  .rappel p { margin: 0 0 3px; color: #1B2B25; }
  .rien { color: #6B776F; font-style: italic; }
  .nowrap { white-space: nowrap; }
  .saut { page-break-before: always; }
  .notes { height: 150px; border: 1px solid #CFD3C8; }
  footer { position: fixed; bottom: -28px; left: 0; right: 0; font-size: 8px; color: #6B776F; }
</style>
</head>
<body>
  <footer>Registre d'élevage apicole · {{ $proprietaire }} · du {{ $jour($depuis) }} au {{ $jour($jusqu) }} · édité avec BeeNote le {{ now()->format('d/m/Y') }}</footer>

  <h1>Registre d'élevage apicole</h1>
  <p>Du {{ $jour($depuis) }} au {{ $jour($jusqu) }}@if ($rucher) · rucher {{ $rucher }}@endif</p>

  <h2>Apiculteur</h2>
  <table class="fiche">
    <tr><td class="cle">Nom et prénom</td><td>{{ $proprietaire }}</td></tr>
    <tr><td class="cle">Exploitation</td>@if ($exploitation)<td>{{ $exploitation }}</td>@else<td class="ligne"></td>@endif</tr>
    {{-- Repris du profil ; une ligne à remplir à la main quand il manque. --}}
    @foreach (['adresse' => 'Adresse', 'telephone' => 'Téléphone', 'napi' => "N° d'apiculteur (NAPI)", 'siret' => 'NUMAGRIT / SIRET'] as $champ => $libelle)
      <tr><td class="cle">{{ $libelle }}</td>@if (filled($identite[$champ] ?? null))<td>{{ $identite[$champ] }}</td>@else<td class="ligne"></td>@endif</tr>
    @endforeach
    <tr><td class="cle">Production</td><td>{{ $registre['production_miel'] ? '☒' : '☐' }} Miel &nbsp; ☐ Essaims &nbsp; ☐ Reines &nbsp; ☐ Gelée royale &nbsp; ☐ Pollen &nbsp; ☐ Propolis</td></tr>
  </table>

  <div class="rappel">
    <p><strong>À garder 5 ans.</strong> Le registre est obligatoire dès que des produits de la ruche sont cédés, même gratuitement (arrêté du 5 juin 2000).</p>
    <p>Joindre au registre de l'année : le récépissé de la déclaration de ruchers, les ordonnances vétérinaires, les résultats d'analyses et les comptes rendus de visites sanitaires.</p>
  </div>

  <h2>Contacts sanitaires</h2>
  <table class="fiche">
    <tr><td class="cle">DDPP / DDETSPP</td><td class="ligne"></td></tr>
    <tr><td class="cle">GDS, section apicole</td><td class="ligne"></td></tr>
    <tr><td class="cle">Technicien sanitaire apicole</td><td class="ligne"></td></tr>
    <tr><td class="cle">Vétérinaire (PSE)</td><td class="ligne"></td></tr>
    <tr><td class="cle">Assurance des ruches, n° de contrat</td><td class="ligne"></td></tr>
  </table>

  <h2 class="saut">Cheptel</h2>
  <p>{{ $registre['nb_ruches'] }} {{ $registre['nb_ruches'] > 1 ? 'ruches' : 'ruche' }} en vie aujourd'hui, réparties sur {{ $registre['ruchers']->count() }} {{ $registre['ruchers']->count() > 1 ? 'ruchers' : 'rucher' }}.</p>
  @include('pdf.registre-tableau', ['colonnes' => ['Rucher', 'Localisation', 'Ruches'], 'cles' => ['nom', 'localisation', 'ruches_count'], 'lignes' => $registre['ruchers']->map(fn ($r) => ['nom' => $r->nom, 'localisation' => $r->localisation ?: '—', 'ruches_count' => $r->ruches_count])->all()])

  <h2>Interventions sur les ruchers</h2>

  <h3>Lutte contre varroa : estimation de l'infestation</h3>
  @include('pdf.registre-tableau', ['colonnes' => ['Date', 'Rucher', 'Ruches', 'Méthode', 'Résultats'], 'cles' => ['date', 'rucher', 'ruches', 'methode', 'resultats'], 'lignes' => $registre['varroa']])

  <h3>Traitement contre varroa</h3>
  @include('pdf.registre-tableau', ['colonnes' => ['Date', 'Rucher', 'Ruches', 'Produit ou matière active', 'Dosage / ruche', 'Remarques'], 'cles' => ['date', 'rucher', 'ruches', 'produit', 'dosage', 'remarques'], 'lignes' => $registre['traitements_varroa']])

  <h3>Autres interventions sanitaires</h3>
  @include('pdf.registre-tableau', ['colonnes' => ['Date', 'Rucher', 'Ruches', 'Objet', 'Diagnostic par *', 'Traitement ou intervention', 'Remarques'], 'cles' => ['date', 'rucher', 'ruches', 'objet', 'diagnostic', 'traitement', 'remarques'], 'lignes' => $registre['sanitaires']])
  <p style="font-size: 8px">* Apiculteur, technicien sanitaire apicole, vétérinaire ou analyse de laboratoire : à compléter à la main.</p>

  <h3>Nourrissement</h3>
  @include('pdf.registre-tableau', ['colonnes' => ['Date', 'Rucher', 'Ruches', 'Cause', 'Produit', 'Remarques'], 'cles' => ['date', 'rucher', 'ruches', 'cause', 'produit', 'remarques'], 'lignes' => $registre['nourrissements']])

  <h3>Autres interventions (récolte, hausses, essaimage, nouvelles colonies…)</h3>
  @include('pdf.registre-tableau', ['colonnes' => ['Date', 'Rucher', 'Ruches', 'Cause', 'Action'], 'cles' => ['date', 'rucher', 'ruches', 'cause', 'action'], 'lignes' => $registre['interventions']])

  <h2>Suivi des transhumances</h2>
  @include('pdf.registre-tableau', ['colonnes' => ['Date', 'Emplacement de départ', 'Emplacement d\'arrivée', 'Ruches'], 'cles' => ['date', 'depuis', 'vers', 'ruches'], 'lignes' => $registre['transhumances']])

  <h2 class="saut">Journal des visites</h2>
  <table>
    <thead><tr><th>Date</th><th>Ruche</th><th>Rucher</th><th>Observations</th></tr></thead>
    <tbody>
      @forelse ($lignes as $ligne)
        <tr>
          <td class="nowrap">{{ $jour($ligne['date']) }}</td>
          <td>{{ $ligne['ruche'] }}</td>
          <td>{{ $ligne['rucher'] }}</td>
          <td>{{ $ligne['detail'] }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="rien">Aucune visite sur cette période.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2>Notes personnelles</h2>
  <div class="notes"></div>
  <p style="margin-top: 14px">Fait à ……………………………… le ……………………… &nbsp;&nbsp; Signature :</p>
</body>
</html>
