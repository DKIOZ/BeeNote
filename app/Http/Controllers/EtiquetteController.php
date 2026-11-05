<?php

namespace App\Http\Controllers;

use App\Models\Rucher;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EtiquetteController extends Controller
{
    public function index(Request $request)
    {
        $ruchers = Rucher::where('team_id', $request->user()->current_team_id)
            ->with(['ruches' => fn ($q) => $q->orderBy('id')])
            ->orderBy('nom')
            ->get();

        $qr = new QRCode(new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'outputBase64' => true,
            'addQuietzone' => true,
        ]));

        return Inertia::render('Etiquettes', [
            'ruchers' => $ruchers->map(fn (Rucher $rucher) => [
                'id' => $rucher->id,
                'nom' => $rucher->nom,
                'ruches' => $rucher->ruches->map(fn ($ruche) => [
                    'id' => $ruche->id,
                    'nom' => $ruche->nom,
                    'qr' => $qr->render(route('qr.scan', $ruche->qr_token)),
                ]),
            ]),
        ]);
    }
}
